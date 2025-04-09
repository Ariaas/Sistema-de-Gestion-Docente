<!DOCTYPE html>
<html lang="en">
<head>
    <?php require_once("components/head.php"); ?>
    <title>Docente</title>
</head>
<body>
    <?php require_once("components/sidebar.php"); ?>
    <main class="main-content">
        <h1>Docente</h1>
        <!-- Button trigger modal -->
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#exampleModal">
                <h8>Registrar Docente</h8>
            </button>

            <!-- Modal -->
            <div class="modal fade" id="exampleModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="exampleModalLabelLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="exampleModalLabel">Registrar Docente</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Formulario de docente -->
                    <form>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="nombre" class="form-label">Nombre</label>
                                <input type="text" class="form-control" id="nombre" placeholder="Ingrese el nombre" required>
                            </div>
                            <div class="col-md-6">
                                <label for="apellido" class="form-label">Apellido</label>
                                <input type="text" class="form-control" id="apellido" placeholder="Ingrese el apellido" required>
                            </div>
                        </div>
                        <div class="row g-3 mt-3">
                            <div class="col-md-6">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" placeholder="ejemplo@correo.com" required>
                            </div>
                            <div class="col-md-6">
                                <label for="telefono" class="form-label">Teléfono</label>
                                <input type="tel" class="form-control" id="telefono" placeholder="Ingrese el teléfono" required>
                            </div>
                        </div>
                        <div class="row g-3 mt-3">
                            <div class="col-md-6">
                                <label for="direccion" class="form-label">Dirección</label>
                                <input type="text" class="form-control" id="direccion" placeholder="Ingrese la dirección" required>
                            </div>
                            <div class="col-md-6">
                                <label for="fecha_nacimiento" class="form-label">Fecha de Nacimiento</label>
                                <input type="date" class="form-control" id="fecha_nacimiento" required>
                            </div>
                        </div>
                        <div class="row g-3 mt-3">
                            <div class="col-md-6">
                                <label for="especialidad" class="form-label">Especialidad</label>
                                <select class="form-control" id="especialidad" required>
                                    <option value="" disabled selected>Seleccione una especialidad</option>
                                    <option value="matematicas">Matemáticas</option>
                                    <option value="fisica">Física</option>
                                    <option value="quimica">Química</option>
                                    <option value="biologia">Biología</option>
                                    <option value="informatica">Informática</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="documento_identidad" class="form-label">Documento de Identidad</label>
                                <input type="text" class="form-control" id="documento_identidad" placeholder="Ingrese el documento de identidad" required>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#exampleModal">Registrar</button>
                </div>
                </div>
            </div>
            </div>
            <!-- DATATABLE -->
            <table id="tabla-docentes" class="table table-striped table-hover table-bordered" style="width:100% ;margin: 20px auto;">
                <caption>Lista de Docentes</caption>
                <thead class="table-primary">
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Apellido</th>
                        <th>Correo</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>1</td>
                        <td>Juan</td>
                        <td>Pérez</td>
                        <td>juan.perez@example.com</td>
                        <td><button class="btn btn-primary btn-sm">Editar</button></td>
                    </tr>
                    <tr>
                        <td>2</td>
                        <td>María</td>
                        <td>Gómez</td>
                        <td>maria.gomez@example.com</td>
                        <td><button class="btn btn-primary btn-sm">Editar</button></td>
                    </tr>
                </tbody>
            </table>
    </main>
    <script src="js/docente.js"></script>
    <?php require_once("components/footer.php"); ?>
</body>
</html>