import eslintComments from '@eslint-community/eslint-plugin-eslint-comments';
import commentsConfig from '@eslint-community/eslint-plugin-eslint-comments/configs';
import eslintReact from '@eslint-react/eslint-plugin';
import css from '@eslint/css';
import js from '@eslint/js';
import json from '@eslint/json';
import markdown from '@eslint/markdown';
import wordpress from '@wordpress/eslint-plugin';
import compat from 'eslint-plugin-compat';
import packageJson from 'eslint-plugin-package-json';
import perfectionist from 'eslint-plugin-perfectionist';
import preferArrowFunctions from 'eslint-plugin-prefer-arrow-functions';
import prettierRecommended from 'eslint-plugin-prettier/recommended';
import { defineConfig, globalIgnores } from 'eslint/config';
import globals from 'globals';
import tseslint from 'typescript-eslint';

export default defineConfig(
	globalIgnores(['dist/', 'package-lock.json', 'vendor/']),
	packageJson.configs.recommended,
	{
		linterOptions: {
			reportUnusedDisableDirectives: 'error',
			reportUnusedInlineConfigs: 'error',
		},
	},
	{
		extends: [css.configs.recommended],
		files: ['**/*.css'],
		language: 'css/css',
		rules: {
			'css/no-important': 'off',
		},
	},
	{
		extends: [json.configs.recommended],
		files: ['**/*.json'],
		ignores: ['package.json'],
		language: 'json/json',
	},
	{
		extends: [markdown.configs.recommended],
		files: ['**/*.md'],
		language: 'markdown/commonmark',
	},
	{
		// eslint-disable-next-line @typescript-eslint/no-unsafe-assignment -- @wordpress/eslint-plugin's untyped recommended config makes this array any[]
		extends: [
			js.configs.recommended,
			prettierRecommended,
			commentsConfig.recommended,
			compat.configs['flat/recommended'],
			eslintReact.configs.strict,
			tseslint.configs.strict,
			tseslint.configs.stylistic,
			perfectionist.configs['recommended-natural'],
			// eslint-disable-next-line @typescript-eslint/no-unsafe-member-access -- @wordpress/eslint-plugin ships no types
			wordpress.configs.recommended,
		],
		files: ['**/*.js', '**/*.ts'],
		plugins: {
			'eslint-comments': eslintComments,
			'prefer-arrow-functions': preferArrowFunctions,
		},
		rules: {
			'@eslint-react/dom-no-string-style-prop': 'error',
			'@eslint-react/dom-no-unknown-property': 'error',
			'@eslint-react/no-missing-component-display-name': 'error',
			'@eslint-react/no-missing-context-display-name': 'error',
			'@wordpress/data-no-store-string-literals': 'error',
			'@wordpress/no-dom-globals-in-constructor': 'error',
			'@wordpress/no-dom-globals-in-module-scope': 'error',
			'@wordpress/no-dom-globals-in-react-cc-render': 'error',
			'@wordpress/no-dom-globals-in-react-fc': 'error',
			'@wordpress/no-non-module-stylesheet-imports': 'error',
			'@wordpress/no-unmerged-classname': 'error',
			'@wordpress/react-no-unsafe-timeout': 'error',
			'@wordpress/use-recommended-components': 'error',
			'array-callback-return': 'error',
			'arrow-body-style': ['error', 'as-needed'],
			'block-scoped-var': 'error',
			camelcase: 'error',
			'capitalized-comments': [
				'error',
				'always',
				{ ignoreConsecutiveComments: true },
			],
			'consistent-return': 'error',
			'consistent-this': 'error',
			'default-case': 'error',
			'default-case-last': 'error',
			eqeqeq: 'error',
			'eslint-comments/require-description': [
				'error',
				{
					ignore: ['eslint-enable'],
				},
			],
			'func-name-matching': 'error',
			'guard-for-in': 'error',
			'jsdoc/multiline-blocks': 'error',
			'jsdoc/require-yields-check': 'error',
			'jsdoc/tag-lines': 'error',
			'logical-assignment-operators': 'error',
			'new-cap': 'error',
			'no-alert': 'error',
			'no-await-in-loop': 'error',
			'no-console': 'error',
			'no-constructor-return': 'error',
			'no-div-regex': 'error',
			'no-duplicate-imports': 'error',
			'no-else-return': 'error',
			'no-eval': 'error',
			'no-extend-native': 'error',
			'no-extra-bind': 'error',
			'no-inner-declarations': 'error',
			'no-invalid-this': 'error',
			'no-iterator': 'error',
			'no-labels': 'error',
			'no-lone-blocks': 'error',
			'no-lonely-if': 'error',
			'no-multi-assign': 'error',
			'no-negated-condition': 'error',
			'no-new-func': 'error',
			'no-object-constructor': 'error',
			'no-octal-escape': 'error',
			'no-param-reassign': 'error',
			'no-promise-executor-return': 'error',
			'no-return-assign': 'error',
			'no-self-compare': 'error',
			'no-sequences': 'error',
			'no-template-curly-in-string': 'error',
			'no-unmodified-loop-condition': 'error',
			'no-unneeded-ternary': 'error',
			'no-unreachable-loop': 'error',
			'no-useless-call': 'error',
			'no-useless-computed-key': 'error',
			'no-useless-concat': 'error',
			'no-useless-rename': 'error',
			'no-useless-return': 'error',
			'no-void': ['error', { allowAsStatement: true }],
			'no-warning-comments': 'warn',
			'object-shorthand': 'error',
			'operator-assignment': 'error',
			'perfectionist/sort-union-types': [
				'error',
				{
					groups: [
						'literal',
						'function',
						'import',
						'operator',
						'conditional',
						'object',
						'tuple',
						'intersection',
						'union',
						'named',
						'keyword',
						'nullish',
						'unknown',
					],
				},
			],
			'prefer-arrow-functions/prefer-arrow-functions': [
				'error',
				{
					allowNamedFunctions: true,
				},
			],
			'prefer-exponentiation-operator': 'error',
			'prefer-object-spread': 'error',
			'prefer-regex-literals': 'error',
			'prefer-template': 'error',
			radix: 'error',
			'require-atomic-updates': 'error',
			strict: ['error', 'never'],
		},
	},
	{
		extends: [
			tseslint.configs.strictTypeChecked,
			tseslint.configs.stylisticTypeChecked,
			eslintReact.configs['strict-typescript'],
		],
		files: ['**/*.ts'],
		languageOptions: {
			parserOptions: {
				projectService: true,
				sourceType: 'script',
			},
		},
		rules: {
			'@eslint-react/no-class-component': 'off',
			'@typescript-eslint/array-type': ['error', { default: 'generic' }],
			'@typescript-eslint/class-methods-use-this': [
				'error',
				{
					ignoreOverrideMethods: true,
				},
			],
			'@typescript-eslint/consistent-type-exports': 'error',
			'@typescript-eslint/consistent-type-imports': 'error',
			'@typescript-eslint/default-param-last': 'error',
			'@typescript-eslint/explicit-function-return-type': 'error',
			'@typescript-eslint/explicit-member-accessibility': 'error',
			'@typescript-eslint/explicit-module-boundary-types': 'error',
			'@typescript-eslint/init-declarations': 'error',
			'@typescript-eslint/method-signature-style': ['error', 'method'],
			'@typescript-eslint/no-import-type-side-effects': 'error',
			'@typescript-eslint/no-invalid-void-type': 'error',
			'@typescript-eslint/no-shadow': 'error',
			'@typescript-eslint/no-unnecessary-parameter-property-assignment':
				'error',
			'@typescript-eslint/no-unnecessary-qualifier': 'error',
			'@typescript-eslint/no-use-before-define': [
				'error',
				{ functions: false },
			],
			'@typescript-eslint/no-useless-empty-export': 'error',
			'@typescript-eslint/parameter-properties': 'error',
			'@typescript-eslint/prefer-enum-initializers': 'error',
			'@typescript-eslint/prefer-readonly': 'error',
			'@typescript-eslint/promise-function-async': 'error',
			'@typescript-eslint/require-array-sort-compare': 'error',
			'@typescript-eslint/strict-boolean-expressions': 'error',
			'@typescript-eslint/switch-exhaustiveness-check': 'error',
			'@typescript-eslint/typedef': 'error',
		},
	},
	{
		files: ['*.config.js', '*.config.ts'],
		languageOptions: {
			globals: {
				...globals.node,
			},
		},
	},
	{
		files: ['scripts/**/*.js'],
		languageOptions: {
			globals: {
				...globals.node,
			},
		},
		rules: {
			'no-console': 'off', // These are CLI scripts, stdout is their interface
		},
	}
);
