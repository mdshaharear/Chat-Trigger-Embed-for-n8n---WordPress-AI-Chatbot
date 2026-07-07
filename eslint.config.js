import js from '@eslint/js';
import tseslint from '@typescript-eslint/eslint-plugin';
import tsparser from '@typescript-eslint/parser';

export default [
	js.configs.recommended,
	{
		files: ['src/**/*.ts', 'tests/**/*.ts'],
		languageOptions: {
			parser: tsparser,
			parserOptions: {
				ecmaVersion: 'latest',
				sourceType: 'module',
				project: './tsconfig.json'
			},
			globals: {
				window: 'readonly',
				document: 'readonly',
				navigator: 'readonly',
				crypto: 'readonly',
				console: 'readonly',
				fetch: 'readonly',
				MutationObserver: 'readonly',
				HTMLElement: 'readonly',
				HTMLButtonElement: 'readonly',
				HTMLTextAreaElement: 'readonly',
				Event: 'readonly',
				Storage: 'readonly',
				Intl: 'readonly'
			}
		},
		plugins: {
			'@typescript-eslint': tseslint
		},
		rules: {
			'@typescript-eslint/no-unused-vars': ['error', { argsIgnorePattern: '^_' }],
			'no-unused-vars': 'off'
		}
	}
];
