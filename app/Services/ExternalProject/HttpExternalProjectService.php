<?php

namespace App\Services\ExternalProject;

use App\Contracts\ExternalProjectServiceInterface;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class HttpExternalProjectService implements ExternalProjectServiceInterface
{
    private readonly string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = rtrim((string) config('services.project_management.base_url', ''), '/');
    }

    public function fetchProject(string $externalId): array
    {
        $this->ensureConfigured();

        $response = Http::withHeaders($this->headers())
            ->get("{$this->baseUrl}/projects/{$externalId}");

        $response->throw();

        $data = $response->json('data');

        return is_array($data) && array_is_list($data) ? $data[0] : $data;
    }

    public function fetchCategories(string $externalProjectId): array
    {
        $this->ensureConfigured();

        $response = Http::withHeaders($this->headers())
            ->get("{$this->baseUrl}/spekteks/search", ['project_id' => $externalProjectId]);

        $response->throw();

        return $response->json('data') ?? [];
    }

    public function fetchCategory(string $externalCategoryId): array 
    {
        $this->ensureConfigured();

        $response = Http::withHeaders($this->headers())
            ->get("{$this->baseUrl}/spekteks/{$externalCategoryId}");

        $response->throw();

        $data = $response->json('data');

        return is_array($data) && array_is_list($data) ? $data[0] : $data;
    }

    public function fetchSubcategories(string $externalCategoryId): array
    {
        $this->ensureConfigured();

        $response = Http::withHeaders($this->headers())
            ->get("{$this->baseUrl}/sub-spekteks/search", ['spektek_id' => $externalCategoryId]);

        $response->throw();

        return $response->json('data') ?? [];
    }

    private function headers(): array
    {
        return array_filter([
            'Authorization' => config('services.project_management.token')
                ? 'Bearer '.config('services.project_management.token')
                : null,
        ]);
    }

    private function ensureConfigured(): void
    {
        if (empty($this->baseUrl)) {
            throw new RuntimeException(
                'Service Project Management belum dikonfigurasi. Periksa PROJECT_MANAGEMENT_BASE_URL dalam .env'
            );
        }
    }
}
