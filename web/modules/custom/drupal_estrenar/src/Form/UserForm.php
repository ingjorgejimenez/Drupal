<?php namespace Drupal\drupal_estrenar\Form;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\State\StateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class UserForm extends FormBase
{
    protected $messenger;
    protected $state;

    public function __construct(MessengerInterface $messenger, StateInterface $state)
    {
        $this->messenger = $messenger;
        $this->state = $state;
    }

    public function getFormId()
    {
        return 'user_form_form';
    }

    public function buildForm(array $form, FormStateInterface $form_state)
    {
        // Obtener las opciones de la tabla.
        $query = \Drupal::database()->select('cargo_options', 'o');
        $query->fields('o', ['value', 'label']);
        $cargos = $query->execute()->fetchAllAssoc('value');
        foreach ($cargos as $cargo) {
            $options[$cargo->value] = $this->t($cargo->label);
        }
        $form['name'] = [
            '#type' => 'textfield', // Tipo de elemento: campo de texto
            '#title' => $this->t('Nombre'), // Título del campo
            '#ajax' => [
                'callback' => '::ajaxCallback',
                'event' => 'blur',
                'event_modifier' => 'once',
                'wrapper' => 'ajax-wrapper',
                'progress' => [
                    'type' => 'throbber',
                    'message' => $this->t('Cargando...'),
                ],
            ],
            '#required' => true, // Campo requerido
            '#maxlength' => 255, // Longitud máxima del campo
            '#prefix' => '<div class="field-wrapper">',
            '#suffix' => '</div>',
        ];

        $form['name_error_message'] = [
            '#type' => 'html_tag',
            '#tag' => 'div',
            '#attributes' => ['class' => 'error-message name-error-message'],
            '#value' => '',
        ];
        $form['identification'] = [
            '#type' => 'textfield',
            '#title' => $this->t('Identificación'),
            '#required' => true,
            '#placeholder' => $this->t('Identification'),
            '#ajax' => [
                'callback' => '::ajaxCallback',
                'event' => 'change',
                'wrapper' => 'ajax-wrapper',
                'progress' => [
                    'type' => 'throbber',
                    'message' => $this->t('Cargando...'),
                ],
            ],
        ];

        $form['identification_error_message'] = [
            '#type' => 'html_tag',
            '#tag' => 'div',
            '#attributes' => ['class' => 'error-message identification-error-message'],
            '#value' => '',
        ];

        $form['birthdate'] = [
            '#type' => 'date',
            '#title' => $this->t('Fecha de nacimiento'),
            '#required' => true,
            '#description' => $this->t('Ingrese su fecha de nacimiento en el formato YYYY-MM-DD.'),
            '#ajax' => [
                'callback' => '::ajaxCallback',
                'event' => 'change', // Evento 'change' para ejecutar AJAX cuando cambie el valor.
                'wrapper' => 'ajax-wrapper',
                'progress' => [
                    'type' => 'throbber',
                    'message' => $this->t('Cargando...'),
                ],
            ],
        ];
        $form['birthdate_error_message'] = [
            '#type' => 'html_tag',
            '#tag' => 'div',
            '#attributes' => ['class' => 'error-message birthdate_error_message'],
            '#value' => '',
        ];

        $form['cargo'] = [
            '#type' => 'select',
            '#title' => $this->t('Cargo'),
            '#required' => true,
            '#options' => $options,
            '#value_callback' => '::setSelectedCargoValue',
            '#ajax' => [
                'callback' => '::ajaxCallback',
                'event' => 'change',
                'wrapper' => 'ajax-wrapper',
                'progress' => [
                    'type' => 'throbber',
                    'message' => $this->t('Cargando...'),
                ],
            ],
        ];
        $form['cargo_error_message'] = [
            '#type' => 'html_tag',
            '#tag' => 'div',
            '#attributes' => ['class' => 'error-message cargo-error-message'],
            '#value' => '',
        ];

        $form['submit'] = [
            '#type' => 'submit',
            '#value' => $this->t('Enviar'),
        ];
        $form['#attached']['library'][] = 'drupal_estrenar/user-form-css';

        return $form;
    }

    /* public function validateForm(array &$form, FormStateInterface $form_state)
    {
    $name = $form_state->getValue('name');
    if (!preg_match('/^[a-zA-Z]+$/', $name)) {
    $form_state->setErrorByName('name', $this->t('El nombre solo debe contener letras.'));
    }

    $cargo = $form_state->getValue('cargo');
    if (!preg_match('/^[a-zA-Z]+$/', $cargo)) {
    $form_state->setErrorByName('cargo', $this->t('El apellido solo debe contener caracteres alfanuméricos.'));
    }

    $identification = $form_state->getValue('identification');
    if (!preg_match('/^[a-zA-Z0-9]+$/', $identification)) {
    $form_state->setErrorByName('identification', $this->t('La identificación solo debe contener caracteres alfanuméricos.'));
    }

    $birthdate = $form_state->getValue('birthdate');
    if (!$this->validateDate($birthdate)) {
    $form_state->setErrorByName('birthdate', $this->t('La fecha de nacimiento no es válida.'));
    }

    }*/

    public function ajaxCallback(array &$form, FormStateInterface $form_state)
    {
        $response = new AjaxResponse();
        // Obtener el nombre del elemento que desencadenó el evento AJAX.
        $triggering_element = $form_state->getTriggeringElement();
        $field_name = $triggering_element['#name'];

        // Realizar la validación correspondiente al campo.
        $field_value = $form_state->getValue($field_name);
        $error_message = '';

        if ($field_name === 'name') {
            if (!preg_match('/^[a-zA-Z]+$/', $field_value)) {
                $error_message = $this->t('¡Verificar los datos del campo Nombre!');
            } elseif ($field_value === '') {
                $error_message = $this->t('¡Campo obligatorio!');
            }
        } elseif ($field_name === 'identification') {
            if (!preg_match('/^[0-9]+$/', $field_value)) {
                $error_message = $this->t('¡Verificar los datos del campo Identificación!');
            } elseif ($field_value === '') {
                $error_message = $this->t('¡Campo obligatorio!');
            }
        } elseif ($field_name === 'birthdate') {
            if (!$this->validateDate($field_value)) {
                $error_message = $this->t('¡Verificar los datos del campo Fecha de Nacimiento!');
            } elseif ($field_value === '') {
                $error_message = $this->t('¡Campo obligatorio!');
            }
        } elseif ($field_name === 'cargo') {
            if (!preg_match('/^[a-zA-Z]+$/', $field_value)) {
                $error_message = $this->t('¡Verificar los datos del campo Cargo!');
            } elseif ($field_value === '') {
                $error_message = $this->t('¡Campo obligatorio!');
            }
        }

        // Agregar el comando de respuesta al elemento de error correspondiente.
        $error_selector = '.' . $field_name . '-error-message';
        $response->addCommand(new HtmlCommand($error_selector, $error_message));

        return $response;
    }
    public static function create(ContainerInterface $container)
    {
        return new static(
            $container->get('messenger'),
            $container->get('state')
        );
    }

    public function submitForm(array &$form, FormStateInterface $form_state)
    {

        $name = $form_state->getValue('name');
        $identification = $form_state->getValue('identification');
        $birthdate = $form_state->getValue('birthdate');
        $birthdate_value = new \DateTime($birthdate);
        $cargo = $form_state->getValue('cargo');

        $estado = ($cargo === 'Administrador') ? 1 : 0;

        // Insertar datos en la tabla.
        $fields = [
            'nombre' => $name,
            'identificacion' => $identification,
            'cargo' => $cargo,
            'fecha_nacimiento' => $birthdate_value->format('Y-m-d H:i:s'), // Formatear como datetime.
            'estado' => $estado,
        ];
        \Drupal::database()->insert('example_users')
            ->fields($fields)
            ->execute();

        $this->messenger->addStatus($this->t('El formulario ha sido enviado. Gracias.'));
    }

    protected function validateDate($date)
    {
        $format = 'Y-m-d';
        $date_obj = \DateTime::createFromFormat($format, $date);
        return $date_obj && $date_obj->format($format) === $date;
    }
    public static function setSelectedCargoValue($element, $input, FormStateInterface $form_state)
    {
        // Devuelve el valor seleccionado por el usuario o null si no se ha seleccionado nada.
        return isset($input['cargo']) ? $input['cargo'] : null;
    }

}
