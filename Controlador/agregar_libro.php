<?php
// Indicamos que vamos a responder con JSON
header('Content-Type: application/json; charset=utf-8');

// 1. Capturamos los datos enviados por fetch() en formato JSON
$inputJSON = file_get_contents('php://input');
$nuevoLibro = json_decode($inputJSON, true);

// Verificamos que se hayan recibido datos
if (!$nuevoLibro) {
    http_response_code(400); // Bad Request
    echo json_encode(["error" => "No se recibieron datos válidos."]);
    exit;
}

$archivo = 'libros.json';
$libros = [];

// 2. Leemos los libros que ya existen en el archivo
if (file_exists($archivo)) {
    $json_data = file_get_contents($archivo);
    $libros = json_decode($json_data, true);
    if (!is_array($libros)) {
        $libros = [];
    }
}

// 3. Generamos un ID autoincremental
$nuevoId = 1;
if (count($libros) > 0) {
    // Buscamos el ID más alto actual y le sumamos 1
    $ids = array_column($libros, 'id');
    $nuevoId = max($ids) + 1;
}

// 4. Preparamos el registro del nuevo libro
$libroAGuardar = [
    "id" => $nuevoId,
    "titulo" => trim($nuevoLibro['titulo']),
    "autor" => trim($nuevoLibro['autor']),
    "genero" => trim($nuevoLibro['genero']),
    "notas" => trim($nuevoLibro['notas']),
    // Aseguramos que los booleanos se guarden correctamente
    "leido" => isset($nuevoLibro['leido']) ? (bool)$nuevoLibro['leido'] : false,
    "eliminado" => false // Borrado lógico inicializado en falso
];

// 5. Añadimos el libro al array
$libros[] = $libroAGuardar;

// 6. Sobreescribimos el archivo JSON con los nuevos datos
// JSON_PRETTY_PRINT ayuda a que el archivo siga siendo legible si lo abres
file_put_contents($archivo, json_encode($libros, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

// 7. Devolvemos una respuesta de éxito al JavaScript
http_response_code(201); // 201 Created
echo json_encode([
    "success" => true, 
    "mensaje" => "Libro agregado correctamente", 
    "libro" => $libroAGuardar
]);
?>