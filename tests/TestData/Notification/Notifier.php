<?php

namespace Ajaxray\Test\TestData\Notification;

class Notifier
{
    public function __construct(
        private TransportInterface $transport,
        private string             $receiver
    )
    {
    }

    public function notify(string $message): string
    {
        $this->transport->send($message, $this->receiver);
        return "Notified {$this->receiver} using {$this->transport->name()} - $message";
    }
}