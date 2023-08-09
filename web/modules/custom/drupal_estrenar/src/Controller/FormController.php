<?php
namespace Drupal\drupal_estrenar\Controller;

use Drupal\Core\Controller\ControllerBase;

class FormController extends ControllerBase
{
    public function FormMethod()
    {
        $form = \Drupal::formBuilder()->getForm('Drupal\drupal_estrenar\Form\UserForm');
        return [
            'form' => $form,
        ];
    }
}
