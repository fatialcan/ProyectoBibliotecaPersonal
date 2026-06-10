<?php
header('Content-Type: application/json; charset=utf-8');
require_once 'proteger.php';

$inputJSON = json_decode(file_get_contents('php://input'), true);

if (isset($inputJSON['id'])) {
    $archivo = __DIR__ . '/libros.json';
    $libros = [];

    if (file_exists($archivo)) {
        $libros = json_decode(file_get_contents($archivo), true);
        if (!is_array($libros)) {
            $libros = [];
        }
    }

    foreach ($libros as $key => $libro) {
        if ($libro['id'] == $inputJSON['id'] && isset($libro['usuario']) && $libro['usuario'] === $usuarioActual) {
            $libros[$key]['eliminado'] = true;
            file_put_contents($archivo, json_encode($libros, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), LOCK_EX);
            echo json_encode(["success" => true]);
            exit;
        }
    }
}

http_response_code(400);
echo json_encode(["error" => "No se pudo eliminar."]);
?>
