<?php

namespace Tests\Feature;

use App\Models\Host;
use App\Models\Mapping;
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
        ])->assertSeeText('The action field is required.')->assertViewIs('legacy')->assertUnprocessable();

        // the host must exist:
        $this->post($route, [
            'host' => 'incorrect',
            'pass' => 'password',
            'action' => 'register',
        ])->assertSeeText('The selected host is invalid.')->assertUnprocessable();

        $this->post($route, [
            'host' => 'test',
            'pass' => 'incorrect',
            'action' => 'register',
        ])->assertForbidden();

        $this->post($route, [
            'host' => 'test',
            'pass' => 'password',
        ])->assertUnprocessable()->assertSeeText('The action field is required.');

        $this->post($route, [
            'host' => 'test',
            'pass' => 'password',
            'action' => 'register',
            'ip' => $ip = $this->faker->unique()->ipv4(),
            'ipv4' => $ip,
        ])->assertUnprocessable()->assertSeeText('The ipv4 field prohibits ip / ipv6 from being present.');

        $this->post($route, [
            'host' => 'test',
            'pass' => 'password',
            'action' => 'register',
            'ip' => $this->faker->unique()->ipv6(),
            'ipv6' => $this->faker->unique()->ipv4(),
        ])->assertUnprocessable()->assertSeeText('The ipv6 field prohibits ip / ipv4 from being present.');

        $this->post($route, [
            'host' => 'test',
            'pass' => 'password',
            'action' => 'register',
            'ipv4' => $this->faker->unique()->ipv4(),
            'ipv6' => $this->faker->unique()->ipv6(),
        ])->assertUnprocessable()->assertSeeText('The ipv4 field prohibits ip / ipv6 from being present.');
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
        Mapping::query()->create([
            'host_id' => $this->host->id,
            'address' => $this->faker()->ipv4(),
            'family' => AF_INET,
        ]);
        Mapping::query()->create([
            'host_id' => $this->host->id,
            'address' => $this->faker()->ipv6(),
            'family' => AF_INET6,
        ]);
        $this->assertDatabaseCount('mappings', 2);

        $this->post($route, [
            'host' => 'test',
            'pass' => 'password',
            'action' => 'unregister',
        ])->assertNoContent();
        $this->assertDatabaseCount('mappings', 0);
    }
}
