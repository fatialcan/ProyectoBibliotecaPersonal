<?php
// Indicamos que vamos a responder con JSON
header('Content-Type: application/json; charset=utf-8');
require_once 'proteger.php';

// 1. Capturamos los datos enviados por fetch() en formato JSON
$inputJSON = file_get_contents('php://input');
$nuevoLibro = json_decode($inputJSON, true);

// Verificamos que se hayan recibido datos
if (!$nuevoLibro) {
    http_response_code(400); // Bad Request
    echo json_encode(["error" => "No se recibieron datos válidos."]);
    exit;
}

$archivo = __DIR__ . '/libros.json';
$libros = [];

// 2. Leemos los libros que ya existen en el archivo
if (file_exists($archivo)) {
    $json_data = file_get_contents($archivo);
    $libros = json_decode($json_data, true);
    if (!is_array($libros)) {
        $libros = [];
    }
}

// 3. Generamos un ID autoincremental global
$nuevoId = 1;
if (count($libros) > 0) {
    $ids = array_column($libros, 'id');
    $ids = array_filter($ids, 'is_numeric');
    if (count($ids) > 0) {
        $nuevoId = max($ids) + 1;
    }
}

// 4. Preparamos el registro del nuevo libro
$libroAGuardar = [
    "id" => $nuevoId,
    "usuario" => $usuarioActual,
    "titulo" => trim($nuevoLibro['titulo']),
    "autor" => trim($nuevoLibro['autor']),
    "genero" => trim($nuevoLibro['genero']),
    "notas" => trim($nuevoLibro['notas']),
    "leido" => isset($nuevoLibro['leido']) ? (bool)$nuevoLibro['leido'] : false,
    "eliminado" => false
];

// 5. Añadimos el libro al array
$libros[] = $libroAGuardar;

// 6. Sobreescribimos el archivo JSON con los nuevos datos
file_put_contents($archivo, json_encode($libros, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), LOCK_EX);

// 7. Devolvemos una respuesta de éxito al JavaScript
http_response_code(201);
echo json_encode([
    "success" => true,
    "libro" => $libroAGuardar
]);
?>
