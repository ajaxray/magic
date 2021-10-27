<?php
namespace Ajaxray\Test\TestData;

class GreetMailerWithConstructor {
    public function __construct(
        private GreetWithConstructor $greeter,
        private string $email) {
    }

    public function mail() :string
    {
        $greetMsg = $this->greeter->greet();

        return "Mailing \"$greetMsg\" to {$this->email}";
    }
}