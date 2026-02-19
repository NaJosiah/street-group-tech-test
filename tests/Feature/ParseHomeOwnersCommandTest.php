<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ParseHomeOwnersCommandTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
    }

    public function test_command_outputs_parsed_json_from_csv(): void
    {
        Storage::disk('local')->put('examples.csv', "homeowner\nMr John Smith\nMrs Jane Doe");

        $exitCode = Artisan::call('app:parse-home-owners');
        $output = Artisan::output();

        $this->assertEquals(0, $exitCode);
        $this->assertStringContainsString('"title": "Mr"', $output);
        $this->assertStringContainsString('"first_name": "John"', $output);
        $this->assertStringContainsString('"last_name": "Smith"', $output);
        $this->assertStringContainsString('"title": "Mrs"', $output);
        $this->assertStringContainsString('"first_name": "Jane"', $output);
        $this->assertStringContainsString('"last_name": "Doe"', $output);
    }

    public function test_command_fails_when_csv_missing(): void
    {
        $exitCode = Artisan::call('app:parse-home-owners');
        $output = Artisan::output();

        $this->assertEquals(1, $exitCode);
        $this->assertStringContainsString('CSV file not found', $output);
    }

    public function test_command_skips_csv_header_row(): void
    {
        Storage::disk('local')->put('examples.csv', "homeowner\nMr John Smith");

        $exitCode = Artisan::call('app:parse-home-owners');
        $output = Artisan::output();

        $this->assertEquals(0, $exitCode);
        $this->assertStringNotContainsString('"title": "homeowner"', $output);
        $this->assertStringContainsString('"title": "Mr"', $output);
    }

    public function test_command_parses_couples_from_csv(): void
    {
        Storage::disk('local')->put('examples.csv', "homeowner\nMr and Mrs Smith");

        $exitCode = Artisan::call('app:parse-home-owners');
        $output = Artisan::output();

        $decoded = json_decode($output, true);

        $this->assertEquals(0, $exitCode);
        $this->assertCount(2, $decoded);
        $this->assertEquals('Mr', $decoded[0]['title']);
        $this->assertEquals('Smith', $decoded[0]['last_name']);
        $this->assertEquals('Mrs', $decoded[1]['title']);
        $this->assertEquals('Smith', $decoded[1]['last_name']);
    }
}
