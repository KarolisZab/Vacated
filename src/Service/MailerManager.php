<?php

namespace App\Service;

use App\Entity\User;
use App\Trait\LoggerTrait;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class MailerManager
{
    use LoggerTrait;

    private const FROM_EMAIL = 'vacated.dev@gmail.com';

    public function __construct(
        private EntityManagerInterface $entityManager,
        private MailerInterface $mailer
    ) {
    }

    public function sendWelcomeEmail(string $email, string $password)
    {
        $email = (new Email())
            ->from(self::FROM_EMAIL)
            ->to($email)
            ->subject('Welcome to our website!')
            ->text(
                "Greetings!\n
                \nThank you for joining us.\n
                \nYour email: {$email}\n
                Your password: {$password}"
            )
            ->html(
                "<p>Greetings!</p>
                <p>Thank you for joining us.</p>
                <p>Your email: {$email}</p>
                <p>Your password: {$password}</p>"
            );

        $this->mailer->send($email);
    }

    public function sendNotificationToAdmins(string $message)
    {
        $admins = $this->entityManager->getRepository(User::class)->findBy(['isAdmin' => true]);

        foreach ($admins as $admin) {
            $email = (new Email())
                ->from(self::FROM_EMAIL)
                ->to($admin->getEmail())
                ->subject('New Vacation Request Notification')
                ->text($message);

            $this->mailer->send($email);
        }
    }

    public function sendEmailToUser(string $email, string $subject, string $message)
    {
        $email = (new Email())
            ->from(self::FROM_EMAIL)
            ->to($email)
            ->subject($subject)
            ->text($message);

        $this->mailer->send($email);
    }
}
