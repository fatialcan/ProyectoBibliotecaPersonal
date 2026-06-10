<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

$inputJSON = file_get_contents('php://input');
$datos = json_decode($inputJSON, true);

if (!$datos || !isset($datos['usuario']) || !isset($datos['password'])) {
    http_response_code(400);
    echo json_encode(["error" => "Datos incompletos."]);
    exit;
}

$usuario = trim($datos['usuario']);
$password = trim($datos['password']);

$archivo = __DIR__ . '/usuarios.json';

if (!file_exists($archivo)) {
    http_response_code(500);
    echo json_encode(["error" => "Archivo de usuarios no encontrado."]);
    exit;
}

$usuarios = json_decode(file_get_contents($archivo), true);

if (!is_array($usuarios)) {
    http_response_code(500);
    echo json_encode(["error" => "El archivo de usuarios no tiene un formato válido."]);
    exit;
}

foreach ($usuarios as $user) {
    if (isset($user['usuario'], $user['password']) && $user['usuario'] === $usuario && password_verify($password, $user['password'])) {
        $_SESSION['usuario'] = $usuario;

        echo json_encode([
            "success" => true,
            "mensaje" => "Inicio de sesión correcto.",
            "usuario" => $usuario
        ]);
        exit;
    }
}

http_response_code(401);
echo json_encode(["error" => "Usuario o contraseña incorrectos."]);
?>
