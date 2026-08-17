<?php

namespace Tests\Feature\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Redis;
use RuntimeException;
use Tests\TestCase;

class HealthControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_reports_healthy_when_database_and_redis_are_reachable(): void
    {
        Redis::shouldReceive('connection->ping')->once()->andReturn('PONG');

        $response = $this->getJson(route('healthchecks'));

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'data' => [
                'database' => ['ok' => true],
                'redis' => ['ok' => true],
            ],
        ]);
    }

    public function test_reports_unhealthy_when_redis_is_unreachable(): void
    {
        Redis::shouldReceive('connection->ping')->once()->andThrow(new RuntimeException('Connection refused'));

        $response = $this->getJson(route('healthchecks'));

        $response->assertStatus(503);
        $response->assertJson([
            'success' => false,
            'errors' => [
                'database' => ['ok' => true],
                'redis' => ['ok' => false],
            ],
        ]);
    }
}
