<?php

namespace Tests\Feature;

use App\Models\Host;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Override;
use Tests\TestCase;

class LegacyTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    private Host $host;
    #[Override]
    public function setUp(): void
    {
        parent::setUp();
        $password = password_hash('password', PASSWORD_DEFAULT);
        $this->host = Host::query()->create([
            'password' => $password,
            'name' => 'test',
        ]);
    }
    /**
     * A basic feature test example.
     */
    public function test_errors(): void
    {
        $route = route('legacy.submit', absolute: false);
        // need an action:
        $this->post($route, [
            'host' => 'test',
            'pass' => 'password',
        ])->assertUnprocessable()->assertSeeText('The action field is required.')->assertViewIs('legacy');

        // the host must exist:
        $this->post($route, [
            'host' => 'incorrect',
            'pass' => 'password',
            'action' => 'register',
        ])->assertUnprocessable()->assertSeeText('The selected host is invalid.');

        $this->post($route, [
            'host' => 'test',
            'pass' => 'incorrect',
            'action' => 'register',
        ])->assertForbidden();

        $this->post($route, [
            'host' => 'test',
            'pass' => 'password',
        ])->assertUnprocessable()->assertSeeText('The action field is required.');
    }

    function test_register_ipv4() {
        $route = route('legacy.submit', absolute: false);
        $this->post($route, [
            'host' => 'test',
            'pass' => 'password',
            'action' => 'register',
            'ip' => $ip = $this->faker->unique()->ipv4(),
        ])->assertSeeText('dyndns entry created')->assertViewIs('legacy')->assertSeeText($ip)->assertCreated();
        $this->assertDatabaseCount('mappings', 1);
        $this->assertDatabaseHas('mappings', [
            'address' => $ip,
            'family' => AF_INET,
            'host_id' => $this->host->id,
        ]);
        $this->post($route, [
            'host' => 'test',
            'pass' => 'password',
            'action' => 'register',
        ], [
            'REMOTE_ADDR' => $ip = $this->faker->unique()->ipv4(),
        ])->assertSeeText('dyndns entry updated')->assertViewIs('legacy')->assertSeeText($ip)->assertOk();
        $this->assertDatabaseCount('mappings', 1);
        $this->assertDatabaseHas('mappings', [
            'address' => $ip,
            'family' => AF_INET,
            'host_id' => $this->host->id,
        ]);
    }

    function test_register_ipv6() {
        $route = route('legacy.submit', absolute: false);
        $this->post($route, [
            'host' => 'test',
            'pass' => 'password',
            'action' => 'register',
            'ip' => $ip = $this->faker->unique()->ipv6(),
        ])->assertCreated();
        $this->assertDatabaseCount('mappings', 1);
        $this->assertDatabaseHas('mappings', [
            'address' => $ip,
            'family' => AF_INET6,
            'host_id' => $this->host->id,
        ]);
        $this->post($route, [
            'host' => 'test',
            'pass' => 'password',
            'action' => 'register',
        ], [
            'REMOTE_ADDR' => $ip = $this->faker->unique()->ipv6(),
        ])->assertSeeText('dyndns entry updated')->assertViewIs('legacy')->assertSeeText($ip)->assertOk();
        $this->assertDatabaseCount('mappings', 1);
        $this->assertDatabaseHas('mappings', [
            'address' => $ip,
            'family' => AF_INET6,
            'host_id' => $this->host->id,
        ]);
        $ip = $this->faker->unique()->ipv6();
        $this->post($route, [
            'host' => 'test',
            'pass' => 'password',
            'action' => 'register',
        ], [
            'REMOTE_ADDR' => $ip,
        ])->assertOk();
        $this->assertDatabaseCount('mappings', 1);
        $this->assertDatabaseHas('mappings', [
            'address' => $ip,
            'family' => AF_INET6,
            'host_id' => $this->host->id,
        ]);
    }

    function test_register_explicit() {
        // $this->markTestIncomplete('ipv4 & ipv6 inputs not implemented');
        $route = route('legacy.submit', absolute: false);
        $this->post($route, [
            'host' => 'test',
            'pass' => 'password',
            'action' => 'register',
            'ipv4' => $ip = $this->faker->unique()->ipv4(),
        ])->assertCreated();
        $this->assertDatabaseHas('mappings', [
            'address' => $ip,
            'family' => AF_INET,
            'host_id' => $this->host->id,
        ]);
        $this->post($route, [
            'host' => 'test',
            'pass' => 'password',
            'action' => 'register',
            'ipv6' => $ip = $this->faker->unique()->ipv6(),
        ])->assertCreated();
        $this->assertDatabaseHas('mappings', [
            'address' => $ip,
            'family' => AF_INET6,
            'host_id' => $this->host->id,
        ]);
    }

    function test_unregister() {
        // $this->markTestIncomplete('unregister action not implemented');
        $route = route('legacy.submit', absolute: false);
        $this->post($route, [
            'host' => 'test',
            'pass' => 'password',
            'action' => 'unregister',
        ])->assertNotFound();
    }
}
