<?php

namespace AppBundle\Command;

use AppBundle\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

class CreateAdminCommand extends ContainerAwareCommand
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        parent::__construct();
        $this->em = $em;
    }

    protected function configure()
    {
        $this
                ->setName('app:create-admin')
                ->setDescription('Creates a new super-admin.')
                ->setHelp('This command allows you to create a new user with
                    super-admin privileges.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $helper = $this->getHelper('question');

        $output->writeln([
                '',
                'Admin Creator',
                '=============',
                '',
                'Now you must answer the following questions to create a new '
                . 'user with admin privileges.',
                ''
        ]);

        $usernameQuestion = new Question('Enter the username '
                . '[<comment>admin</comment>]: ', 'admin');
        $emailQuestion = new Question('Enter the email: ');
        $passwordQuestion = new Question('Enter the password: ');
        $passwordQuestion->setHidden(true);
        $passwordQuestion->setHiddenFallback(false);
        $repeatPasswordQuestion = new Question('Re-enter the password: ');
        $repeatPasswordQuestion->setHidden(true);
        $repeatPasswordQuestion->setHiddenFallback(false);
        $nameQuestion = new Question('Enter the name '
                . '[<comment>Admin</comment>]: ', 'Admin');

        $username = $helper->ask($input, $output, $usernameQuestion);
        $email = $helper->ask($input, $output, $emailQuestion);
        $password = $helper->ask($input, $output, $passwordQuestion);
        $repeatPassword = $helper->ask($input, $output, $repeatPasswordQuestion);
        $name = $helper->ask($input, $output, $nameQuestion);

        if ($email) {
            if (($password && $repeatPassword) &&
                    ($password === $repeatPassword)) {
                $user = new User();
                $passwordEncoder = $this->getContainer()
                        ->get('security.password_encoder');
                $encodedPassword = $passwordEncoder
                        ->encodePassword($user, $password);
                $user->setUsername($username);
                $user->setEmail($email);
                $user->setPassword($encodedPassword);
                $user->setName($name);
                $user->setRoles(array('ROLE_SUPER_ADMIN'));
                $this->em->persist($user);
                $flush = $this->em->flush();
                if (!$flush) {
                    $output->write('<info>User added properly.</info>');
                } else {
                    $output->write('<error>Something went wrong when'
                            . 'adding the new user.</error>');
                }
            } else {
                $output->write('<error>Both passwords must be equals and '
                        . 'contain some character.</error>');
            }
        } else {
            $output->write('<error>You must enter a valid '
                    . 'email address.</error>');
        }
    }
}
