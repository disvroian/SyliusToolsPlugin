<?php
declare(strict_types=1);

namespace Eknow\SyliusToolsPlugin\EventListener ;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;

class MaintenanceListener {

  /**
   * @var Twig_Environment
   */
  private $twig ;

  private $ipAuthorized= [] ;

  private $lockFilePath ;

  public function __construct($parameters,
                          \Twig_Environment $twig)
  {
    $this->lockFilePath= $parameters[0]["lockFilePath"] ;
    $this->ipAuthorized= $parameters[0]["ipAuthorized"] ;
    $this->twig= $twig ;
  }

  public function onKernelRequest(GetResponseEvent $event)
  {

    $request= $event->getRequest();
    if(strpos("sylius_admin", $request->attributes->get('_route')) === false) {

      $currentIP= $_SERVER['REMOTE_ADDR'] ;
      if( file_exists($this->lockFilePath) ) {
        if( !in_array($currentIP, $this->ipAuthorized)) {
  // We load our maintenance template
          $template= $this->twig->render('@EknowSyliusToolsPlugin/maintenance.html.twig') ;

  // We send our response with a 503 response code (service unavailable)
          $event->setResponse(
                    new Response(
                            $template,
                            Response::HTTP_SERVICE_UNAVAILABLE
                    )
          ) ;
          $event->stopPropagation() ;
        }
      }
    }
  }
}
