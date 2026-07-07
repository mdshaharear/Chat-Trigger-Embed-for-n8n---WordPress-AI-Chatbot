import http from 'node:http';

const port = Number(process.env.CTEN_MOCK_N8N_PORT || 8787);
const delayMs = Number(process.env.CTEN_MOCK_N8N_DELAY_MS || 0);

function readBody(request) {
	return new Promise((resolve) => {
		let body = '';
		request.on('data', (chunk) => {
			body += chunk;
		});
		request.on('end', () => resolve(body));
	});
}

function send(response, status, payload, headers = {}) {
	response.writeHead(status, {
		'content-type': 'application/json; charset=utf-8',
		'access-control-allow-origin': '*',
		'access-control-allow-methods': 'GET,POST,OPTIONS',
		'access-control-allow-headers': 'content-type',
		...headers
	});
	response.end(typeof payload === 'string' ? payload : JSON.stringify(payload));
}

function parsePayload(request, rawBody) {
	const url = new URL(request.url || '/', `http://${request.headers.host || 'localhost'}`);
	if (request.method === 'GET') {
		return Object.fromEntries(url.searchParams.entries());
	}
	try {
		return rawBody ? JSON.parse(rawBody) : {};
	} catch {
		return { action: 'invalidJson' };
	}
}

function responseFor(payload) {
	const chatInput = String(payload.chatInput || payload.message || '');
	if (payload.action === 'loadPreviousSession') {
		return { data: [] };
	}
	if (chatInput.includes('empty')) {
		return {};
	}
	if (chatInput.includes('array')) {
		return [{ text: 'Array response from mock n8n.' }];
	}
	if (chatInput.includes('options')) {
		return { output: 'Choose one:\n[[OPTION:Customer Support]]\n[[OPTION:Lead Collection]]' };
	}
	if (chatInput.includes('lead hot')) {
		return { output: 'Thanks, I marked this as urgent. [[LEAD_STATUS:hot]]' };
	}
	return { output: `Mock n8n received: ${chatInput || 'hello'}` };
}

const server = http.createServer(async (request, response) => {
	if (request.method === 'OPTIONS') {
		send(response, 204, '');
		return;
	}

	const url = new URL(request.url || '/', `http://${request.headers.host || 'localhost'}`);
	const status = Number(url.searchParams.get('status') || 200);
	if (url.searchParams.get('invalidJson') === '1') {
		response.writeHead(200, { 'content-type': 'application/json', 'access-control-allow-origin': '*' });
		response.end('{invalid');
		return;
	}
	if (url.searchParams.get('timeout') === '1') {
		return;
	}

	const rawBody = await readBody(request);
	const payload = parsePayload(request, rawBody);
	const reply = () => send(response, status, status >= 400 ? { error: `Mock HTTP ${status}` } : responseFor(payload));

	if (delayMs > 0) {
		setTimeout(reply, delayMs);
		return;
	}
	reply();
});

server.listen(port, () => {
	console.log(`Mock n8n server listening at http://localhost:${port}/webhook/chat`);
});
