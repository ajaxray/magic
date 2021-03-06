<?php

namespace Ajaxray\Test\TestData;

class MailerImplementation implements MailerInterface
{
    public string $channel = 'email';

    public function mail(string $body, string $subject, string $email)
    {
        return "Sending '$body' to $email with subject '$subject'";
    }
}