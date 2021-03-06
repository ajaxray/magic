<?php
namespace Ajaxray\Test;

use Ajaxray\Magic\Magic;
use Ajaxray\Test\TestData\ChainedConstruct;
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
class ObjectChainingTest extends TestCase
{
    private Magic $magic;

    protected function setUp(): void
    {
        $this->magic = new Magic();

        parent::setUp();
    }

    public function testResolveClassesInChainedObjectGraph()
    {
        /** @var ChainedConstruct $chain */
        $chain = $this->magic->get(ChainedConstruct::class);
        $this->assertEquals('Wof! Wof!', $chain->a->b->Bark());
    }
}

