<?php

namespace Ajaxray\Test\TestData\Notification;

class SMSTransport implements TransportInterface
{

    public function name(): string
    {
        return 'SMS';
    }

    public function send(string $message, string $to, ?string $subject = null): bool
    {
        return true;
    }
}