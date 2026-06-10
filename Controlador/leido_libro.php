<?php
header('Content-Type: application/json; charset=utf-8');
require_once 'proteger.php';

$inputJSON = file_get_contents('php://input');
$datos = json_decode($inputJSON, true);

if (!isset($datos['id']) || !isset($datos['leido'])) {
    http_response_code(400);
    echo json_encode(["error" => "ID o estado no proporcionado."]);
    exit;
}

$archivo = __DIR__ . '/libros.json';
if (!file_exists($archivo)) {
    http_response_code(404);
    echo json_encode(["error" => "Base de datos no encontrada."]);
    exit;
}

$libros = json_decode(file_get_contents($archivo), true);
if (!is_array($libros)) {
    $libros = [];
}

$estadoCambiado = false;

foreach ($libros as $key => $libro) {
    if ($libro['id'] == $datos['id'] && isset($libro['usuario']) && $libro['usuario'] === $usuarioActual) {
        $libros[$key]['leido'] = (bool)$datos['leido'];
        $estadoCambiado = true;
        break;
    }
}

if ($estadoCambiado) {
    file_put_contents($archivo, json_encode($libros, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), LOCK_EX);
    echo json_encode(["success" => true, "mensaje" => "Estado actualizado."]);
} else {
    http_response_code(404);
    echo json_encode(["error" => "Libro no encontrado."]);
}
?>
