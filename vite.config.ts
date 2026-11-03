import { existsSync, readdirSync, rmSync, writeFileSync } from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';
import tailwindcss from '@tailwindcss/vite';
import react from '@vitejs/plugin-react';
import { defineConfig, type Plugin } from 'vite';

const root = path.dirname(fileURLToPath(import.meta.url));
const devPort = Number(process.env.VITE_PORT ?? 5173);
const devOrigin = process.env.VITE_ORIGIN ?? `http://localhost:${devPort}`;

/**
 * Every top-level `src/js/*.ts` file becomes its own entry, so adding a new
 * script never requires touching this config. Keys are relative to the project
 * root, which is also how they appear in the manifest (e.g. `src/js/app.ts`).
 */
function discoverEntries(): string[] {
  const jsDir = path.join(root, 'src/js');
  const scripts = existsSync(jsDir)
    ? readdirSync(jsDir)
        .filter((name) => name.endsWith('.ts') && !name.endsWith('.d.ts'))
        .map((name) => `src/js/${name}`)
    : [];

  return ['src/css/app.css', ...scripts];
}

/**
 * Laravel-style "hot" file: while `vite` (dev server) runs, write its URL to
 * `<theme>/hot`. PHP checks for that file to decide between the dev server and
 * the built manifest, so WordPress never has to probe the dev server over HTTP.
 * The file is removed when the server stops.
 */
function hotFile(): Plugin {
  const hotPath = path.join(root, 'hot');
  let cleanedUp = false;

  const cleanUp = (): void => {
    if (cleanedUp) return;
    cleanedUp = true;
    rmSync(hotPath, { force: true });
  };

  return {
    name: 'cik-hybrid:hot-file',
    apply: 'serve',
    configureServer(server) {
      server.httpServer?.once('listening', () => {
        writeFileSync(hotPath, devOrigin);
        cleanedUp = false;
      });
      server.httpServer?.once('close', cleanUp);

      process.once('exit', cleanUp);
      for (const signal of ['SIGINT', 'SIGTERM', 'SIGHUP'] as const) {
        process.once(signal, () => {
          cleanUp();
          process.exit();
        });
      }
    },
  };
}

export default defineConfig({
  plugins: [tailwindcss(), react(), hotFile()],
  base: './',
  build: {
    outDir: 'dist',
    emptyOutDir: true,
    manifest: true,
    target: 'es2022',
    rollupOptions: {
      input: discoverEntries(),
    },
  },
  server: {
    port: devPort,
    strictPort: true,
    origin: devOrigin,
    cors: true,
  },
});
