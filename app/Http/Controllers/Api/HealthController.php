<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Throwable;

class HealthController extends ApiController
{
    public function check(): JsonResponse
    {
        $checks = [
            'database' => $this->checkDatabase(),
            'redis' => $this->checkRedis(),
        ];

        $healthy = collect($checks)->every(fn (array $check) => $check['ok']);

        return $healthy
            ? $this->success($checks, 'Healthy')
            : $this->error('Unhealthy', 503, $checks);
    }

    /**
     * @return array{ok: bool, message: string}
     */
    private function checkDatabase(): array
    {
        try {
            DB::connection()->getPdo();

            return ['ok' => true, 'message' => 'Connected'];
        } catch (Throwable $e) {
            return ['ok' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * @return array{ok: bool, message: string}
     */
    private function checkRedis(): array
    {
        try {
            Redis::connection()->ping();

            return ['ok' => true, 'message' => 'Connected'];
        } catch (Throwable $e) {
            return ['ok' => false, 'message' => $e->getMessage()];
        }
    }
}
