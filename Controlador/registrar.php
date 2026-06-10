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

if (!preg_match('/^[a-zA-Z0-9_]{3,20}$/', $usuario)) {
    http_response_code(400);
    echo json_encode(["error" => "El usuario debe tener de 3 a 20 caracteres. Solo letras, números y guion bajo."]);
    exit;
}

if (strlen($password) < 4) {
    http_response_code(400);
    echo json_encode(["error" => "La contraseña debe tener mínimo 4 caracteres."]);
    exit;
}

$archivo = __DIR__ . '/usuarios.json';

if (!file_exists($archivo)) {
    file_put_contents($archivo, json_encode([], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

$usuarios = json_decode(file_get_contents($archivo), true);

if (!is_array($usuarios)) {
    $usuarios = [];
}

foreach ($usuarios as $user) {
    if (isset($user['usuario']) && strtolower($user['usuario']) === strtolower($usuario)) {
        http_response_code(409);
        echo json_encode(["error" => "Ese usuario ya existe. Usa otro nombre."]);
        exit;
    }
}

$nuevoUsuario = [
    "usuario" => $usuario,
    "password" => password_hash($password, PASSWORD_DEFAULT)
];

$usuarios[] = $nuevoUsuario;

$guardado = file_put_contents(
    $archivo,
    json_encode($usuarios, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
    LOCK_EX
);

if ($guardado === false) {
    http_response_code(500);
    echo json_encode(["error" => "No se pudo guardar el usuario."]);
    exit;
}

$_SESSION['usuario'] = $usuario;

echo json_encode([
    "success" => true,
    "mensaje" => "Cuenta creada correctamente.",
    "usuario" => $usuario
]);
?>
