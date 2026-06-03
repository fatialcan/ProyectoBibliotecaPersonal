<?php
// Indicamos que la respuesta será en formato JSON
header('Content-Type: application/json; charset=utf-8');

$archivo = 'libros.json';
$libros = [];

// Leemos el archivo JSON si existe
if (file_exists($archivo)) {
    $json_data = file_get_contents($archivo);
    $libros = json_decode($json_data, true);
    if (!is_array($libros)) {
        $libros = [];
    }
}

// Obtenemos los parámetros de la URL (si existen)
$q = isset($_GET['q']) ? strtolower(trim($_GET['q'])) : '';
$estado = isset($_GET['estado']) ? $_GET['estado'] : '';

$resultados = [];

foreach ($libros as $libro) {
    // 1. Aplicar Borrado Lógico: Ignoramos los eliminados
    if (isset($libro['eliminado']) && $libro['eliminado'] === true) {
        continue; 
    }

    $coincideBusqueda = true;
    $coincideEstado = true;

    // 2. Filtro de búsqueda de texto (Título o Autor)
    if ($q !== '') {
        $titulo = strtolower($libro['titulo']);
        $autor = strtolower($libro['autor']);
        
        // Si la búsqueda no está en el título ni en el autor, lo descartamos
        if (strpos($titulo, $q) === false && strpos($autor, $q) === false) {
            $coincideBusqueda = false;
        }
    }

    // 3. Filtro por estado de lectura
    if ($estado !== '') {
        if ($estado === 'leido' && $libro['leido'] !== true) {
            $coincideEstado = false;
        }
        if ($estado === 'no-leido' && $libro['leido'] === true) {
            $coincideEstado = false;
        }
    }

    // Si pasa todos los filtros, lo agregamos a los resultados
    if ($coincideBusqueda && $coincideEstado) {
        $resultados[] = $libro;
    }
}

// Devolvemos el array filtrado como JSON al cliente
echo json_encode($resultados);
?>