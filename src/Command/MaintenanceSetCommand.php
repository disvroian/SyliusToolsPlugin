<?php
declare(strict_types=1);

namespace Eknow\SyliusToolsPlugin\Command ;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand ;
use Symfony\Component\Console\Input\InputInterface ;
use Symfony\Component\Console\Output\OutputInterface ;
use Symfony\Component\Console\Input\InputArgument ;

use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Filesystem\Filesystem;

class MaintenanceSetCommand extends ContainerAwareCommand
{
  protected $_lockFile ;

  public function __construct($maintenance)
  {
    $this->_lockFile= $maintenance["lockFilePath"] ;
    parent::__construct() ;
  }

  protected static $defaultName = 'sylius:maintenance:set';

  /**
   * {@inheritdoc}
   */
  protected function configure(): void
  {
    $this
        ->setDescription('Disable/Enable the maintenance page using enable/disable')
        ->addArgument('enable_disable', InputArgument::REQUIRED, 'Choose if you want enable or disable the maintenance page.') ;
  }

  /**
   * {@inheritdoc}
   */
  protected function execute(InputInterface $input, OutputInterface $output)
  {
    $filesystem= new Filesystem() ;
    if( $filesystem->exists($this->_lockFile)) {
      if( $input->getArgument('enable_disable') == "disable" ) {
        $filesystem->remove($this->_lockFile) ;
        $output->writeln('Maintenance page is now <info>disable</info>') ;
      }
    }
    else {
      if( !$filesystem->exists($this->_lockFile)) {
        if( $input->getArgument('enable_disable') == "enable" ) {
          $filesystem->touch($this->_lockFile) ;
          $output->writeln('Maintenance page is now <info>enable</info>') ;
        }
      }
    }
    return 0;
  }
}
