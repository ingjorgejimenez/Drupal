<?php
// modules/custom/drupal_estrenar/src/EventSubscriber/DrupalEstrenarEventSubscriber.php

namespace Drupal\drupal_estrenar\EventSubscriber;

use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class DrupalEstrenarEventSubscriber implements EventSubscriberInterface
{
    use StringTranslationTrait;

    public function checkRestfulModule(RequestEvent $event)
    {
        $current_path = \Drupal::service('path.current')->getPath();

        // Si la ruta actual es /example-module/data y el módulo restful_module no está instalado
        if ($current_path == '/example-module/data' && !\Drupal::moduleHandler()->moduleExists('restful_module')) {
            $message = $this->t('Debes instalar el modulo restful_module para ver los datos');
            $messenger = \Drupal::messenger();
            $messenger->addWarning($message);
        }

    }

    public static function getSubscribedEvents()
    {
        $events[KernelEvents::REQUEST][] = ['checkRestfulModule'];
        return $events;
    }

}
