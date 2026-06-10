<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

if (isset($_SESSION['usuario'])) {
    echo json_encode([
        "logueado" => true,
        "usuario" => $_SESSION['usuario']
    ]);
} else {
    echo json_encode([
        "logueado" => false
    ]);
}
?>
