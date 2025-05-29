<?php
if (!isset($_SESSION['name'])) {
    header('Location: .');
    exit();
}
?>

<!DOCTYPE html>
<html lang="ES">

<head>
    <?php require_once("public/components/head.php"); ?>
    <title>Preguntas Frecuentes (FAQ)</title>
    <style>
        .accordion-button:not(.collapsed) {
            color: #0c63e4;
            /* Color de texto del botón de acordeón cuando está activo */
            background-color: #e7f1ff;
            /* Color de fondo del botón de acordeón cuando está activo */
        }

        .accordion-button:focus {
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, .25);
            /* Sombra al enfocar */
        }

        .faq-category h4 {
            margin-top: 2rem;
            margin-bottom: 1rem;
            color: #343a40;
            /* Un color un poco más oscuro para los títulos de categoría */
            border-bottom: 2px solid #0d6efd;
            /* Línea azul debajo del título de categoría */
            padding-bottom: 0.5rem;
        }

        .accordion-body strong {
            color: #212529;
        }
    </style>
</head>

<body class="d-flex flex-column min-vh-100">

    <?php require_once("public/components/sidebar.php"); ?>

    <main class="main-content flex-shrink-0">
        <section class="container py-4">
            <h2 class="text-primary text-center mb-4" style="font-weight: 600; letter-spacing: 1px;">Preguntas Frecuentes (FAQ)</h2>

            <div class="row justify-content-center">
                <div class="col-md-10 col-lg-9">

                    <div class="faq-category">
                        <h4>Gestión General</h4>
                        <div class="accordion" id="faqAccordionGeneral">
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="headingGeneral1">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseGeneral1" aria-expanded="false" aria-controls="collapseGeneral1">
                                        ¿Cómo puedo registrar un nuevo elemento (ej. un área, un título, una categoría)?
                                    </button>
                                </h2>
                                <div id="collapseGeneral1" class="accordion-collapse collapse" aria-labelledby="headingGeneral1" data-bs-parent="#faqAccordionGeneral">
                                    <div class="accordion-body">
                                        En la pantalla de gestión del módulo correspondiente (ej. "Gestionar Áreas"), busca el botón "Registrar [Nombre del Módulo]" (ej. "Registrar Área"). Al hacer clic, se abrirá un formulario donde deberás ingresar los datos solicitados y luego pulsar "Guardar" o "Registrar".
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="headingGeneral2">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseGeneral2" aria-expanded="false" aria-controls="collapseGeneral2">
                                        El sistema me dice que "[El elemento] ya existe" cuando intento registrar algo. ¿Qué debo hacer?
                                    </button>
                                </h2>
                                <div id="collapseGeneral2" class="accordion-collapse collapse" aria-labelledby="headingGeneral2" data-bs-parent="#faqAccordionGeneral">
                                    <div class="accordion-body">
                                        Esto significa que ya existe un registro con los mismos datos identificativos (ej. mismo nombre, o misma combinación de código y nombre). Por favor, verifica los datos que ingresaste y asegúrate de que sean únicos según lo requiera el módulo.
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="headingGeneral3">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseGeneral3" aria-expanded="false" aria-controls="collapseGeneral3">
                                        ¿Cómo puedo modificar la información de un elemento ya registrado?
                                    </button>
                                </h2>
                                <div id="collapseGeneral3" class="accordion-collapse collapse" aria-labelledby="headingGeneral3" data-bs-parent="#faqAccordionGeneral">
                                    <div class="accordion-body">
                                        En la tabla que lista los elementos, busca el que deseas cambiar y haz clic en el botón "Modificar" (usualmente de color amarillo o naranja) en su fila. Se abrirá un formulario con los datos actuales, donde podrás hacer los cambios y luego guardarlos.
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="headingGeneral4">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseGeneral4" aria-expanded="false" aria-controls="collapseGeneral4">
                                        ¿Qué sucede cuando "elimino" un registro? ¿Se borra permanentemente?
                                    </button>
                                </h2>
                                <div id="collapseGeneral4" class="accordion-collapse collapse" aria-labelledby="headingGeneral4" data-bs-parent="#faqAccordionGeneral">
                                    <div class="accordion-body">
                                        En la mayoría de los casos, "eliminar" un registro realiza una eliminación lógica. Esto significa que el elemento se marca como inactivo y no aparecerá en las listas y búsquedas activas, pero la información se conserva en la base de datos para mantener la integridad de los datos históricos y las relaciones.
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="headingGeneral5">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseGeneral5" aria-expanded="false" aria-controls="collapseGeneral5">
                                        ¿Cómo puedo encontrar un registro específico en las tablas largas?
                                    </button>
                                </h2>
                                <div id="collapseGeneral5" class="accordion-collapse collapse" aria-labelledby="headingGeneral5" data-bs-parent="#faqAccordionGeneral">
                                    <div class="accordion-body">
                                        Todas las tablas principales de listado cuentan con un campo de "Buscar:" en la parte superior. Simplemente escribe parte del nombre o código que buscas y la tabla se filtrará automáticamente.
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="headingGeneral6">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseGeneral6" aria-expanded="false" aria-controls="collapseGeneral6">
                                        Veo mensajes debajo de los campos del formulario, ¿qué significan?
                                    </button>
                                </h2>
                                <div id="collapseGeneral6" class="accordion-collapse collapse" aria-labelledby="headingGeneral6" data-bs-parent="#faqAccordionGeneral">
                                    <div class="accordion-body">
                                        Esos son mensajes de validación. Te ayudan a ingresar los datos en el formato correcto (ej. longitud mínima o máxima de caracteres, tipo de dato). Si un campo está incorrecto, el mensaje te indicará cómo corregirlo.
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="headingGeneral7">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseGeneral7" aria-expanded="false" aria-controls="collapseGeneral7">
                                        ¿Qué navegador web se recomienda para usar el sistema?
                                    </button>
                                </h2>
                                <div id="collapseGeneral7" class="accordion-collapse collapse" aria-labelledby="headingGeneral7" data-bs-parent="#faqAccordionGeneral">
                                    <div class="accordion-body">
                                        El sistema es compatible con las versiones más recientes de navegadores modernos como Google Chrome, Mozilla Firefox, Microsoft Edge y Safari. Para una mejor experiencia, asegúrate de tener tu navegador actualizado.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="faq-category">
                        <h4>Módulo: Títulos Académicos</h4>
                        <div class="accordion" id="faqAccordionTitulos">
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="headingTitulos1">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTitulos1" aria-expanded="false" aria-controls="collapseTitulos1">
                                        Al registrar un título, ¿qué son el "Tipo (prefijo)" y el "Nombre"?
                                    </button>
                                </h2>
                                <div id="collapseTitulos1" class="accordion-collapse collapse" aria-labelledby="headingTitulos1" data-bs-parent="#faqAccordionTitulos">
                                    <div class="accordion-body">
                                        El "Tipo (prefijo)" es el grado general (ej. Ingeniero, Licenciado, TSU, Master, Doctorado). El "Nombre" es la especialización (ej. "En Informática", "En Educación Mención..."). La combinación de ambos debe ser única.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="faq-category">
                        <h4>Módulo: Certificados</h4>
                        <div class="accordion" id="faqAccordionCertificados">
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="headingCertificados1">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseCertificados1" aria-expanded="false" aria-controls="collapseCertificados1">
                                        ¿Qué información se requiere para un nuevo certificado?
                                    </button>
                                </h2>
                                <div id="collapseCertificados1" class="accordion-collapse collapse" aria-labelledby="headingCertificados1" data-bs-parent="#faqAccordionCertificados">
                                    <div class="accordion-body">
                                        Debes proporcionar el "Nombre" del certificado y seleccionar el "Trayecto" académico al que está asociado. La combinación de nombre y trayecto debe ser única.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="faq-category">
                        <h4>Módulo: Malla Curricular</h4>
                        <div class="accordion" id="faqAccordionMalla">
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="headingMalla1">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseMalla1" aria-expanded="false" aria-controls="collapseMalla1">
                                        ¿Cómo asigno Unidades Curriculares (UCs) a una malla?
                                    </button>
                                </h2>
                                <div id="collapseMalla1" class="accordion-collapse collapse" aria-labelledby="headingMalla1" data-bs-parent="#faqAccordionMalla">
                                    <div class="accordion-body">
                                        En la lista de mallas, busca la malla deseada y haz clic en "Asignar UC". Se abrirá un modal donde podrás seleccionar las UCs de una lista y agregarlas. Finalmente, guarda la asignación.
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="headingMalla2">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseMalla2" aria-expanded="false" aria-controls="collapseMalla2">
                                        ¿Dónde puedo ver qué UCs o certificados tiene asignados una malla?
                                    </button>
                                </h2>
                                <div id="collapseMalla2" class="accordion-collapse collapse" aria-labelledby="headingMalla2" data-bs-parent="#faqAccordionMalla">
                                    <div class="accordion-body">
                                        En la página "Gestionar Malla Curricular", puedes usar los botones "Ver Asignación UC" o "Ver Asignación Certificados" para cambiar a las tablas que muestran estas relaciones.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="faq-category">
                        <h4>Módulo: Horario</h4>
                        <div class="accordion" id="faqAccordionHorario">
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="headingHorario1">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseHorario1" aria-expanded="false" aria-controls="collapseHorario1">
                                        ¿Cuál es el primer paso para registrar un horario nuevo?
                                    </button>
                                </h2>
                                <div id="collapseHorario1" class="accordion-collapse collapse" aria-labelledby="headingHorario1" data-bs-parent="#faqAccordionHorario">
                                    <div class="accordion-body">
                                        Debes ir a "Gestionar Horario" y hacer clic en "Registrar Horario". Lo primero será seleccionar la "Sección" y la "Fase" para la cual deseas crear el horario.
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="headingHorario2">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseHorario2" aria-expanded="false" aria-controls="collapseHorario2">
                                        Al añadir una clase a la grilla, ¿cómo se seleccionan las Unidades Curriculares?
                                    </button>
                                </h2>
                                <div id="collapseHorario2" class="accordion-collapse collapse" aria-labelledby="headingHorario2" data-bs-parent="#faqAccordionHorario">
                                    <div class="accordion-body">
                                        Después de seleccionar un Docente en el modal de clase ("Añadir/Editar Clase"), la lista de Unidades Curriculares se filtrará para mostrar aquellas asociadas a ese docente y, si aplica, al trayecto de la sección del horario.
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="headingHorario3">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseHorario3" aria-expanded="false" aria-controls="collapseHorario3">
                                        ¿Qué pasa si intento registrar un horario para una Sección y Fase que ya tiene uno activo?
                                    </button>
                                </h2>
                                <div id="collapseHorario3" class="accordion-collapse collapse" aria-labelledby="headingHorario3" data-bs-parent="#faqAccordionHorario">
                                    <div class="accordion-body">
                                        El sistema te alertará indicando que ya existe un horario para esa combinación. No podrás crear un duplicado. Deberás modificar el horario existente o elegir una Sección/Fase diferente.
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="headingHorario4">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseHorario4" aria-expanded="false" aria-controls="collapseHorario4">
                                        ¿Puedo definir mis propias franjas horarias?
                                    </button>
                                </h2>
                                <div id="collapseHorario4" class="accordion-collapse collapse" aria-labelledby="headingHorario4" data-bs-parent="#faqAccordionHorario">
                                    <div class="accordion-body">
                                        Sí. Dentro del formulario principal de creación/modificación de horario (donde ves la grilla), hay un botón "Nueva Franja". Al pulsarlo, podrás ingresar una hora de inicio y fin para crear una nueva franja que se añadirá a la grilla para esa sesión de edición.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="faq-category">
                        <h4>Mantenimiento del Sistema (Respaldos)</h4>
                        <div class="accordion" id="faqAccordionBackup">
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="headingBackup1">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseBackup1" aria-expanded="false" aria-controls="collapseBackup1">
                                        ¿Cómo puedo generar un respaldo del sistema?
                                    </button>
                                </h2>
                                <div id="collapseBackup1" class="accordion-collapse collapse" aria-labelledby="headingBackup1" data-bs-parent="#faqAccordionBackup">
                                    <div class="accordion-body">
                                        En la sección "Gestionar Mantenimiento del Sistema", dentro de "Respaldo de Bases de Datos", haz clic en el botón "Generar Nuevo Respaldo". El sistema creará un archivo comprimido (.zip) con las bases de datos.
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="headingBackup2">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseBackup2" aria-expanded="false" aria-controls="collapseBackup2">
                                        ¿Qué incluye el respaldo del sistema?
                                    </button>
                                </h2>
                                <div id="collapseBackup2" class="accordion-collapse collapse" aria-labelledby="headingBackup2" data-bs-parent="#faqAccordionBackup">
                                    <div class="accordion-body">
                                        El respaldo incluye una copia completa de la base de datos principal de la aplicación y de la base de datos de la bitácora.
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="headingBackup3">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseBackup3" aria-expanded="false" aria-controls="collapseBackup3">
                                        ¿Cómo puedo restaurar el sistema a un punto anterior?
                                    </button>
                                </h2>
                                <div id="collapseBackup3" class="accordion-collapse collapse" aria-labelledby="headingBackup3" data-bs-parent="#faqAccordionBackup">
                                    <div class="accordion-body">
                                        En la sección "Restaurar Sistema desde Respaldo", selecciona un archivo de la lista desplegable que dice "Seleccione un archivo de respaldo (.zip)". Luego, haz clic en el botón "Restaurar Sistema".
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="headingBackup4">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseBackup4" aria-expanded="false" aria-controls="collapseBackup4">
                                        <strong>¡Importante!</strong> ¿Qué debo tener en cuenta antes de restaurar un respaldo?
                                    </button>
                                </h2>
                                <div id="collapseBackup4" class="accordion-collapse collapse" aria-labelledby="headingBackup4" data-bs-parent="#faqAccordionBackup">
                                    <div class="accordion-body">
                                        Restaurar un respaldo <strong>reemplazará los datos actuales</strong> de ambas bases de datos (principal y bitácora) con los datos del archivo de respaldo seleccionado. <strong>Cualquier cambio realizado en el sistema después de la fecha de creación de ese respaldo se perderá.</strong> Asegúrate de seleccionar el archivo correcto y de que realmente necesitas realizar esta operación, ya que es irreversible.
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="headingBackup5">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseBackup5" aria-expanded="false" aria-controls="collapseBackup5">
                                        ¿Dónde se guardan los respaldos y cómo aparecen en la lista para restaurar?
                                    </button>
                                </h2>
                                <div id="collapseBackup5" class="accordion-collapse collapse" aria-labelledby="headingBackup5" data-bs-parent="#faqAccordionBackup">
                                    <div class="accordion-body">
                                        Los respaldos se guardan en una carpeta específica en el servidor. La lista desplegable para restaurar te mostrará los archivos de respaldo disponibles, usualmente identificados por la fecha y hora de su creación (ej. "Respaldo del AAAA/MM/DD HH:MM:SS").
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </section>
    </main>

    <?php require_once("public/components/footer.php"); ?>

</body>

</html>