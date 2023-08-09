<?php
namespace Drupal\drupal_estrenar\Controller;

use Drupal\Core\Controller\ControllerBase;

class DataController extends ControllerBase
{
    public function DataMethod()
    {
        // verificar si el mopdulo esta instalado
        if (!\Drupal::moduleHandler()->moduleExists('restful_module')) {
            // return new RedirectResponse('/example-module/data');
            $message = $this->t('Debes instalar el modulo restful_module para ver los datos de la API');
            $this->messenger()->addWarning($message);
            return [];
        }
    }
}
