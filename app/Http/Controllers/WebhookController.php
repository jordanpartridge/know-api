<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;

class WebhookController
{
    /**
     * Handle GitHub webhook for deployments.
     */
    public function __invoke(Request $request): Response|JsonResponse
    {
        // Verify webhook signature
        if (! $this->verifyGitHubSignature($request)) {
            Log::warning('Invalid webhook signature from IP: ' . $request->ip());

            return response('Unauthorized', 401);
        }

        $payload = $request->json();

        // Only deploy on push to master branch
        if ($payload->get('ref') !== 'refs/heads/master') {
            Log::info('Ignoring push to branch: ' . $payload->get('ref'));

            return response('Ignored - not master branch', 200);
        }

        $commitHash = substr((string) $payload->get('after', 'unknown'), 0, 7);
        Log::info("🚀 Starting deployment for commit: {$commitHash}");

        // Queue the deployment to avoid timeout
        dispatch(function () use ($commitHash) {
            $this->performDeployment($commitHash);
        })->onQueue('deployments');

        return response()->json([
            'status' => 'deployment_queued',
            'commit' => $commitHash,
            'message' => 'Deployment started successfully',
        ]);
    }

    /**
     * Verify GitHub webhook signature.
     */
    private function verifyGitHubSignature(Request $request): bool
    {
        $signature = $request->header('X-Hub-Signature-256');
        $secret = config('app.webhook_secret');

        if (! $signature || ! $secret) {
            return false;
        }

        $payload = $request->getContent();
        $expectedSignature = 'sha256=' . hash_hmac('sha256', $payload, $secret);

        return hash_equals($expectedSignature, $signature);
    }

    /**
     * Perform the actual deployment.
     */
    private function performDeployment(string $commitHash): void
    {
        try {
            Log::info("📦 Starting container swap deployment for commit: {$commitHash}");

            // Step 1: Update code
            $this->runCommand('git pull origin master', 'Updating code from repository');

            // Step 2: Install dependencies
            $this->runCommand('composer install --no-dev --optimize-autoloader', 'Installing dependencies');

            // Step 3: Run migrations
            $this->runCommand('php artisan migrate --force', 'Running database migrations');

            // Step 4: Clear and cache config
            $this->runCommand('php artisan config:cache', 'Caching configuration');
            $this->runCommand('php artisan route:cache', 'Caching routes');
            $this->runCommand('php artisan view:cache', 'Caching views');

            // Step 5: Container swap (zero-downtime)
            $this->performContainerSwap();

            Log::info("✅ Deployment completed successfully for commit: {$commitHash}");
        } catch (\Exception $e) {
            Log::error("❌ Deployment failed for commit: {$commitHash}. Error: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Perform zero-downtime container swap.
     */
    private function performContainerSwap(): void
    {
        Log::info('🔄 Starting container swap...');

        // Build new image if Dockerfile changed
        $this->runCommand('docker-compose build app', 'Building updated container image');

        // Start new container alongside old one
        $this->runCommand('docker-compose up -d --no-deps app', 'Starting new container');

        // Wait for new container to be healthy
        $this->waitForHealthCheck();

        // Clean up old containers and images
        $this->runCommand('docker system prune -f', 'Cleaning up old containers');

        Log::info('✅ Container swap completed successfully');
    }

    /**
     * Wait for the new container to pass health checks.
     */
    private function waitForHealthCheck(): void
    {
        Log::info('⏳ Waiting for new container to be healthy...');

        $maxAttempts = 30;
        $attempt = 0;

        while ($attempt < $maxAttempts) {
            try {
                $result = Process::run('curl -f http://localhost/health');

                if ($result->successful()) {
                    Log::info('✅ New container is healthy');

                    return;
                }
            } catch (\Exception $e) {
                // Health check failed, continue waiting
            }

            $attempt++;
            sleep(2);
        }

        throw new \Exception('Health check timeout - new container failed to become healthy');
    }

    /**
     * Run a shell command and log the result.
     */
    private function runCommand(string $command, string $description): void
    {
        Log::info("🔧 {$description}...");

        $result = Process::run($command);

        if ($result->successful()) {
            Log::info("✅ {$description} completed");
            if ($result->output()) {
                Log::debug('Command output: ' . $result->output());
            }
        } else {
            Log::error("❌ {$description} failed");
            Log::error('Error output: ' . $result->errorOutput());
            throw new \Exception("Command failed: {$command}");
        }
    }
}
