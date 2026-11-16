<?php
session_start();
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'conductor') {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MoldesGo - App Conductor</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        * { box-sizing: border-box; font-family: system-ui, sans-serif; margin: 0; padding: 0; }
        body { display: flex; flex-direction: column; height: 100vh; background: #f8f9fa; }
        header { background: #fff; padding: 15px 20px; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #ddd; }
        .logo { font-weight: bold; font-size: 20px; color: #e65100; }
        .container { display: flex; flex: 1; flex-direction: column; }
        @media (min-width: 768px) { .container { flex-direction: row; } }
        .panel { width: 100%; max-width: 420px; padding: 20px; background: white; overflow-y: auto; box-shadow: 2px 0 5px rgba(0,0,0,0.05); }
        #map { flex: 1; min-height: 300px; }
        .status-box { background: #e8f5e9; border: 1px solid #c8e6c9; padding: 15px; border-radius: 8px; margin-bottom: 20px; }
        .viaje-card { border: 1px solid #ddd; padding: 15px; border-radius: 8px; margin-bottom: 15px; background: #fafafa; }
        .btn-aceptar { width: 100%; padding: 10px; background: #2e7d32; color: white; border: none; border-radius: 6px; font-weight: bold; cursor: pointer; }
    </style>
</head>
<body>

<header>
    <div class="logo">MoldesGo (Chofer)</div>
    <div>Hola, <strong><?= htmlspecialchars($_SESSION['nombre']) ?></strong> | <a href="logout.php">Salir</a></div>
</header>

<div class="container">
    <div class="panel">
        <h2>Panel de Control</h2>
        <div class="status-box">
            <strong>Conectado / Disponible</strong>
            <p style="font-size: 12px; color: #555;">Recibiendo solicitudes en Coronel Moldes</p>
        </div>

        <h3>Solicitudes Pendientes</h3>
        <div id="lista-viajes">Cargando viajes disponibles...</div>
    </div>

    <div id="map"></div>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    let map = L.map('map').setView([-25.2774, -65.4783], 14);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '© OpenStreetMap' }).addTo(map);

    let markers = [];

    function cargarViajes() {
        fetch('obtener_viajes.php')
        .then(res => res.json())
        .then(data => {
            let html = '';
            // Limpiar marcadores anteriores
            markers.forEach(m => map.removeLayer(m));
            markers = [];

            if (data.length === 0) {
                html = '<p style="font-size: 14px; color: #777; margin-top: 10px;">No hay viajes disponibles por ahora.</p>';
            } else {
                data.forEach(v => {
                    html += `
                        <div class="viaje-card">
                            <p><strong>Origen:</strong> ${v.origen_nombre}</p>
                            <p><strong>Destino:</strong> ${v.destino_nombre}</p>
                            <p><strong>Precio:</strong> $${v.precio} (${v.distancia_km} km)</p>
                            <button class="btn-aceptar" onclick="aceptarViaje(${v.id})">Aceptar Viaje</button>
                        </div>
                    `;

                    let mOrigen = L.marker([v.origen_lat, v.origen_lng]).addTo(map).bindPopup('Origen viaje #' + v.id);
                    let mDestino = L.marker([v.destino_lat, v.destino_lng]).addTo(map).bindPopup('Destino viaje #' + v.id);
                    markers.push(mOrigen, mDestino);
                });
            }
            document.getElementById('lista-viajes').innerHTML = html;
        });
    }

    function aceptarViaje(id) {
        let datos = new FormData();
        datos.append('id', id);
        fetch('aceptar_viaje.php', { method: 'POST', body: datos })
        .then(res => res.json())
        .then(res => {
            if (res.success) {
                alert("¡Viaje aceptado!");
                cargarViajes();
            }
        });
    }

    setInterval(cargarViajes, 3000); // Consulta viajes nuevos cada 3 segundos
    cargarViajes();
</script>
</body>
</html>