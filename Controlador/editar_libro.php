<?php
header('Content-Type: application/json; charset=utf-8');

// Capturamos los datos enviados por fetch() (Petición PUT)
$inputJSON = file_get_contents('php://input');
$datosEditados = json_decode($inputJSON, true);

if (!$datosEditados || !isset($datosEditados['id'])) {
    http_response_code(400);
    echo json_encode(["error" => "Datos incompletos o ID no proporcionado."]);
    exit;
}

$archivo = 'libros.json';
if (!file_exists($archivo)) {
    http_response_code(404);
    echo json_encode(["error" => "Base de datos no encontrada."]);
    exit;
}

$libros = json_decode(file_get_contents($archivo), true);
$libroActualizado = false;

// Buscamos el libro y actualizamos sus campos
foreach ($libros as $key => $libro) {
    if ($libro['id'] == $datosEditados['id']) {
        $libros[$key]['titulo'] = trim($datosEditados['titulo']);
        $libros[$key]['autor'] = trim($datosEditados['autor']);
        $libros[$key]['genero'] = trim($datosEditados['genero']);
        $libros[$key]['notas'] = trim($datosEditados['notas']);
        $libros[$key]['leido'] = isset($datosEditados['leido']) ? (bool)$datosEditados['leido'] : false;
        
        $libroActualizado = true;
        break;
    }
}

if ($libroActualizado) {
    file_put_contents($archivo, json_encode($libros, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    echo json_encode(["success" => true, "mensaje" => "Libro actualizado correctamente."]);
} else {
    http_response_code(404);
    echo json_encode(["error" => "Libro no encontrado."]);
}
?>