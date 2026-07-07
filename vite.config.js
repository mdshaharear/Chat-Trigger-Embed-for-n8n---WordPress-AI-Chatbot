import { defineConfig } from 'vite';

export default defineConfig({
  build: {
    outDir: 'dist',
    emptyOutDir: false,
    minify: 'esbuild',
    sourcemap: false,
    rollupOptions: {
      input: {
        chat: 'src/public/chat-controller.ts'
      },
      output: {
        assetFileNames: (assetInfo) => {
          if (assetInfo.name && assetInfo.name.endsWith('.css')) {
            return 'chat-trigger-embed.css';
          }
          return 'assets/[name]-[hash][extname]';
        },
        entryFileNames: 'chat-trigger-embed.js'
      }
    }
  }
});
