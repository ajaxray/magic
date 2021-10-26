<?php
namespace Ajaxray\Test\TestData;

interface MailerInterface
{
    public function mail(string $body, string $subject, string $email);
}