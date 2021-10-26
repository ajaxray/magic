<?php
namespace Ajaxray\Test\TestData;

class GreetWithConstructor {
    public string $name;

    public function __construct($name) {
        $this->name = $name;
    }

    public function greet() :string
    {
        return 'Hello '. $this->name;
    }
}
