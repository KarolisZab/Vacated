<?php

namespace App\Service\Mailer;

use App\Service\Mailer\MailerManagerInterface;

class MockedMailerManager implements MailerManagerInterface
{
    public function sendWelcomeEmail(string $email, string $password)
    {
    }

    public function sendNotificationToAdmins(string $message)
    {
    }

    public function sendEmailToUser(string $email, string $subject, string $message)
    {
    }
}
