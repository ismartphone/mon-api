<?php

namespace App\Command;

use App\Service\UserService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Validator\Exception\ValidationFailedException;

#[AsCommand(name: 'app:create-user', description: 'Creates a user account (use --admin for an administrator)')]
final class CreateUserCommand extends Command
{
    public function __construct(private readonly UserService $userService)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('email', InputArgument::REQUIRED, 'Email address')
            ->addArgument('username', InputArgument::REQUIRED, 'Public username')
            ->addArgument('password', InputArgument::REQUIRED, 'Plain password')
            ->addOption('admin', null, InputOption::VALUE_NONE, 'Give the ROLE_ADMIN role');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $roles = $input->getOption('admin') ? ['ROLE_ADMIN'] : [];

        try {
            $user = $this->userService->create(
                (string) $input->getArgument('email'),
                (string) $input->getArgument('username'),
                (string) $input->getArgument('password'),
                $roles,
            );
        } catch (UnprocessableEntityHttpException $exception) {
            $previous = $exception->getPrevious();
            $io->error($previous instanceof ValidationFailedException ? (string) $previous->getViolations() : $exception->getMessage());

            return Command::FAILURE;
        }

        $io->success(\sprintf('User "%s" created with roles: %s.', $user->getEmail(), implode(', ', $user->getRoles())));

        return Command::SUCCESS;
    }
}
