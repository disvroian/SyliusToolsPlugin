<?php
declare(strict_types=1);

namespace Eknow\SyliusToolsPlugin\Command ;

use Sylius\Component\Core\Model\AdminUserInterface ;
use Sylius\Component\User\Repository\UserRepositoryInterface ;
use Symfony\Component\Console\Helper\QuestionHelper ;
use Symfony\Component\Console\Question\Question ;
use Symfony\Component\Console\Style\SymfonyStyle ;
use Symfony\Component\Validator\Constraints\NotBlank ;
use Symfony\Component\Validator\ConstraintViolationListInterface ;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand ;
use Symfony\Component\Console\Input\InputInterface ;
use Symfony\Component\Console\Output\OutputInterface ;

class UnlockAdminCommand extends ContainerAwareCommand
{

  protected static $defaultName = 'sylius:admin:unlock' ;

    /**
     * {@inheritdoc}
     */
  protected function configure(): void
  {
    $this
        ->setName('sylius:admin:unlock')
        ->setDescription('Unlock the admin user on backend with his ID');
  }

  /**
   * {@inheritdoc}
   */
  protected function execute(InputInterface $input, OutputInterface $output)
  {
    $output->writeln('Command will unlock the administrator.') ;
    $this->setupAdministratorUser($input, $output) ;
    return 0;
  }

  protected function setupAdministratorUser(InputInterface $input, OutputInterface $output): void
  {
      $outputStyle = new SymfonyStyle($input, $output);
      $outputStyle->writeln('Unlock an administrator account.');

      $userManager= $this->getContainer()->get('sylius.manager.admin_user');

      /** @var UserRepositoryInterface $userRepository */
      $userRepository= $this->getAdminUserRepository() ;
      $user= $userRepository->find($this->getAdministratorId($input, $output)) ;
      $user->setEnabled(true) ;
      $user->setLocked(false) ;
      $userManager->persist($user) ;
      $userManager->flush() ;

      $outputStyle->writeln('Administrator account <info>successfully unlocked.</info>') ;
      $outputStyle->newLine() ;
  }

  private function createIdQuestion(): Question
  {
      return (new Question('Administrator ID: '))
          ->setValidator(function ($value) {
              /** @var ConstraintViolationListInterface $errors */
              $errors = $this->getContainer()->get('validator')->validate((string) $value, [new NotBlank()]);
              foreach ($errors as $error) {
                  throw new \DomainException($error->getMessage());
              }

              return $value;
          })
          ->setMaxAttempts(3)
      ;
  }

  private function getAdministratorId(InputInterface $input, OutputInterface $output): string
  {
      /** @var QuestionHelper $questionHelper */
      $questionHelper = $this->getHelper('question');
      /** @var UserRepositoryInterface $userRepository */
      $userRepository = $this->getAdminUserRepository();
      do {
          $question = $this->createIdQuestion();
          $id= $questionHelper->ask($input, $output, $question);
          if($id!= "") {
            return $id ;
          }
      } while ($id != "") ;
  }

  private function getAdminUserRepository(): UserRepositoryInterface
  {
      return $this->getContainer()->get('sylius.repository.admin_user');
  }
}
