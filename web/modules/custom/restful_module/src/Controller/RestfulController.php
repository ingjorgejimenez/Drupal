<?php

namespace Drupal\restful_module\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class RestfulController extends ControllerBase
{

    public function listUsers()
    {
        $query = \Drupal::database()->select('example_users', 'eu')
            ->fields('eu')
            ->execute();

        $users = [];

        foreach ($query as $row) {
            $users[] = [
                'id' => $row->id,
                'nombre' => $row->nombre,
                'identificacion' => $row->identificacion,
                'fecha_nacimiento' => $row->fecha_nacimiento,
                'cargo' => $row->cargo,
                'estado' => $row->estado,
            ];
        }

        return new JsonResponse($users);
    }
    public function getUser($id)
    {
        // Consultar la base de datos para obtener los detalles del usuario por ID.
        $query = \Drupal::database()->select('example_users', 'u')
            ->fields('u')
            ->condition('id', $id)
            ->execute()
            ->fetchAssoc();

        if (!$query) {
            return new JsonResponse(['error' => 'El usuario no existe.'], 404); // Not Found
        }
        if (!is_numeric($id)) {
            return new JsonResponse(['error' => 'ID inválido.'], 400); // Bad Request
        }

        $userDetails = [
            'id' => $query['id'],
            'nombre' => $query['nombre'],
            'identificacion' => $query['identificacion'],
            'fecha_nacimiento' => $query['fecha_nacimiento'],
            'cargo' => $query['cargo'],
            'estado' => $query['estado'],
        ];

        return new JsonResponse($userDetails);

    }

    public function createUser(Request $request)
    {
        // Validar los datos recibidos del cliente.
        $data = json_decode($request->getContent(), true);

        if (empty($data['nombre']) || empty($data['identificacion']) || empty($data['fecha_nacimiento']) || empty($data['cargo']) || empty($data['estado'])) {
            return new JsonResponse(['error' => 'Todos los campos son requeridos.'], 400); // Bad Request
        }

        // Crear un nuevo usuario en la tabla example_users.
        $fields = [
            'nombre' => $data['nombre'],
            'identificacion' => $data['identificacion'],
            'fecha_nacimiento' => strtotime($data['fecha_nacimiento']),
            'cargo' => $data['cargo'],
            'estado' => $data['estado'],
        ];

        $result = \Drupal::database()->insert('example_users')
            ->fields($fields)
            ->execute();

        if ($result) {
            return new JsonResponse(['message' => 'Usuario creado correctamente.']);
        } else {
            return new JsonResponse(['error' => 'Error al crear el usuario.'], 500);
        }

        return new JsonResponse(['message' => 'Usuario creado exitosamente.']);
    }

    public function deleteUser(Request $request, $id)
    {
        // Verificar si el usuario existe antes de intentar eliminarlo.
        $user = \Drupal::database()->select('example_users', 'u')
            ->fields('u')
            ->condition('id', $id)
            ->execute()
            ->fetchAssoc();

        if (!$user) {
            return new JsonResponse(['error' => 'El usuario no existe.'], 404);
        }
        if (!$id || empty($id)) {
            return new JsonResponse(['error' => 'Falta el parámetro ID.'], 400);
        }

        // Eliminar el usuario de la tabla example_users.
        $result = \Drupal::database()->delete('example_users')
            ->condition('id', $id)
            ->execute();

        if ($result) {
            return new JsonResponse(['message' => 'Usuario eliminado correctamente.']);
        } else {
            return new JsonResponse(['error' => 'Error al eliminar el usuario.'], 500);
        }
    }

    public function updateUser(Request $request, $id)
    {
        // Verificar si el usuario existe antes de intentar actualizarlo.
        $user = \Drupal::database()->select('example_users', 'u')
            ->fields('u')
            ->condition('id', $id)
            ->execute()
            ->fetchAssoc();

        if (!$user) {
            return new JsonResponse(['error' => 'El usuario no existe.'], 404);
        }
        if (!$id || empty($id)) {
            return new JsonResponse(['error' => 'Falta el parámetro ID.'], 400);
        }

        $data = json_decode($request->getContent(), true);

        // Actualizar el usuario en la tabla example_users.
        $fields = [
            'nombre' => $data['nombre'],
            'identificacion' => $data['identificacion'],
            'fecha_nacimiento' => strtotime($data['fecha_nacimiento']),
            'cargo' => $data['cargo'],
            // Otros campos que quieras actualizar
        ];

        $result = \Drupal::database()->update('example_users')
            ->fields($fields)
            ->condition('id', $id)
            ->execute();

        if ($result) {
            return new JsonResponse(['message' => 'Usuario actualizado correctamente.']);
        } else {
            return new JsonResponse(['error' => 'Error al actualizar el usuario.'], 500);
        }
    }

}
