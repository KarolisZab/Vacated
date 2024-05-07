<?php

namespace App\Service\Mailer;

interface MailerManagerInterface
{
    public function sendWelcomeEmail(string $email, string $password);

    public function sendNotificationToAdmins(string $message);

    public function sendEmailToUser(string $email, string $subject, string $message);
}
