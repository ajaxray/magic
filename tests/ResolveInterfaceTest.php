<?php
namespace Ajaxray\Test;

use Ajaxray\Magic\Magic;
use Ajaxray\Test\TestData\MailerImplementation;
use Ajaxray\Test\TestData\MailerInterface;
use Ajaxray\Test\TestData\Notification\Notifier;
use Ajaxray\Test\TestData\Notification\SMSTransport;
use Ajaxray\Test\TestData\Notification\TransportInterface;
use Ajaxray\Test\TestData\Simplest;
use PHPUnit\Framework\TestCase;

/**
 *  Testng if implementation of interface can be resolved
 *
 * @author Anis Uddin Ahmad <anis.programmer@gmail.com>
 */
class ResolveInterfaceTest extends TestCase
{
    private Magic $magic;

    protected function setUp(): void
    {
        $this->magic = new Magic();

        parent::setUp();
    }

    public function testServiceLoadingByInterfaceIfSingleImplementation()
    {
        include_once 'TestData/MailerInterface.php';
        include_once 'TestData/MailerImplementation.php';

        $this->magic->map('my-mailer', MailerInterface::class);

        /** @var MailerImplementation $obj */
        $obj = $this->magic->get('my-mailer');
        $result = "Sending 'Hi there' to abc@xyz.tld with subject 'Hello'";
        $this->assertEquals($result, $obj->mail("Hi there", "Hello", "abc@xyz.tld"));
    }

    public function testResolveInterfaceTypeHintToImplementationIfSingleImplementation()
    {
        include_once 'TestData/Notification/SMSTransport.php';

        $this->magic->map('notifier', Notifier::class, ['receiver' => 'receiver@xyz.tld']);

        /** @var Notifier $obj */
        $obj = $this->magic->get('notifier');
        $result = "Notified receiver@xyz.tld using SMS - Something happened";
        $this->assertEquals($result, $obj->notify("Something happened"));
    }

    public function testServiceLoadingByMappedInterface()
    {
        $this->magic->map('mailer', MailerInterface::class);
        $this->magic->mapInterface(MailerInterface::class, MailerImplementation::class);

        /** @var MailerImplementation $obj */
        $obj = $this->magic->get('mailer');
        $result = "Sending 'Hi there' to abc@xyz.tld with subject 'Hello'";
        $this->assertEquals($result, $obj->mail("Hi there", "Hello", "abc@xyz.tld"));
    }

    public function testResolveMappedInterfaceTypeHintToImplementation()
    {
        $this->magic->map('notifier', Notifier::class, ['receiver' => 'receiver@xyz.tld']);
        $this->magic->mapInterface(TransportInterface::class, SMSTransport::class);

        /** @var Notifier $obj */
        $obj = $this->magic->get('notifier');
        $result = "Notified receiver@xyz.tld using SMS - Something happened";
        $this->assertEquals($result, $obj->notify("Something happened"));
    }
}
