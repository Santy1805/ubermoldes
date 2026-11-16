<?php
session_start();
require_once 'conexion.php';

if (isset($_POST['id']) && $_SESSION['rol'] === 'conductor') {
    $stmt = $pdo->prepare("UPDATE viajes SET estado = 'aceptado', conductor_id = ? WHERE id = ?");
    $stmt->execute([$_SESSION['usuario_id'], $_POST['id']]);
    echo json_encode(['success' => true]);
}
?>