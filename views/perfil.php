<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['name'])) {
    header('Location: .');
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <?php require_once("public/components/head.php"); ?>
    <title>Perfil de Usuario</title>
    <link rel="stylesheet" href="vendor/select2/select2/dist/css/select2.min.css" />
    <link rel="stylesheet" href="vendor/apalfrey/select2-bootstrap-5-theme/dist/select2-bootstrap-5-theme.min.css" />
    <style>
        .perfil-wrapper {
            width: 100%;
            max-width: 1200px;
        }

        .perfil-card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.06);
            padding: 2rem;
        }

        .perfil-foto-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-bottom: 2rem;
        }

        .perfil-foto {
            width: 140px;
            height: 140px;
            object-fit: cover;
            border-radius: 50%;
            border: 4px solid #0d6efd;
            background: #f8f9fa;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }

        .perfil-foto-label {
            margin-top: 1rem;
            font-weight: 500;
            color: #0d6efd;
            cursor: pointer;
        }

        .perfil-form .invalid-feedback,
        .perfil-docente-form .invalid-feedback {
            white-space: normal;
            word-break: break-word;
        }

        .perfil-docente-form .form-control.campo-bloqueado,
        .perfil-docente-form .form-select.campo-bloqueado {
            background-color: #f3f5f9;
            color: #495057;
            opacity: 0.85;
        }

        .select2-container--bootstrap-5.select2-container--disabled .select2-selection {
            background-color: #f3f5f9;
            opacity: 0.85;
        }

        .perfil-section-title {
            font-weight: 600;
            letter-spacing: .5px;
            margin-bottom: 1.5rem;
            color: #0d6efd;
        }

        .perfil-badge-list span {
            display: inline-block;
            margin: 0 .35rem .35rem 0;
            background-color: #e9f3ff;
            color: #0d6efd;
            border-radius: 999px;
            padding: .35rem .75rem;
            font-size: .85rem;
        }
    </style>
</head>

<body class="d-flex flex-column min-vh-100">
    <?php require_once("public/components/sidebar.php"); ?>
    <main class="main-content flex-shrink-0">
        <section class="d-flex flex-column align-items-center justify-content-center py-4">
            <h2 class="text-primary text-center mb-4" style="font-weight: 600; letter-spacing: 1px;">Mi Perfil</h2>
            <div class="perfil-wrapper">
                <div class="row g-4">
                    <div class="col-12 col-lg-4" id="perfilAccesoCol">
                        <div class="perfil-card perfil-form">
                            <h5 class="perfil-section-title">Datos de acceso</h5>
                            <form id="formPerfil" enctype="multipart/form-data" autocomplete="off">
                                <div class="perfil-foto-container">
                                    <img id="fotoPerfil" src="public/assets/icons/user-circle.svg" class="perfil-foto" alt="Foto de perfil">
                                    <label for="fotoPerfilInput" class="perfil-foto-label">Cambiar foto</label>
                                    <input type="file" id="fotoPerfilInput" name="fotoPerfilInput" accept=".png,.jpg,.jpeg,.svg" style="display:none;">
                                </div>

                                <div class="mb-3">
                                    <label for="nombreUsuario" class="form-label">Nombre</label>
                                    <input type="text" readonly class="form-control" id="nombreUsuario" name="nombreUsuario">
                                </div>
                                <div class="mb-3">
                                    <label for="correoUsuario" class="form-label">Correo</label>
                                    <input type="email" class="form-control" id="correoUsuario" name="correoUsuario" required>
                                </div>
                                <div class="mb-3">
                                    <label for="contraseniaPerfil" class="form-label">Nueva Contraseña</label>
                                    <input type="password" class="form-control" id="contraseniaPerfil" name="contraseniaPerfil" autocomplete="new-password" placeholder="Dejar vacío para no cambiar">
                                    <span id="scontraseniaPerfil" class="form-text"></span>
                                </div>
                                <div class="d-grid gap-2">
                                    <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="col-12 col-lg-8 d-none" id="perfilDocenteWrapper">
                        <div class="perfil-card perfil-docente-form">
                            <h5 class="perfil-section-title">Datos del docente</h5>
                            <form id="formPerfilDocente" autocomplete="off">
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label for="docenteCedula" class="form-label">Cédula</label>
                                        <input type="text" class="form-control" id="docenteCedula" readonly>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="docenteNombre" class="form-label">Nombre</label>
                                        <input type="text" class="form-control" id="docenteNombre" readonly>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="docenteApellido" class="form-label">Apellido</label>
                                        <input type="text" class="form-control" id="docenteApellido" readonly>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="docenteCategoria" class="form-label">Categoría</label>
                                        <input type="text" class="form-control campo-bloqueado" id="docenteCategoria" readonly>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="docenteCorreo" class="form-label">Correo institucional</label>
                                        <input type="email" class="form-control" id="docenteCorreo">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="docenteDedicacion" class="form-label">Dedicación</label>
                                        <select id="docenteDedicacion" class="form-select campo-bloqueado" disabled>
                                            <option value="" disabled selected>Seleccione...</option>
                                            <option value="Exclusiva">Exclusiva</option>
                                            <option value="Tiempo Completo">Tiempo Completo</option>
                                            <option value="Medio Tiempo">Medio Tiempo</option>
                                            <option value="Tiempo Convencional">Tiempo Convencional</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="docenteCondicion" class="form-label">Condición laboral</label>
                                        <select id="docenteCondicion" class="form-select campo-bloqueado" disabled>
                                            <option value="" disabled selected>Seleccione...</option>
                                            <option value="Ordinario">Ordinario</option>
                                            <option value="Contratado por Credenciales">Contratado por Credenciales</option>
                                            <option value="Suplente">Suplente</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="docenteTipoConcurso" class="form-label">Tipo de concurso</label>
                                        <input type="text" class="form-control campo-bloqueado" id="docenteTipoConcurso" readonly>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="docenteAnioConcurso" class="form-label">Mes y año del concurso</label>
                                        <input type="month" class="form-control campo-bloqueado" id="docenteAnioConcurso" disabled>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="docenteIngreso" class="form-label">Fecha de ingreso</label>
                                        <input type="date" class="form-control campo-bloqueado" id="docenteIngreso" disabled>
                                    </div>
                                </div>

                                <input type="hidden" id="docenteObservacion">

                                <hr class="my-4">

                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="docenteTitulos" class="form-label">Títulos</label>
                                        <select id="docenteTitulos" class="form-select" multiple="multiple"></select>
                                        <small class="form-text text-muted">Seleccione al menos un título académico.</small>
                                        <div class="perfil-badge-list mt-2" id="docenteTitulosResumen"></div>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="docenteCoordinaciones" class="form-label">Coordinaciones</label>
                                        <select id="docenteCoordinaciones" class="form-select" multiple="multiple"></select>
                                        <small class="form-text text-muted">Seleccione las coordinaciones donde participa.</small>
                                        <div class="perfil-badge-list mt-2" id="docenteCoordinacionesResumen"></div>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-end mt-4">
                                    <button type="submit" id="btnGuardarDocente" class="btn btn-primary" disabled>Guardar datos del docente</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>
    <?php require_once("public/components/footer.php"); ?>
    <script src="vendor/select2/select2/dist/js/select2.min.js"></script>
    <script type="text/javascript" src="public/js/perfil.js"></script>
    <script type="text/javascript" src="public/js/validacion.js"></script>
</body>

</html>