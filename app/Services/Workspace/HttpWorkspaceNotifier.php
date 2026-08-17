<?php

namespace App\Services\Workspace;

use App\Contracts\WorkspaceNotifierInterface;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class HttpWorkspaceNotifier implements WorkspaceNotifierInterface
{
    private readonly string $baseUrl;

    private readonly string $webhookPath;

    private readonly string $serviceId;

    private readonly ?string $secret;

    private readonly int $timeout;

    public function __construct()
    {
        $this->baseUrl = rtrim((string) config('services.workspace.base_url', ''), '/');
        $this->webhookPath = '/'.ltrim((string) config('services.workspace.webhook_path', ''), '/');
        $this->serviceId = (string) config('services.workspace.service_id');
        $this->secret = config('services.workspace.secret');
        $this->timeout = (int) config('services.workspace.timeout', 5);
    }

    public function notifyItemsGenerated(array $payload): void
    {
        $this->ensureConfigured();

        // Sign the exact bytes we send so the receiver's HMAC check can't
        // drift from a re-encoded body.
        $body = json_encode($payload, JSON_THROW_ON_ERROR);
        $timestamp = (string) time();
        $signature = hash_hmac('sha256', "{$timestamp}.{$body}", $this->secret);

        $response = Http::withHeaders([
            'X-Service-Id' => $this->serviceId,
            'X-Service-Timestamp' => $timestamp,
            'X-Service-Signature' => $signature,
        ])
            ->timeout($this->timeout)
            ->withBody($body, 'application/json')
            ->post("{$this->baseUrl}{$this->webhookPath}");

        $response->throw();
    }

    private function ensureConfigured(): void
    {
        if (empty($this->baseUrl) || empty($this->secret)) {
            throw new RuntimeException(
                'Layanan Workspace belum dikonfigurasi. Periksa WORKSPACE_SERVICE_BASE_URL dan WORKSPACE_SERVICE_SECRET dalam .env'
            );
        }
    }
}
