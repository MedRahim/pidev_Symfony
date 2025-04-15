<?php

namespace App\Service;

use Symfony\Component\Mime\Email;
use Symfony\Component\Mailer\MailerInterface;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Psr\Log\LoggerInterface;

class EmailService
{
    private MailerInterface $mailer;
    private LoggerInterface $logger;

    public function __construct(MailerInterface $mailer, LoggerInterface $logger, private Environment $twig)
    {
        $this->mailer = $mailer;
        $this->logger = $logger;
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
