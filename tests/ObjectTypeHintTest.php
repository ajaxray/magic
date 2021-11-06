<?php
namespace Ajaxray\Test;

use Ajaxray\Magic\Magic;
use Ajaxray\Test\TestData\GreetMailerWithConstructor;
use Ajaxray\Test\TestData\GreetWithConstructor;
use Ajaxray\Test\TestData\Simplest;
use PHPUnit\Framework\TestCase;

/**
 *  Testng Type Hint Resolution by Auto-wiring and defined service
 *
 * @author Anis Uddin Ahmad <anis.programmer@gmail.com>
 */
class ObjectTypeHintTest extends TestCase
{
    private Magic $magic;

    protected function setUp(): void
    {
        $this->magic = new Magic();
        $this->magic->param('name', 'Anis');
        $this->magic->param('email', 'anis@test.tld');

        parent::setUp();
    }

    public function testResolveObjectsByAutoWiring()
    {
        // Constructor of GreetMailerWithConstructor expects GreetWithConstructor object.
        // Expecting it to be injected by auto-wiring
        $this->magic->map('greet-mailer', GreetMailerWithConstructor::class);

        /** @var GreetMailerWithConstructor $obj */
        $obj = $this->magic->get('greet-mailer');
        $this->assertEquals('Mailing "Hello Anis" to anis@test.tld', $obj->mail());
    }

    public function testResolveObjectsByDefinedServiceByClassName()
    {
        $this->magic->map(GreetWithConstructor::class, fn ($m, $params) => new GreetWithConstructor('Umar'));

        // Constructor of GreetMailerWithConstructor expects GreetWithConstructor object.
        // Expecting it to be injected from mapped Services
        $this->magic->map('greet-mailer', GreetMailerWithConstructor::class);

        /** @var GreetMailerWithConstructor $obj */
        $obj = $this->magic->get('greet-mailer');
        $this->assertEquals('Mailing "Hello Umar" to anis@test.tld', $obj->mail());
    }

    public function testResolveObjectsByDefineServiceByParameterName()
    {
        $this->magic->map('greeter', GreetWithConstructor::class, ['name' => 'Othman']);

        // Constructor of GreetMailerWithConstructor expects GreetWithConstructor object as $greeter.
        // Expecting it to be injected from mapped Services 'greeter'
        $this->magic->map('greet-mailer', GreetMailerWithConstructor::class);

        /** @var GreetMailerWithConstructor $obj */
        $obj = $this->magic->get('greet-mailer');
        $this->assertEquals('Mailing "Hello Othman" to anis@test.tld', $obj->mail());
    }

}
