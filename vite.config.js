import { defineConfig } from 'vite';


export default defineConfig({
	build: {
		target: 'esnext',
		manifest: false,
		outDir: 'assets/dist/',
		rollupOptions: {
			input: {
				backend: '/assets/src/backend.ts',
				frontend: '/assets/src/frontend.ts',
			},
			output: {
				entryFileNames: "[name].js",
				assetFileNames: "[name].[ext]",
			}
		}
	}
});
