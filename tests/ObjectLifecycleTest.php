<?php
namespace Ajaxray\Test;

use Ajaxray\Magic\Magic;
use Ajaxray\Test\TestData\GreetMailerWithConstructor;
use Ajaxray\Test\TestData\GreetWithConstructor;
use Ajaxray\Test\TestData\MailerImplementation;
use Ajaxray\Test\TestData\MailerInterface;
use Ajaxray\Test\TestData\Simplest;
use PHPUnit\Framework\TestCase;

/**
 *  Testng same object (singleton) and different object (factory)
 *
 * @author Anis Uddin Ahmad <anis.programmer@gmail.com>
 */
class ObjectLifecycleTest extends TestCase
{
    private Magic $magic;

    protected function setUp(): void
    {
        $this->magic = new Magic();

        parent::setUp();
    }


    public function testProvidesSameInstanceForMultipleGetCallByDefault()
    {
        $this->magic->map('simple', Simplest::class);

        /** @var Simplest $obj1st */
        $obj1st = $this->magic->get('simple');
        $obj1st->property = 'Updated';

        /** @var Simplest $obj2nd */
        $obj2nd = $this->magic->get('simple');
        $this->assertEquals('Updated', $obj2nd->property);
    }

    public function testProvidesSameInstanceForMultipleGetCallOfInterface()
    {
        $this->magic->map('mailer', MailerInterface::class);
        $this->magic->mapInterface(MailerInterface::class, MailerImplementation::class);

        /** @var MailerImplementation $obj1st */
        $obj1st = $this->magic->get('mailer');
        $obj1st->channel = 'Updated Email';

        /** @var MailerImplementation $obj2nd */
        $obj2nd = $this->magic->get('mailer');
        $this->assertEquals('Updated Email', $obj2nd->channel);
    }


    public function testProvidesSameInstanceForMultipleGetCallOfCallbackBinding()
    {
        $this->magic->map('mailer', fn($m, $params) => new MailerImplementation());

        /** @var MailerImplementation $obj1st */
        $obj1st = $this->magic->get('mailer');
        $obj1st->channel = 'Updated Email';

        /** @var MailerImplementation $obj2nd */
        $obj2nd = $this->magic->get('mailer');
        $this->assertEquals('Updated Email', $obj2nd->channel);
    }

    public function testServiceCachingCanBeDisabledForClassMapping()
    {
        $this->magic->map('simple', Simplest::class, ['@cacheable' => false]);

        /** @var Simplest $obj1st */
        $obj1st = $this->magic->get('simple');
        $obj1st->property = 'Updated';

        /** @var Simplest $obj2nd */
        $obj2nd = $this->magic->get('simple');
        $this->assertEquals('value', $obj2nd->property);
        $obj2nd->property = 'Again Updated';

        /** @var Simplest $obj3rd */
        $obj3rd = $this->magic->get('simple');
        $this->assertEquals('value', $obj3rd->property);
    }

    public function testServiceCachingCanBeDisabledForInterface()
    {
        $this->magic->map('mailer', MailerInterface::class, ['@cacheable' => false]);
        $this->magic->mapInterface(MailerInterface::class, MailerImplementation::class);

        /** @var MailerImplementation $obj1st */
        $obj1st = $this->magic->get('mailer');
        $obj1st->channel = 'Updated Email';

        /** @var MailerImplementation $obj2nd */
        $obj2nd = $this->magic->get('mailer');
        $this->assertEquals('email', $obj2nd->channel);
    }

    public function testServiceCachingCanBeDisabledForCallbackBinding()
    {
        $this->magic->map('mailer', fn($m, $params) => new MailerImplementation(), ['@cacheable' => false]);

        /** @var MailerImplementation $obj1st */
        $obj1st = $this->magic->get('mailer');
        $obj1st->channel = 'Updated Email';

        /** @var MailerImplementation $obj2nd */
        $obj2nd = $this->magic->get('mailer');
        $this->assertEquals('email', $obj2nd->channel);
    }

}
