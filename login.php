<?php
// Capturar el rol enviado por la URL (si existe)
$rol = isset($_GET['rol']) ? strtolower($_GET['rol']) : 'general';

// Configurar interfaz dinámica según el portal seleccionado
switch ($rol) {
    case 'alumno':
    case 'estudiante':
        $titulo_portal = "Portal Estudiantes";
        $color_header = "bg-primary";
        $color_btn = "btn-primary";
        $icono = "fa-user-graduate";
        $placeholder_user = "Ej: 2023110008 o correo@metropoli.edu.mx";
        break;
    case 'docente':
    case 'maestro':
        $titulo_portal = "Portal Docente";
        $color_header = "bg-success";
        $color_btn = "btn-success";
        $icono = "fa-chalkboard-user";
        $placeholder_user = "Ej: prof_roberto o correo@metropoli.edu.mx";
        break;
    case 'admin':
    case 'administrador':
        $titulo_portal = "Panel Administrativo";
        $color_header = "bg-warning text-dark";
        $color_btn = "btn-warning text-dark";
        $icono = "fa-sliders";
        $placeholder_user = "Ej: admin o usuario administrativo";
        break;
    default:
        $titulo_portal = "Control Escolar";
        $color_header = "bg-primary";
        $color_btn = "btn-primary";
        $icono = "fa-shield-halved";
        $placeholder_user = "Ej: admin o correo@univ.edu";
        break;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?php echo $titulo_portal; ?></title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background-color: #f4f6f9; }
        .login-container { max-width: 420px; margin-top: 70px; }
        .card { border: none; border-radius: 12px; }
        .card-header { border-top-left-radius: 12px !important; border-top-right-radius: 12px !important; }
    </style>
</head>
<body>

<div class="container login-container">
    
    <div class="mb-3">
        <a href="index.html" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
            <i class="fa-solid fa-arrow-left me-2"></i>Regresar al Inicio
        </a>
    </div>

    <div class="card shadow">
        <!-- Cabecera dinámica que cambia según el botón presionado -->
        <div class="card-header <?php echo $color_header; ?> text-center py-3">
            <h4 class="mb-0 fw-bold"><i class="fa-solid <?php echo $icono; ?> me-2"></i><?php echo $titulo_portal; ?></h4>
        </div>
        <div class="card-body p-4">
            
            <?php if(isset($_GET['error'])): ?>
                <div class="alert alert-danger text-center py-2 mb-3 small" role="alert">
                    <i class="fa-solid fa-circle-exclamation me-1"></i> Usuario o contraseña incorrectos.
                </div>
            <?php endif; ?>

            <form action="procesar_login.php" method="POST">
                <div class="mb-3">
                    <label for="usuario" class="form-label fw-semibold text-secondary">Usuario o Correo</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light text-muted"><i class="fa-solid fa-user"></i></span>
                        <input type="text" name="usuario" id="usuario" class="form-control" required placeholder="<?php echo $placeholder_user; ?>">
                    </div>
                </div>
                
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <label for="password" class="form-label fw-semibold text-secondary mb-0">Contraseña</label>
                        <a href="#" onclick="alert('Por favor, acuda al departamento de Control Escolar o TI de la UPMP para restablecer sus credenciales de acceso.');" class="small text-decoration-none text-primary">¿Olvidaste tu contraseña?</a>
                    </div>
                    <div class="input-group">
                        <span class="input-group-text bg-light text-muted"><i class="fa-solid fa-lock"></i></span>
                        <input type="password" name="password" id="password" class="form-control" required placeholder="••••••••">
                    </div>
                </div>
                
                <button type="submit" class="btn <?php echo $color_btn; ?> w-100 rounded-pill py-2 fw-bold mt-2">
                    Ingresar <i class="fa-solid fa-right-to-bracket ms-1"></i>
                </button>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>