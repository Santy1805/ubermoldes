<?php
session_start();
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'cliente') {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MoldesGo - App Cliente</title>
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
        .btn-toggle { display: flex; gap: 10px; margin-bottom: 15px; }
        .btn-toggle button { flex: 1; padding: 10px; border: 1px solid #e65100; background: #fff; color: #e65100; border-radius: 6px; cursor: pointer; font-weight: bold; }
        .btn-toggle button.active { background: #e65100; color: #fff; }
        .input-box { margin-bottom: 12px; }
        .input-box label { display: block; font-size: 12px; color: #666; margin-bottom: 4px; }
        .input-box input { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px; }
        .desglose { background: #fafafa; border: 1px solid #eee; padding: 12px; border-radius: 6px; margin: 15px 0; font-size: 14px; }
        .desglose-line { display: flex; justify-content: space-between; margin-bottom: 6px; }
        .precio-total { font-size: 22px; font-weight: bold; color: #e65100; text-align: right; }
        .btn-pedir { width: 100%; padding: 14px; background: #e65100; color: white; border: none; border-radius: 6px; font-size: 16px; font-weight: bold; cursor: pointer; }
        .logout { color: #888; text-decoration: none; font-size: 14px; }
    </style>
</head>
<body>

<header>
    <div class="logo">MoldesGo</div>
    <div>Hola, <strong><?= htmlspecialchars($_SESSION['nombre']) ?></strong> | <a href="logout.php" class="logout">Salir</a></div>
</header>

<div class="container">
    <div class="panel">
        <h3>Solicitar viaje en Coronel Moldes</h3>
        <p style="font-size: 13px; color: #666; margin-bottom: 15px;">Selecciona en el mapa o haz clic en los botones para fijar el origen y destino.</p>

        <div class="btn-toggle">
            <button id="btn-origen" class="active" onclick="setModo('origen')">Origen (A)</button>
            <button id="btn-destino" onclick="setModo('destino')">Destino (B)</button>
        </div>

        <div class="input-box">
            <label>Punto de Origen (A)</label>
            <input type="text" id="origen-txt" value="Parador RN 68 - Coronel Moldes" readonly>
        </div>

        <div class="input-box">
            <label>Punto de Destino (B)</label>
            <input type="text" id="destino-txt" value="Acceso Norte Valle de Lerma" readonly>
        </div>

        <div class="desglose">
            <div class="desglose-line"><span>Tarifa Base</span><span>$ 300</span></div>
            <div class="desglose-line"><span>Distancia (<span id="dist-val">0.95</span> km)</span><span id="dist-precio">$ 380</span></div>
            <div class="desglose-line"><span>Cuota de Servicio</span><span>$ 150</span></div>
            <hr style="margin: 8px 0; border: none; border-top: 1px solid #eee;">
            <div class="desglose-line"><strong>Precio Total Final</strong><span class="precio-total" id="precio-val">$ 830</span></div>
        </div>

        <button class="btn-pedir" onclick="pedirViaje()">Pedir Viaje</button>
    </div>

    <div id="map"></div>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    let map = L.map('map').setView([-25.2774, -65.4783], 15); // Coordenadas de Coronel Moldes

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap'
    }).addTo(map);

    let modo = 'origen';
    let origenLat = -25.2774, origenLng = -65.4783;
    let destinoLat = -25.2830, destinoLng = -65.4750;

    let markerOrigen = L.marker([origenLat, origenLng], {draggable: true, title: "Origen"}).addTo(map);
    let markerDestino = L.marker([destinoLat, destinoLng], {draggable: true, title: "Destino"}).addTo(map);

    function setModo(m) {
        modo = m;
        document.getElementById('btn-origen').classList.toggle('active', m === 'origen');
        document.getElementById('btn-destino').classList.toggle('active', m === 'destino');
    }

    map.on('click', function(e) {
        if (modo === 'origen') {
            origenLat = e.latlng.lat; origenLng = e.latlng.lng;
            markerOrigen.setLatLng(e.latlng);
            document.getElementById('origen-txt').value = `${origenLat.toFixed(5)}, ${origenLng.toFixed(5)}`;
        } else {
            destinoLat = e.latlng.lat; destinoLng = e.latlng.lng;
            markerDestino.setLatLng(e.latlng);
            document.getElementById('destino-txt').value = `${destinoLat.toFixed(5)}, ${destinoLng.toFixed(5)}`;
        }
        calcularTarifa();
    });

    function calcularTarifa() {
        // Cálculo de distancia en KM con la fórmula de Haversine
        let R = 6371;
        let dLat = (destinoLat - origenLat) * Math.PI / 180;
        let dLon = (destinoLng - origenLng) * Math.PI / 180;
        let a = Math.sin(dLat/2) * Math.sin(dLat/2) +
                Math.cos(origenLat * Math.PI / 180) * Math.cos(destinoLat * Math.PI / 180) *
                Math.sin(dLon/2) * Math.sin(dLon/2);
        let c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
        let dist = R * c;

        let precioDistancia = dist * 400;
        let total = 300 + precioDistancia + 150;

        document.getElementById('dist-val').innerText = dist.toFixed(2);
        document.getElementById('dist-precio').innerText = '$ ' + Math.round(precioDistancia);
        document.getElementById('precio-val').innerText = '$ ' + Math.round(total);
    }

    function pedirViaje() {
        let datos = new FormData();
        datos.append('origen_nombre', document.getElementById('origen-txt').value);
        datos.append('origen_lat', origenLat);
        datos.append('origen_lng', origenLng);
        datos.append('destino_nombre', document.getElementById('destino-txt').value);
        datos.append('destino_lat', destinoLat);
        datos.append('destino_lng', destinoLng);
        datos.append('distancia', document.getElementById('dist-val').innerText);
        datos.append('precio', document.getElementById('precio-val').innerText.replace('$ ', ''));

        fetch('crear_viaje.php', { method: 'POST', body: datos })
        .then(res => res.json())
        .then(res => {
            if(res.success) {
                alert("¡Viaje solicitado con éxito! Esperando conductor...");
            } else {
                alert("Error al solicitar el viaje.");
            }
        });
    }

    calcularTarifa();
</script>
</body>
</html>