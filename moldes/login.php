<?php
session_start();
require_once 'conexion.php';

$mensaje = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';

    if ($accion === 'registro') {
        $nombre = trim($_POST['nombre']);
        $email = trim($_POST['email']);
        $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
        $rol = $_POST['rol'];

        $stmt = $pdo->prepare("INSERT INTO usuarios (nombre, email, password, rol) VALUES (?, ?, ?, ?)");
        try {
            $stmt->execute([$nombre, $email, $password, $rol]);
            $mensaje = "Cuenta creada con éxito. Ya puedes iniciar sesión.";
        } catch (PDOException $e) {
            $mensaje = "Error: El correo ya está registrado.";
        }
    } elseif ($accion === 'login') {
        $email = trim($_POST['email']);
        $password = $_POST['password'];

        $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = ?");
        $stmt->execute([$email]);
        $usuario = $stmt->fetch();

        if ($usuario && password_verify($password, $usuario['password'])) {
            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['nombre'] = $usuario['nombre'];
            $_SESSION['rol'] = $usuario['rol'];

            if ($usuario['rol'] === 'cliente') {
                header("Location: cliente.php");
            } else {
                header("Location: conductor.php");
            }
            exit;
        } else {
            $mensaje = "Credenciales incorrectas.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MoldesGo - Acceso</title>
    <style>
        * { box-sizing: border-box; font-family: system-ui, -apple-system, sans-serif; margin: 0; padding: 0; }
        body { background: #f4f6f8; display: flex; align-items: center; justify-content: center; min-height: 100vh; padding: 20px; }
        .card { background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); width: 100%; max-width: 400px; }
        h2 { color: #e65100; text-align: center; margin-bottom: 20px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: 600; color: #333; }
        input, select { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 6px; font-size: 14px; }
        button { width: 100%; padding: 12px; background: #e65100; color: white; border: none; border-radius: 6px; font-weight: bold; cursor: pointer; margin-top: 10px; }
        button:hover { background: #bf360c; }
        .toggle-btn { text-align: center; margin-top: 15px; color: #666; cursor: pointer; text-decoration: underline; font-size: 14px; }
        .alert { background: #ffebee; color: #c62828; padding: 10px; border-radius: 6px; margin-bottom: 15px; text-align: center; font-size: 14px; }
    </style>
</head>
<body>
<div class="card">
    <h2>MoldesGo</h2>
    <?php if ($mensaje): ?><div class="alert"><?=$mensaje?></div><?php endif; ?>

    <!-- Formulario de Login -->
    <form id="form-login" method="POST">
        <input type="hidden" name="accion" value="login">
        <div class="form-group">
            <label>Correo Electrónico</label>
            <input type="email" name="email" required>
        </div>
        <div class="form-group">
            <label>Contraseña</label>
            <input type="password" name="password" required>
        </div>
        <button type="submit">Iniciar Sesión</button>
        <p class="toggle-btn" onclick="toggleForm()">¿No tienes cuenta? Regístrate aquí</p>
    </form>

    <!-- Formulario de Registro -->
    <form id="form-registro" method="POST" style="display: none;">
        <input type="hidden" name="accion" value="registro">
        <div class="form-group">
            <label>Nombre Completo</label>
            <input type="text" name="nombre" required>
        </div>
        <div class="form-group">
            <label>Correo Electrónico</label>
            <input type="email" name="email" required>
        </div>
        <div class="form-group">
            <label>Contraseña</label>
            <input type="password" name="password" required>
        </div>
        <div class="form-group">
            <label>Tipo de Usuario</label>
            <select name="rol" required>
                <option value="cliente">Cliente</option>
                <option value="conductor">Conductor</option>
            </select>
        </div>
        <button type="submit">Crear Cuenta</button>
        <p class="toggle-btn" onclick="toggleForm()">¿Ya tienes cuenta? Inicia Sesión</p>
    </form>
</div>

<script>
    function toggleForm() {
        const login = document.getElementById('form-login');
        const reg = document.getElementById('form-registro');
        if (login.style.display === 'none') {
            login.style.display = 'block';
            reg.style.display = 'none';
        } else {
            login.style.display = 'none';
            reg.style.display = 'block';
        }
    }
</script>
</body>
</html>