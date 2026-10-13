import { defineConfig } from 'vitest/config'
import react from '@vitejs/plugin-react'
import { loadEnv } from 'vite'

// https://vite.dev/config/
export default defineConfig(({ mode }) => {
  const config = loadEnv(mode, process.cwd(), 'SIGNALIST_');
  const backend = config.SIGNALIST_DEV_BACKEND_URL || `http://localhost:${config.SIGNALIST_HTTP_PORT || '8000'}`;
  return {
  plugins: [react()],
  server: {
    port: 5173,
    proxy: {
      '/oauth': { target: backend, changeOrigin: true },
      '/api': {
        target: backend,
        changeOrigin: true,
      },
    },
  },
  test: {
    globals: true,
    environment: 'jsdom',
    setupFiles: './src/test/setup.ts',
  },
  };
})
