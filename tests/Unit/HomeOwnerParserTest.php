<?php

namespace Tests\Unit;

use App\DataTransferObjects\Person;
use App\Services\HomeOwnerParser;
use PHPUnit\Framework\TestCase;

class HomeOwnerParserTest extends TestCase
{
    private HomeOwnerParser $parser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->parser = new HomeOwnerParser();
    }

    public function test_parses_simple_name(): void
    {
        $this->parser->parse('Mr John Smith');
        $result = $this->parser->getParsedHomeOwners();

        $this->assertCount(1, $result);
        $this->assertPersonEquals($result[0], 'Mr', 'John', null, 'Smith');
    }

    public function test_parses_title(): void
    {
        $this->parser->parse('Mister John Doe');
        $result = $this->parser->getParsedHomeOwners();

        $this->assertCount(1, $result);
        $this->assertPersonEquals($result[0], 'Mister', 'John', null, 'Doe');
    }

    public function test_parses_initial_without_period(): void
    {
        $this->parser->parse('Mr M Mackie');
        $result = $this->parser->getParsedHomeOwners();

        $this->assertCount(1, $result);
        $this->assertPersonEquals($result[0], 'Mr', null, 'M', 'Mackie');
    }

    public function test_parses_initial_with_period(): void
    {
        $this->parser->parse('Mr F. Fredrickson');
        $result = $this->parser->getParsedHomeOwners();

        $this->assertCount(1, $result);
        $this->assertPersonEquals($result[0], 'Mr', null, 'F', 'Fredrickson');
    }

    public function test_parses_hyphenated_last_name(): void
    {
        $this->parser->parse('Mrs Faye Hughes-Eastwood');
        $result = $this->parser->getParsedHomeOwners();

        $this->assertCount(1, $result);
        $this->assertPersonEquals($result[0], 'Mrs', 'Faye', null, 'Hughes-Eastwood');
    }

    public function test_parses_couple_with_and_sharing_last_name(): void
    {
        $this->parser->parse('Mr and Mrs Smith');
        $result = $this->parser->getParsedHomeOwners();

        $this->assertCount(2, $result);
        $this->assertPersonEquals($result[0], 'Mr', null, null, 'Smith');
        $this->assertPersonEquals($result[1], 'Mrs', null, null, 'Smith');
    }

    public function test_parses_couple_with_ampersand_inheriting_name(): void
    {
        $this->parser->parse('Dr & Mrs Joe Bloggs');
        $result = $this->parser->getParsedHomeOwners();

        $this->assertCount(2, $result);
        $this->assertPersonEquals($result[0], 'Dr', 'Joe', null, 'Bloggs');
        $this->assertPersonEquals($result[1], 'Mrs', 'Joe', null, 'Bloggs');
    }

    public function test_parses_two_complete_people_with_and(): void
    {
        $this->parser->parse('Mr Tom Staff and Mr John Doe');
        $result = $this->parser->getParsedHomeOwners();

        $this->assertCount(2, $result);
        $this->assertPersonEquals($result[0], 'Mr', 'Tom', null, 'Staff');
        $this->assertPersonEquals($result[1], 'Mr', 'John', null, 'Doe');
    }

    public function test_parses_multiple_parse_calls(): void
    {
        $this->parser->parse('Mr John Smith');
        $this->parser->parse('Mrs Jane Doe');
        $result = $this->parser->getParsedHomeOwners();

        $this->assertCount(2, $result);
        $this->assertPersonEquals($result[0], 'Mr', 'John', null, 'Smith');
        $this->assertPersonEquals($result[1], 'Mrs', 'Jane', null, 'Doe');
    }

    public function test_person_json_serialization(): void
    {
        $person = new Person(
            title: 'Mr',
            first_name: 'John',
            initial: null,
            last_name: 'Smith'
        );

        $decoded = json_decode(json_encode($person), true);

        $this->assertEquals('Mr', $decoded['title']);
        $this->assertEquals('John', $decoded['first_name']);
        $this->assertNull($decoded['initial']);
        $this->assertEquals('Smith', $decoded['last_name']);
    }

    private function assertPersonEquals(
        Person $person,
        string $title,
        ?string $firstName,
        ?string $initial,
        string $lastName
    ): void {
        $this->assertEquals($title, $person->title, "Title mismatch");
        $this->assertEquals($firstName, $person->first_name, "First name mismatch");
        $this->assertEquals($initial, $person->initial, "Initial mismatch");
        $this->assertEquals($lastName, $person->last_name, "Last name mismatch");
    }
}
