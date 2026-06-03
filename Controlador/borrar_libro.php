<?php
header('Content-Type: application/json; charset=utf-8');
$inputJSON = json_decode(file_get_contents('php://input'), true);

if (isset($inputJSON['id'])) {
    $archivo = 'libros.json';
    $libros = json_decode(file_get_contents($archivo), true);
    
    foreach ($libros as $key => $libro) {
        if ($libro['id'] == $inputJSON['id']) {
            $libros[$key]['eliminado'] = true; // ¡Aquí ocurre el borrado lógico!
            file_put_contents($archivo, json_encode($libros, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            echo json_encode(["success" => true]);
            exit;
        }
    }
}
http_response_code(400);
echo json_encode(["error" => "No se pudo eliminar."]);
?>