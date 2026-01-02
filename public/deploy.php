<?php

declare(strict_types=1);

// Security Token - MUST match the DEPLOY_TOKEN secret in GitHub
$secretToken = 'YOUR_SECURE_TOKEN_HERE_CHANGE_ME_IN_GITHUB_SECRETS';

// Verify Token
$requestToken = $_GET['token'] ?? $_SERVER['HTTP_X_DEPLOY_TOKEN'] ?? null;

if ($requestToken !== $secretToken) {
    http_response_code(403);
    exit('Access Denied: Invalid Token');
}

// Configuration
$projectDir = dirname(__DIR__); // Assumes this file is in /public
$log = [];

function runCommand($cmd, $cwd)
{
    global $log;
    $output = [];
    $returnVar = 0;
    $fullCmd = "cd $cwd && $cmd 2>&1";
    exec($fullCmd, $output, $returnVar);
    $log[] = "Command: $cmd";
    $log[] = 'Status: '.(0 === $returnVar ? 'OK' : "ERROR ($returnVar)");
    $log[] = "Output:\n".implode("\n", $output);
    $log[] = str_repeat('-', 20);

    return 0 === $returnVar;
}

// Start Deployment
header('Content-Type: text/plain');
echo "🚀 Starting Deployment...\n";
echo 'Date: '.date('Y-m-d H:i:s')."\n";
echo "Project Dir: $projectDir\n\n";

// 1. Git Pull
if (!runCommand('git pull origin main', $projectDir)) {
    http_response_code(500);
    exit('❌ Git Pull Failed. Check logs.');
}

// 2. Composer Install
// Optimize autoloader, no dev dependencies
if (!runCommand('/opt/cpanel/composer/bin/composer install --no-dev --optimize-autoloader --no-interaction', $projectDir)) {
    // Fallback to local composer or phar if global not found
    if (!runCommand('composer install --no-dev --optimize-autoloader --no-interaction', $projectDir)) {
        http_response_code(500);
        exit('❌ Composer Install Failed. Check logs.');
    }
}

// 3. Migrations
if (!runCommand('php bin/console doctrine:migrations:migrate --no-interaction --env=prod', $projectDir)) {
    http_response_code(500);
    exit('❌ Migrations Failed. Check logs.');
}

// 4. Cache Clear
if (!runCommand('php bin/console cache:clear --env=prod', $projectDir)) {
    http_response_code(500);
    exit('❌ Cache Clear Failed. Check logs.');
}

// 5. Asset Mapper Compile (if needed)
if (!runCommand('php bin/console asset-mapper:compile --env=prod', $projectDir)) {
    // Warning only, don't fail hard?
    $log[] = '⚠️ Asset compilation had issues, check output.';
}

echo "✅ Deployment Finished Successfully!\n\n";
echo "=== LOGS ===\n";
echo implode("\n", $log);
