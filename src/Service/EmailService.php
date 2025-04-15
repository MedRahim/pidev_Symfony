<?php

namespace App\Service;

use Symfony\Component\Mime\Email;
use Symfony\Component\Mailer\MailerInterface;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class EmailService
{
    private MailerInterface $mailer;

    public function __construct(MailerInterface $mailer,private Environment $twig)
    {
        $this->mailer = $mailer;
    }

    public function sendEmail(
        string $to,
        string $subject,
        string $template,
        array $context = []
    ): void {
        try {
            $email = (new Email())
                ->from('mekkiamine34@gmail.com')
                ->to($to)
                ->subject($subject)
                ->html($this->twig->render($template, $context));
        } catch (LoaderError $e) {

        }

        $this->mailer->send($email);
    }

    private function renderTemplate(string $template, array $context): string
    {
        // Implement your template rendering logic
        // Could use Twig or plain PHP
        extract($context);
        ob_start();
        include $template;
        return ob_get_clean();
    }
}
