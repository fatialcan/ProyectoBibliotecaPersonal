<?php
// Indicamos que la respuesta será en formato JSON
header('Content-Type: application/json; charset=utf-8');
require_once 'proteger.php';

$archivo = __DIR__ . '/libros.json';
$libros = [];

// Leemos el archivo JSON si existe
if (file_exists($archivo)) {
    $json_data = file_get_contents($archivo);
    $libros = json_decode($json_data, true);
    if (!is_array($libros)) {
        $libros = [];
    }
}

$q = isset($_GET['q']) ? strtolower(trim($_GET['q'])) : '';
$estado = isset($_GET['estado']) ? $_GET['estado'] : '';

$resultados = [];

foreach ($libros as $libro) {
    // Cada usuario solo puede ver sus propios libros
    if (!isset($libro['usuario']) || $libro['usuario'] !== $usuarioActual) {
        continue;
    }

    if (isset($libro['eliminado']) && $libro['eliminado'] === true) {
        continue;
    }

    $coincideBusqueda = true;
    $coincideEstado = true;

    if ($q !== '') {
        $titulo = strtolower($libro['titulo']);
        $autor = strtolower($libro['autor']);

        if (strpos($titulo, $q) === false && strpos($autor, $q) === false) {
            $coincideBusqueda = false;
        }
    }

    if ($estado !== '') {
        if ($estado === 'leido' && $libro['leido'] !== true) {
            $coincideEstado = false;
        }

        if ($estado === 'no-leido' && $libro['leido'] === true) {
            $coincideEstado = false;
        }
    }

    if ($coincideBusqueda && $coincideEstado) {
        $resultados[] = $libro;
    }
}

echo json_encode($resultados);
?>
