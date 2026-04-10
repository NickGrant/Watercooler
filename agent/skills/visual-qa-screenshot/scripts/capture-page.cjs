#!/usr/bin/env node

const { spawnSync } = require('child_process');
const fs = require('fs');
const path = require('path');

function parseArgs(argv) {
  const args = {};
  for (let index = 0; index < argv.length; index += 1) {
    const token = argv[index];

    if (!token.startsWith('--')) {
      continue;
    }

    const key = token.slice(2);
    const next = argv[index + 1];

    if (!next || next.startsWith('--')) {
      args[key] = true;
      continue;
    }

    args[key] = next;
    index += 1;
  }

  return args;
}

function parseViewport(input) {
  if (!input) {
    return { width: 1440, height: 1400 };
  }

  const match = /^(\d+)x(\d+)$/i.exec(input);

  if (!match) {
    throw new Error('Viewport must use WIDTHxHEIGHT, for example 1440x1400.');
  }

  return {
    width: Number.parseInt(match[1], 10),
    height: Number.parseInt(match[2], 10),
  };
}

async function main() {
  const args = parseArgs(process.argv.slice(2));

  if (!args.url || !args.output) {
    throw new Error('Both --url and --output are required.');
  }

  const viewport = parseViewport(args.viewport);
  const timeout = Number.parseInt(args.timeout ?? '20000', 10);
  const delay = Number.parseInt(args.delay ?? '750', 10);
  const outputPath = path.resolve(args.output);
  fs.mkdirSync(path.dirname(outputPath), { recursive: true });

  const commandArgs = [
    'exec',
    '--yes',
    '--package=playwright',
    '--',
    'playwright',
    'screenshot',
    '--browser',
    'chromium',
    '--viewport-size',
    `${viewport.width},${viewport.height}`,
    '--timeout',
    `${timeout}`,
  ];

  if (args['wait-for']) {
    commandArgs.push('--wait-for-selector', args['wait-for']);
  }

  if (delay > 0) {
    commandArgs.push('--wait-for-timeout', `${delay}`);
  }

  if (args['full-page'] !== 'false') {
    commandArgs.push('--full-page');
  }

  commandArgs.push(args.url, outputPath);

  const runNpm = (args) =>
    process.platform === 'win32'
      ? spawnSync('cmd.exe', ['/d', '/s', '/c', 'npm.cmd', ...args], {
          encoding: 'utf8',
          shell: false,
        })
      : spawnSync('npm', args, {
          encoding: 'utf8',
          shell: false,
        });

  const printResult = (result) => {
    if (result.stdout) {
      process.stdout.write(result.stdout);
    }

    if (result.stderr) {
      process.stderr.write(result.stderr);
    }
  };

  let result = runNpm(commandArgs);

  const combinedOutput = `${result.stdout ?? ''}\n${result.stderr ?? ''}`;

  if (result.status !== 0 && combinedOutput.includes('playwright install')) {
    process.stdout.write('Installing Playwright Chromium for visual QA...\n');

    const installResult = runNpm([
      'exec',
      '--yes',
      '--package=playwright',
      '--',
      'playwright',
      'install',
      'chromium',
    ]);

    printResult(installResult);

    if (installResult.error) {
      throw installResult.error;
    }

    if (installResult.status !== 0) {
      throw new Error(`Playwright install failed with exit code ${installResult.status}.`);
    }

    result = runNpm(commandArgs);
  }

  printResult(result);

  if (result.error) {
    throw result.error;
  }

  if (result.status !== 0) {
    throw new Error(`Playwright screenshot command failed with exit code ${result.status}.`);
  }
}

main().catch((error) => {
  process.stderr.write(`${error.message}\n`);
  process.exitCode = 1;
});
