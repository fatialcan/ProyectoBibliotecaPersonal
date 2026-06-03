<?php
header('Content-Type: application/json; charset=utf-8');

$inputJSON = file_get_contents('php://input');
$datos = json_decode($inputJSON, true);

if (!isset($datos['id']) || !isset($datos['leido'])) {
    http_response_code(400);
    echo json_encode(["error" => "ID o estado no proporcionado."]);
    exit;
}

$archivo = 'libros.json';
if (!file_exists($archivo)) {
    http_response_code(404);
    echo json_encode(["error" => "Base de datos no encontrada."]);
    exit;
}

$libros = json_decode(file_get_contents($archivo), true);
$estadoCambiado = false;

foreach ($libros as $key => $libro) {
    if ($libro['id'] == $datos['id']) {
        // Actualizamos únicamente el estado de lectura
        $libros[$key]['leido'] = (bool)$datos['leido'];
        $estadoCambiado = true;
        break;
    }
}

if ($estadoCambiado) {
    file_put_contents($archivo, json_encode($libros, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    echo json_encode(["success" => true, "mensaje" => "Estado actualizado."]);
} else {
    http_response_code(404);
    echo json_encode(["error" => "Libro no encontrado."]);
}
?>
