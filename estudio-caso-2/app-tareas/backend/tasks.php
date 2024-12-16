<?php

require 'db.php';

function crearTarea($user_id, $title, $description, $due_date) {
    global $pdo;
    try {
        $sql = "INSERT INTO tasks (user_id, title, description, due_date) values (:user_id, :title, :description, :due_date)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'user_id' => $user_id,
            'title' => $title,
            'description' => $description,
            'due_date' => $due_date
        ]);
        return $pdo->lastInsertId();
    } catch (Exception $e) {
        logError("Error creando tarea: " . $e->getMessage());
        return 0;
    }
}

function editarTarea($id, $title, $description, $due_date) {
    global $pdo;
    try {
        $sql = "UPDATE tasks set title = :title, description = :description, due_date = :due_date where id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'title' => $title,
            'description' => $description,
            'due_date' => $due_date,
            'id' => $id
        ]);
        return $stmt->rowCount() > 0;
    } catch (Exception $e) {
        logError($e->getMessage());
        return false;
    }
}

function obtenerTareasPorUsuario($user_id) {
    global $pdo;
    try {
        $sql = "SELECT * FROM tasks WHERE user_id = :user_id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['user_id' => $user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        logError("Error al obtener tareas: " . $e->getMessage());
        return [];
    }
}

function eliminarTarea($id) {
    global $pdo;
    try {
        $sql = "DELETE FROM tasks WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->rowCount() > 0;
    } catch (Exception $e) {
        logError("Error al eliminar la tarea: " . $e->getMessage());
        return false;
    }
}

// Funciones para manejar comentarios
function agregarComentario($task_id, $user_id, $comment) {
    global $pdo;
    try {
        $sql = "INSERT INTO comments (task_id, user_id, comment) VALUES (:task_id, :user_id, :comment)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'task_id' => $task_id,
            'user_id' => $user_id,
            'comment' => $comment
        ]);
        return $pdo->lastInsertId();
    } catch (Exception $e) {
        logError("Error agregando comentario: " . $e->getMessage());
        return 0;
    }
}

function editarComentario($id, $comment) {
    global $pdo;
    try {
        $sql = "UPDATE comments SET comment = :comment WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'comment' => $comment,
            'id' => $id
        ]);
        return $stmt->rowCount() > 0;
    } catch (Exception $e) {
        logError("Error editando comentario: " . $e->getMessage());
        return false;
    }
}

function eliminarComentario($id) {
    global $pdo;
    try {
        $sql = "DELETE FROM comments WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->rowCount() > 0;
    } catch (Exception $e) {
        logError("Error eliminando comentario: " . $e->getMessage());
        return false;
    }
}

function obtenerComentariosPorTarea($task_id) {
    global $pdo;
    try {
        $sql = "SELECT * FROM comments WHERE task_id = :task_id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['task_id' => $task_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        logError("Error al obtener comentarios: " . $e->getMessage());
        return [];
    }
}

$method = $_SERVER['REQUEST_METHOD'];
header('Content-Type: application/json');

function getJsonInput() {
    return json_decode(file_get_contents("php://input"), true);
}

session_start();
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    logDebug($user_id);
    switch ($method) {
        case 'GET':
            if (isset($_GET['task_id'])) {
                $comentarios = obtenerComentariosPorTarea($_GET['task_id']);
                echo json_encode($comentarios);
            } else {
                $tareas = obtenerTareasPorUsuario($user_id);
                echo json_encode($tareas);
            }
            break;

        case 'POST':
            $input = getJsonInput();
            if (isset($input['task_id'], $input['comment'])) {
                $id = agregarComentario($input['task_id'], $user_id, $input['comment']);
                if ($id > 0) {
                    http_response_code(201);
                    echo json_encode(["message" => "Comentario creado: ID:" . $id]);
                } else {
                    http_response_code(500);
                    echo json_encode(["error" => "Error general creando el comentario"]);
                }
            } else if (isset($input['title'], $input['description'], $input['due_date'])) {
                $id = crearTarea($user_id, $input['title'], $input['description'], $input['due_date']);
                if ($id > 0) {
                    http_response_code(201);
                    echo json_encode(["message" => "Tarea creada: ID:" . $id]);
                } else {
                    http_response_code(500);
                    echo json_encode(["error" => "Error general creando la tarea"]);
                }
            } else {
                http_response_code(400);
                echo json_encode(["error" => "Datos insuficientes"]);
            }
            break;

        case 'PUT':
            $input = getJsonInput();
            if (isset($input['comment']) && isset($_GET['id'])) {
                $editResult = editarComentario($_GET['id'], $input['comment']);
                if ($editResult) {
                    http_response_code(201);
                    echo json_encode(['message' => "Comentario actualizado"]);
                } else {
                    http_response_code(500);
                    echo json_encode(["message" => "Error actualizando el comentario"]);
                }
            } else if (isset($input['title'], $input['description'], $input['due_date']) && isset($_GET['id'])) {
                $editResult = editarTarea($_GET['id'], $input['title'], $input['description'], $input['due_date']);
                if ($editResult) {
                    http_response_code(201);
                    echo json_encode(['message' => "Tarea actualizada"]);
                } else {
                    http_response_code(500);
                    echo json_encode(["message" => "Error actualizando la tarea"]);
                }
            } else {
                http_response_code(400);
                echo json_encode(["error" => "Datos insuficientes"]);
            }
            break;

        case 'DELETE':
            if (isset($_GET['id'])) {
                $fueEliminado = eliminarComentario($_GET['id']);
                if ($fueEliminado) {
                    http_response_code(200);
                    echo json_encode(['message' => "Comentario eliminado"]);
                } else {
                    http_response_code(500);
                    echo json_encode(['message' => 'Sucedio un error al eliminar el comentario']);
                }
            } else if (isset($_GET['task_id'])) {
                $fueEliminado = eliminarTarea($_GET['task_id']);
                if ($fueEliminado) {
                    http_response_code(200);
                    echo json_encode(['message' => "Tarea eliminada"]);
                } else {
                    http_response_code(500);
                    echo json_encode(['message' => 'Sucedio un error al eliminar la tarea']);
                }
            } else {
                http_response_code(400);
                echo json_encode(["error" => "Datos insuficientes"]);
            }
            break;

        default:
            http_response_code(405);
            echo json_encode(["error" => "Metodo no permitido"]);
            break;
    }
} else {
    http_response_code(401);
    echo json_encode(["error" => "Sesion no activa"]);
}