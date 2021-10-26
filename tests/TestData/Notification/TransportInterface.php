<?php

namespace Ajaxray\Test\TestData\Notification;

interface TransportInterface
{
    public function name() :string;
    public function send(string $message, string $to, ?string $subject = null) :bool;
}