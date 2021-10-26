<?php
namespace Ajaxray\Test;

use Ajaxray\Magic\Magic;
use Ajaxray\Test\TestData\GreetMailerWithConstructor;
use Ajaxray\Test\TestData\GreetWithConstructor;
use Ajaxray\Test\TestData\Simplest;
use PHPUnit\Framework\TestCase;

/**
 *  Testng basic class initializing using service name
 *
 * @author Anis Uddin Ahmad <anis.programmer@gmail.com>
 */
class BasicClassTest extends TestCase
{
    private Magic $magic;

    protected function setUp(): void
    {
        $this->magic = new Magic();

        parent::setUp();
    }


    public function testServiceMappingWithoutConstructor()
    {
        $this->magic->map('simple', Simplest::class);

        $obj = $this->magic->get('simple');
        $this->assertEquals('b', $obj->a);
    }

    public function testServiceMappingWithScalarParamConstructor()
    {
        $this->magic->map('greeter', GreetWithConstructor::class, ['name' => 'Anis']);

        /** @var GreetWithConstructor $obj */
        $obj = $this->magic->get('greeter');
        $this->assertEquals('Hello Anis', $obj->greet());
    }

    public function testServiceMappingWithObjectParamConstructor()
    {
        $this->magic->map('greet-mailer', GreetMailerWithConstructor::class, ['name' => 'Anis', 'email' => 'anis@test.tld']);

        /** @var GreetMailerWithConstructor $obj */
        $obj = $this->magic->get('greet-mailer');
        $this->assertEquals('Mailing "Hello Anis" to anis@test.tld', $obj->mail());
    }

}
