<?php
require_once 'conexion.php';
$stmt = $pdo->query("SELECT * FROM viajes WHERE estado = 'pendiente' ORDER BY id DESC");
echo json_encode($stmt->fetchAll());
?>