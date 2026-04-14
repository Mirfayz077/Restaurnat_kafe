import concurrentlyModule from 'concurrently';
import { spawn as spawnChild } from 'node:child_process';
import { existsSync } from 'node:fs';
import { join } from 'node:path';

const concurrently = concurrentlyModule.default ?? concurrentlyModule;

const defaultCommands = [
  { command: 'php artisan serve', name: 'server' },
  { command: 'php artisan queue:listen --tries=1 --timeout=0', name: 'queue' },
  { command: 'php artisan reverb:start --host=0.0.0.0 --port=8080', name: 'reverb' },
  { command: 'npm run dev', name: 'vite' },
];

function resolveCommands() {
  const override = process.env.DEV_RUNNER_COMMANDS_JSON;

  if (!override) {
    return defaultCommands;
  }

  try {
    const parsed = JSON.parse(override);

    if (!Array.isArray(parsed) || parsed.length === 0) {
      throw new Error('Expected a non-empty JSON array.');
    }

    return parsed;
  } catch (error) {
    console.error('DEV_RUNNER_COMMANDS_JSON is invalid:', error.message);
    process.exit(1);
  }
}

function resolveWindowsShell() {
  const candidates = [
    process.env.ComSpec,
    process.env.COMSPEC,
    process.env.SystemRoot && join(process.env.SystemRoot, 'System32', 'cmd.exe'),
    process.env.windir && join(process.env.windir, 'System32', 'cmd.exe'),
    'C:\\Windows\\System32\\cmd.exe',
  ].filter(Boolean);

  return candidates.find((candidate) => existsSync(candidate)) ?? 'cmd.exe';
}

const windowsShell = resolveWindowsShell();

function spawnCommand(command, options) {
  // Prefer an absolute Windows shell path so a broken PATH or ComSpec does not break `composer dev`.
  if (process.platform === 'win32') {
    return spawnChild(windowsShell, ['/s', '/c', `"${command}"`], {
      ...options,
      windowsVerbatimArguments: true,
    });
  }

  return spawnChild('/bin/sh', ['-c', command], options);
}

const { result } = concurrently(resolveCommands(), {
  prefix: 'name',
  prefixColors: ['#93c5fd', '#c4b5fd', '#34d399', '#fdba74'],
  killOthersOn: ['success', 'failure'],
  spawn: spawnCommand,
});

try {
  await result;
} catch {
  process.exit(1);
}
