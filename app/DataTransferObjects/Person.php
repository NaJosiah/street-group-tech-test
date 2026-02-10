<?php

namespace App\DataTransferObjects;

use JsonSerializable;

final class Person implements JsonSerializable
{
    public function __construct(
        public readonly string  $title,
        public readonly ?string $first_name,
        public readonly ?string $initial,
        public readonly string  $last_name
    ) {}

    public function jsonSerialize(): array
    {
        return [
            'title'      => $this->title,
            'first_name' => $this->first_name,
            'last_name'  => $this->last_name,
            'initial'    => $this->initial
        ];
    }
}