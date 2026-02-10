<?php

namespace App\Services;

use App\DataTransferObjects\Person;
use Illuminate\Support\Str;

class HomeOwnerParser
{

    private array $homeOwnersArray = [];

    public function parse(string $homeOwner): void
    {
        $homeOwner = Str::of($homeOwner)->replace(' and ', ' & ')
                        ->explode(' & ')
                        ->all();

        if (count($homeOwner) === 1) {
            $this->homeOwnersArray[] = $this->parsePerson($homeOwner[0]);
            return;
        }

        $this->parsePeople($homeOwner);
    }

    public function getParsedHomeOwners(): array
    {
        return $this->homeOwnersArray;
    }

    private function parsePerson(string $homeOwner): Person
    {
        $nameArray = explode(' ', trim($homeOwner));

        $title = array_shift($nameArray);

        $firstName = null;
        $initial = null;
        $lastName = '';

        if (count($nameArray) === 1) {

            $lastName = $nameArray[0];

        } elseif (count($nameArray) > 1) {

            $firstName = array_shift($nameArray);
            $lastName = array_pop($nameArray);

            if ($this->isInitial($firstName)) {
                $initial = rtrim($firstName, '.');
                $firstName = null;
            } 
        }

        return new Person(
            $title,
            $firstName,
            $initial,
            $lastName
        );
    }

    /*
      Parse a couple or multi-person entry (e.g. "Dr & Mrs Joe Bloggs").
      The second person has the most complete info and is used as a reference.
      First person inherit missing name parts.
     */
    private function parsePeople(array $homeOwners): void
    {
        $secondPerson = array_pop($homeOwners);
        $secondPerson = $this->parsePerson($secondPerson);

        $nameArray = explode(' ', trim($homeOwners[0]));

        // If the first person record is just a title, inherit name parts from the second person
        if (count($nameArray) === 1) {
            $nameArray[] = $secondPerson->first_name ?? $secondPerson->initial; 
            $nameArray[] = $secondPerson->last_name;
        }

        $nameArray = array_filter($nameArray); // removes element (first name) when empty
        $fullName = implode(' ', $nameArray);
        $firstPerson = $this->parsePerson($fullName);
        
        $this->homeOwnersArray[] = $firstPerson;
        $this->homeOwnersArray[] = $secondPerson;

    }

    
    // Determine initial and also remove period if present.
     
    private function isInitial(string $firstName): bool
    {
        $nameWithoutPeriod = rtrim($firstName, '.');
        return strlen($nameWithoutPeriod) === 1;
    }
}
