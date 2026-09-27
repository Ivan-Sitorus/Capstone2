import js from '@eslint/js';
import globals from 'globals';
import react from 'eslint-plugin-react';
import reactHooks from 'eslint-plugin-react-hooks';
import reactRefresh from 'eslint-plugin-react-refresh';
import prettier from 'eslint-config-prettier';

/**
 * ESLint flat config for the minePOS frontend (React 19 + Inertia + Vite).
 *
 * Scope is intentionally pragmatic: catch real bugs (undefined vars, hooks
 * violations) without fighting the existing hand-formatted codebase. Formatting
 * is owned by Prettier via `eslint-config-prettier`.
 */
export default [
    {
        ignores: [
            'public/build/**',
            'public/hot',
            'vendor/**',
            'node_modules/**',
            'storage/**',
            'bootstrap/cache/**',
            'datamining/**',
            '**/*.min.js',
        ],
    },

    // Application source: React 19 (automatic JSX runtime).
    {
        files: ['resources/js/**/*.{js,jsx}'],
        languageOptions: {
            ecmaVersion: 2022,
            sourceType: 'module',
            parserOptions: {
                ecmaFeatures: { jsx: true },
            },
            globals: {
                ...globals.browser,
                // Injected by resources/js/app.jsx for Ziggy route() helpers.
                route: 'readonly',
            },
        },
        settings: {
            react: { version: 'detect' },
        },
        plugins: {
            react,
            'react-hooks': reactHooks,
            'react-refresh': reactRefresh,
        },
        rules: {
            ...js.configs.recommended.rules,
            ...react.configs.recommended.rules,
            ...react.configs['jsx-runtime'].rules,
            ...reactHooks.configs.recommended.rules,

            // React 19 + automatic runtime: prop-types and React-in-scope are obsolete.
            'react/prop-types': 'off',
            'react/react-in-jsx-scope': 'off',

            // `_` is the codebase convention for an intentionally-unused binding
            // (e.g. `catch (_)` for optional storage access).
            'no-unused-vars': [
                'error',
                {
                    argsIgnorePattern: '^_',
                    varsIgnorePattern: '^_',
                    caughtErrorsIgnorePattern: '^_',
                },
            ],
            // `catch (_) {}` intentionally ignores a best-effort operation.
            'no-empty': ['error', { allowEmptyCatch: true }],

            // New compiler-based rule in react-hooks v7. The flagged effects are
            // intentional sync-from-props patterns; surface them as warnings so the
            // baseline stays actionable without rewriting behavior.
            'react-hooks/set-state-in-effect': 'warn',

            // Keep react-refresh advisory only: this app intentionally co-locates
            // helpers/constants with components in the same modules.
            'react-refresh/only-export-components': ['warn', { allowConstantExport: true }],
        },
    },

    // Config + tooling files run in Node, not the browser.
    {
        files: ['*.config.js', '*.config.mjs'],
        languageOptions: {
            ecmaVersion: 2022,
            sourceType: 'module',
            globals: { ...globals.node },
        },
    },

    // Playwright tests.
    {
        files: ['tests/**/*.{js,jsx}'],
        languageOptions: {
            ecmaVersion: 2022,
            sourceType: 'module',
            globals: { ...globals.node, ...globals.browser },
        },
    },

    // Disable rules that conflict with Prettier. Must stay last.
    prettier,
];
