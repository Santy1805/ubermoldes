<?php
session_start();
require_once 'conexion.php';

if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'cliente') {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

$cliente_id = $_SESSION['usuario_id'];
$origen_nombre = $_POST['origen_nombre'];
$origen_lat = $_POST['origen_lat'];
$origen_lng = $_POST['origen_lng'];
$destino_nombre = $_POST['destino_nombre'];
$destino_lat = $_POST['destino_lat'];
$destino_lng = $_POST['destino_lng'];
$distancia = $_POST['distancia'];
$precio = $_POST['precio'];

$stmt = $pdo->prepare("INSERT INTO viajes (cliente_id, origen_nombre, origen_lat, origen_lng, destino_nombre, destino_lat, destino_lng, distancia_km, precio) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");

if ($stmt->execute([$cliente_id, $origen_nombre, $origen_lat, $origen_lng, $destino_nombre, $destino_lat, $destino_lng, $distancia, $precio])) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false]);
}
?>