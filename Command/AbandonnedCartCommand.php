<?php
declare(strict_types=1);

namespace Eknow\SyliusToolsPlugin\Command ;

use Sylius\Component\Order\Repository\OrderRepositoryInterface;

use Sylius\Component\User\Model\UserInterface ;
use Symfony\Component\Console\Input\InputArgument ;
use Symfony\Component\Console\Input\InputInterface ;
use Symfony\Component\Console\Output\OutputInterface ;
use Symfony\Component\Console\Question\Question ;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand ;

class AbandonnedCartCommand extends ContainerAwareCommand
{
  protected static $defaultName = 'sylius:cart:abandonned' ;

  /** @var ObjectManager */
  private $orderManager;

  /** @var OrderRepositoryInterface */
  private $orderRepository;

  public function __construct(?string $name = null)
  {
    parent::__construct($name);
  }

  /**
   * {@inheritdoc}
   */
  protected function configure()
  {
    $this
        ->setDescription('Clean all abandonned carts.')
        ->setDefinition([
          new InputArgument('days', InputArgument::REQUIRED, 'Days')
        ]);
  }

  /**
   * {@inheritdoc}
   */
  protected function execute(InputInterface $input, OutputInterface $output)
  {
    $this->orderRepository= $this->getContainer()->get('sylius.repository.order') ;
    $this->orderManager= $this->getContainer()->get('sylius.manager.order') ;
    $days= $input->getArgument('days') ;

    $date = new \DateTime(\Date("Y-m-d", strtotime(date("Y-m-d") . " -" . $days . " Day")));

    $output->writeln(sprintf('Every cart not modified since <comment>%s</comment> will be removed', $date->format("Y-m-d"))) ;

    $expiredCarts= $this->orderRepository->findCartsNotModifiedSince($date) ;
    foreach($expiredCarts as $expiredCart) {
      $this->orderManager->remove($expiredCart) ;
      $output->writeln(sprintf('Cart ID <comment>%s</comment> ready to deleted', $expiredCart->getId())) ;
    }
    $this->orderManager->flush() ;
    $output->writeln('All abandonned cart successfully deleted') ;
  }

  /**
   * {@inheritdoc}
   */
  protected function interact(InputInterface $input, OutputInterface $output)
  {
    $helper= $this->getHelper('question') ;

    if (!$input->getArgument('days')) {
      $question = new Question('Please enter an number of days:', false) ;
      $question->setNormalizer(function ($value) {
        if (empty($value)) {
          throw new \Exception('Email can not be empty');
        }
        return $value;
      });

      $days= $helper->ask($input, $output, $question);
      $input->setArgument('days', $days);
    }
  }
}
