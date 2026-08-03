<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesKrsData;
use Tests\TestCase;

abstract class FeatureTestCase extends TestCase
{
    use CreatesKrsData;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }
}
