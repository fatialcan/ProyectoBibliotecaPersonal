<?php
// Indicamos que la respuesta será en formato JSON
header('Content-Type: application/json; charset=utf-8');

$archivo = 'libros.json';
$libros = [];

// Leemos el archivo JSON si existe
if (file_exists($archivo)) {
    $json_data = file_get_contents($archivo);
    $libros = json_decode($json_data, true);
    // condicion de seguridad por si la traduccion fallo y lo que quedo no es un arreglo
    if (!is_array($libros)) {
        $libros = [];
    }
}

// verificar si el usuario escribio algo en la busqueda si no fue asi simplemente guarda un texto vacio
$q = isset($_GET['q']) ? strtolower(trim($_GET['q'])) : '';
// filtro de estado: leido o no leido
$estado = isset($_GET['estado']) ? $_GET['estado'] : '';

$resultados = [];

// ciclo que se ejecuta para cada libro hasta terminar
foreach ($libros as $libro) {
    // 1. Aplicar Borrado Lógico: Ignoramos los libros eliminados
    if (isset($libro['eliminado']) && $libro['eliminado'] === true) {
        continue; 
    }
    // se asume que coincide y pasa a las siguientes condiciones
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
        // si el usuario pidiio los "leido" pero la pagina dice que este libro no esta leido
        if ($estado === 'leido' && $libro['leido'] !== true) {
            // se descarta
            $coincideEstado = false;
        }
        // si el usuario selecciona los "no leido" y el libro tiene "leido"
        if ($estado === 'no-leido' && $libro['leido'] === true) {
            // tambien se descarta
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