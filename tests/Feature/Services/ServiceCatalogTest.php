<?php

namespace Tests\Feature\Services;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ServiceCatalogTest extends TestCase
{
    use RefreshDatabase;

    public function test_service_catalog_page_shows_the_unified_brand(): void
    {
        $response = $this->get('/services');

        $response->assertOk()
            ->assertSee('الإمداد')
            ->assertDontSee('الإمداد العقارية');
    }

    public function test_service_catalog_page_renders_an_empty_state_when_service_tables_are_missing(): void
    {
        Schema::shouldReceive('hasTable')
            ->andReturnUsing(fn (string $table): bool => ! str_starts_with($table, 'service_'));

        $response = $this->get('/services');

        $response->assertOk()
            ->assertSee(__('app.services.no_providers'))
            ->assertDontSee('الإمداد العقارية');
    }
}
