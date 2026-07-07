import { execFileSync } from 'node:child_process';
import { existsSync, readdirSync, statSync, readFileSync } from 'node:fs';
import { dirname, join, resolve } from 'node:path';
import { fileURLToPath } from 'node:url';

const root = resolve(fileURLToPath(new URL('..', import.meta.url)));
const nodeBin = process.env.CTEN_NODE_BIN || 'node';
const phpBin = process.env.CTEN_PHP_BIN || 'php';
const npmBin = process.env.CTEN_NPM_BIN || (process.platform === 'win32' ? join(dirname(nodeBin), 'npm.cmd') : 'npm');
const nodePathPrefix = process.platform === 'win32' ? `${dirname(nodeBin)};` : `${dirname(nodeBin)}:`;
const packageJsonPath = resolve(root, 'package.json');
const packageJson = JSON.parse(readFileSync(packageJsonPath, 'utf8'));
const expectedVersion = packageJson.version;

function run(command, args, label) {
	console.log(`\n> ${label}`);
	const env = {
		...process.env,
		PATH: `${nodePathPrefix}${process.env.PATH || ''}`
	};
	const commandLower = command.toLowerCase();
	if (commandLower.endsWith('.cmd') || commandLower.endsWith('.bat')) {
		const quote = (value) => {
			const text = String(value);
			return /[\s"]/u.test(text) ? `"${text.replaceAll('"', '\\"')}"` : text;
		};
		const fullCommand = [command, ...args.map(quote)].join(' ');
		execFileSync('cmd.exe', ['/d', '/s', '/c', fullCommand], { cwd: root, stdio: 'inherit', env });
		return;
	}

	execFileSync(command, args, { cwd: root, stdio: 'inherit', env });
}

function walkPhpFiles(dir) {
	const stack = [dir];
	const files = [];
	const skipped = new Set(['.git', '.agents', 'node_modules', 'package-build']);
	while (stack.length) {
		const current = stack.pop();
		for (const entry of readdirSync(current, { withFileTypes: true })) {
			const absolute = join(current, entry.name);
			if (entry.isDirectory()) {
				if (skipped.has(entry.name)) {
					continue;
				}
				stack.push(absolute);
				continue;
			}
			if (skipped.has(entry.name)) {
				continue;
			}
			if (entry.isFile() && entry.name.endsWith('.php')) {
				files.push(absolute);
			}
		}
	}
	return files;
}

function assertAsset(path) {
	if (!existsSync(path)) {
		throw new Error(`Missing required release asset: ${path}`);
	}
	const size = statSync(path).size;
	if (size <= 0) {
		throw new Error(`Empty release asset: ${path}`);
	}
}

console.log(`Release verification for ${packageJson.name}@${expectedVersion}`);
console.log(`Using Node: ${nodeBin}`);
console.log(`Using PHP: ${phpBin}`);

run(nodeBin, ['-v'], 'node -v');
run(phpBin, ['-v'], 'php -v');
run(npmBin, ['ci', '--ignore-scripts'], 'npm ci --ignore-scripts');
run(npmBin, ['run', 'build'], 'npm run build');
run(npmBin, ['run', 'lint'], 'npm run lint');
run(npmBin, ['run', 'typecheck'], 'npm run typecheck');
run(npmBin, ['test'], 'npm test');

const phpFiles = walkPhpFiles(root);
for (const file of phpFiles) {
	run(phpBin, ['-l', file], `php -l ${file}`);
}

assertAsset(resolve(root, 'dist/chat-trigger-embed.js'));
assertAsset(resolve(root, 'dist/chat-trigger-embed.css'));
assertAsset(resolve(root, 'dist/chat-trigger-embed-for-n8n.zip'));
assertAsset(resolve(root, 'LICENSE'));

console.log('\nRelease verification completed successfully.');
