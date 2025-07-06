<?php

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:create-admin',
    description: 'Создаёт администратора c ролью ROLE_ADMIN',
)]
class CreateAdminCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $em,
        private UserPasswordHasherInterface $passwordHasher
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('nickname', InputArgument::REQUIRED, 'Никнейм администратора')
            ->addArgument('password', InputArgument::REQUIRED, 'Пароль администратора');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $nickname = $input->getArgument('nickname');
        $password = $input->getArgument('password');
        $existingUser = $this->em->getRepository(User::class)->findOneBy(['nickname' => $nickname]);
        if ($existingUser) {
            $output->writeln("<error>Пользователь с ником '$nickname' уже существует!</error>");
            return Command::FAILURE;
        }

        $user = new User();
        $user->setNickname($nickname);
        $user->setRoles(['ROLE_ADMIN']);
        $hashedPassword = $this->passwordHasher->hashPassword($user, $password);
        $user->setPassword($hashedPassword);

        $this->em->persist($user);
        $this->em->flush();

        $output->writeln("<info>Администратор '$nickname' успешно создан.</info>");
        return Command::SUCCESS;
    }
}
