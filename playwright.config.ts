import { defineConfig, devices } from '@playwright/test';

const ci = Boolean(process.env.CI);

export default defineConfig({
  testDir: 'tests/e2e',
  fullyParallel: true,
  forbidOnly: ci,
  retries: ci ? 1 : 0,
  workers: ci ? 2 : undefined,
  reporter: ci ? [['github'], ['html', { open: 'never' }]] : [['list'], ['html', { open: 'never' }]],
  use: {
    // 127.0.0.1 rather than localhost: wp-env's default, and immune to other servers on [::1].
    baseURL: process.env.WP_BASE_URL ?? 'http://127.0.0.1:8888',
    trace: 'retain-on-failure',
    screenshot: 'only-on-failure',
  },
  projects: [
    {
      name: 'chromium',
      use: {
        ...devices['Desktop Chrome'],
        // Sites whose home URL says "localhost" redirect there; keep that on IPv4 too.
        launchOptions: { args: ['--host-resolver-rules=MAP localhost 127.0.0.1'] },
      },
    },
  ],
});
