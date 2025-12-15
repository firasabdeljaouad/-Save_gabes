<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Address;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

#[AsCommand(
    name: 'app:test-email',
    description: 'Tests email sending configuration',
)]
class TestEmailCommand extends Command
{
    public function __construct(
        private MailerInterface $mailer,
        #[Autowire(param: 'app.mailer.sender_email')] private string $senderEmail,
        #[Autowire(param: 'app.mailer.sender_name')] private string $senderName
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Testing email sending...');
        $output->writeln('From: ' . $this->senderEmail . ' (' . $this->senderName . ')');

        try {
            $email = (new Email())
                ->from(new Address($this->senderEmail, $this->senderName))
                ->to($this->senderEmail) // Send to self for testing
                ->subject('Test Email from Save Gabes')
                ->text('This is a test email to verify the mailer configuration.');

            $this->mailer->send($email);

            $output->writeln('<info>Email sent successfully!</info>');
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $output->writeln('<error>Error sending email: ' . $e->getMessage() . '</error>');
            return Command::FAILURE;
        }
    }
}
