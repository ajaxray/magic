<?php
namespace Ajaxray\Test;

use Ajaxray\Magic\Magic;
use Ajaxray\Test\TestData\GreetMailerWithConstructor;
use Ajaxray\Test\TestData\GreetWithConstructor;
use Ajaxray\Test\TestData\MailerImplementation;
use Ajaxray\Test\TestData\MailerInterface;
use Ajaxray\Test\TestData\Notification\Notifier;
use Ajaxray\Test\TestData\Notification\SMSTransport;
use Ajaxray\Test\TestData\Notification\TransportInterface;
use Ajaxray\Test\TestData\Simplest;
use PHPUnit\Framework\TestCase;

/**
 *  Testng if AutoWiring works (if the dependencies are resolvable)
 *
 * @author Anis Uddin Ahmad <anis.programmer@gmail.com>
 */
class AutoWiringTest extends TestCase
{
    private Magic $magic;

    protected function setUp(): void
    {
        $this->magic = new Magic();

        parent::setUp();
    }

    public function testResolveClassByNameWithoutConstructor()
    {
        /** @var Simplest $obj */
        $obj = $this->magic->get(Simplest::class);
        $this->assertEquals('b', $obj->a);
    }

    public function testResolveClassByNameWithScalarParamConstructor()
    {
        $this->magic->param('name', 'Anis');

        /** @var GreetWithConstructor $obj */
        $obj = $this->magic->get(GreetWithConstructor::class);
        $this->assertEquals('Hello Anis', $obj->greet());
    }

    public function testResolveClassByNameWithObjectParamConstructor()
    {
        $this->magic->param('name', 'Anis');
        $this->magic->param('email', 'anis@test.tld');

        /** @var GreetMailerWithConstructor $obj */
        $obj = $this->magic->get(GreetMailerWithConstructor::class);
        $this->assertEquals('Mailing "Hello Anis" to anis@test.tld', $obj->mail());
    }
}

