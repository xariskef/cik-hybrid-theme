import react from '@vitejs/plugin-react';
import { defineConfig } from 'vitest/config';

// Separate from vite.config.ts: tests need neither Tailwind nor the hot-file plugin.
export default defineConfig({
  plugins: [react()],
  test: {
    environment: 'jsdom',
    include: ['tests/js/**/*.test.{ts,tsx}'],
    setupFiles: ['tests/js/setup.ts'],
    restoreMocks: true,
    unstubGlobals: true,
  },
});
