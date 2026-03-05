<?php
session_start();
include "conexion.php";

/* PROTECCIÓN DE SESIÓN */
if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.html");
    exit;
}

/* RUTAS CORRECTAS (FÍSICAS) */
$carpetaImg = __DIR__ . "/../img_libros/";
$carpetaPdf = __DIR__ . "/../pdf_libros/";

/* RUTAS PARA EL NAVEGADOR (WEB) */
$webImg = "../img_libros/";
$webPdf = "../pdf_libros/";

/* CREAR CARPETAS SI NO EXISTEN */
if(!is_dir($carpetaImg)){
    mkdir($carpetaImg, 0777, true);
}
if(!is_dir($carpetaPdf)){
    mkdir($carpetaPdf, 0777, true);
}

/* VALIDAR ID */
if (!isset($_GET['id'])) {
    die("ID de libro no proporcionado.");
}
$id = (int)$_GET['id'];

/* CARGAR LIBRO */
$libroQ = $conn->query("SELECT * FROM libros WHERE id_libro = $id");
if ($libroQ->num_rows == 0) {
    die("Libro no encontrado.");
}
$libro = $libroQ->fetch_assoc();

/* CARGAR CATEGORIAS */
$categorias = $conn->query("SELECT * FROM categorias");

/* ACTUALIZAR LIBRO */
if (isset($_POST['actualizar'])) {

    $titulo = $conn->real_escape_string($_POST['titulo']);
    $autor = $conn->real_escape_string($_POST['autor']);
    $descripcion = $conn->real_escape_string($_POST['descripcion']);
    $editorial = $conn->real_escape_string($_POST['editorial']);
    $anio = ($_POST['anio'] === "" ? "NULL" : (int)$_POST['anio']);
    $tipo = $conn->real_escape_string($_POST['tipo']);
    $categoria = ($_POST['categoria'] === "" ? "NULL" : (int)$_POST['categoria']);

    /* ===== IMAGEN ===== */
    $imagenFinal = $libro['imagen']; // conservar por defecto

    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] == 0) {

        $permitidos = ["jpg","jpeg","png","webp"];
        $extension = strtolower(pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION));

        if (!in_array($extension, $permitidos)) {
            die("Formato de imagen no permitido.");
        }

        $imagenNueva = time() . "_" . basename($_FILES['imagen']['name']);
        $rutaImagenFisica = $carpetaImg . $imagenNueva;

        if (move_uploaded_file($_FILES['imagen']['tmp_name'], $rutaImagenFisica)) {

            // borrar anterior (ruta física correcta)
            if (!empty($libro['imagen']) && file_exists($carpetaImg . $libro['imagen'])) {
                @unlink($carpetaImg . $libro['imagen']);
            }

            $imagenFinal = $imagenNueva;
        } else {
            die("No se pudo guardar la imagen.");
        }
    }

    /* ===== PDF ===== */
    $pdfFinal = $libro['url_archivo']; // conservar por defecto

    // Si cambia a físico, eliminar pdf y dejar NULL
    if ($tipo === "fisico") {
        if (!empty($libro['url_archivo']) && file_exists($carpetaPdf . $libro['url_archivo'])) {
            @unlink($carpetaPdf . $libro['url_archivo']);
        }
        $pdfFinal = NULL;
    }

    // Si es digital y sube nuevo pdf
    if ($tipo === "digital" && isset($_FILES['archivo']) && $_FILES['archivo']['error'] == 0) {

        $extensionPdf = strtolower(pathinfo($_FILES['archivo']['name'], PATHINFO_EXTENSION));
        if ($extensionPdf != "pdf") {
            die("Solo se permiten archivos PDF.");
        }

        $pdfNuevo = time() . "_" . basename($_FILES['archivo']['name']);
        $rutaPdfFisica = $carpetaPdf . $pdfNuevo;

        if (move_uploaded_file($_FILES['archivo']['tmp_name'], $rutaPdfFisica)) {

            if (!empty($libro['url_archivo']) && file_exists($carpetaPdf . $libro['url_archivo'])) {
                @unlink($carpetaPdf . $libro['url_archivo']);
            }

            $pdfFinal = $pdfNuevo;
        } else {
            die("No se pudo guardar el PDF.");
        }
    }

    /* SQL */
    $imagenSQL = ($imagenFinal === "" || $imagenFinal === NULL) ? "NULL" : "'" . $conn->real_escape_string($imagenFinal) . "'";
    $pdfSQL    = ($pdfFinal === "" || $pdfFinal === NULL) ? "NULL" : "'" . $conn->real_escape_string($pdfFinal) . "'";

    $sql = "UPDATE libros SET
                titulo='$titulo',
                autor=" . ($autor === "" ? "NULL" : "'$autor'") . ",
                descripcion=" . ($descripcion === "" ? "NULL" : "'$descripcion'") . ",
                editorial=" . ($editorial === "" ? "NULL" : "'$editorial'") . ",
                anio_publicacion=$anio,
                tipo='$tipo',
                id_categoria=$categoria,
                url_archivo=$pdfSQL,
                imagen=$imagenSQL
            WHERE id_libro=$id";

    if ($conn->query($sql)) {
        echo "<script>alert('Libro actualizado correctamente'); window.location='admin_ver_libros.php';</script>";
        exit;
    } else {
        echo "Error: " . $conn->error;
    }
}
?>

<!doctype html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Editar Libro</title>
  <link rel="stylesheet" href="../admin.css"/>
</head>

<body>
<div class="container">

  <aside class="sidebar">
    <div class="logo">
      <img src="../img/logo_biblioteca.png">
    </div>

    <button onclick="window.location.href='admin_agregar_libros.php'">Gestión Inventario</button>
    <button onclick="window.location.href='admin_ver_libros.php'">Ver Libros</button>
  </aside>

  <main class="main">

    <header class="topbar">
      <h1>Panel Administrador</h1>

      <div class="user-icons">
        <div class="user-menu">
          <div class="user-trigger">
            <img src="../img/alumno_icono.png">
            <span class="user-name"><?php echo htmlspecialchars($_SESSION['nombre']); ?></span>
          </div>
          <div class="dropdown">
            <a href="logout.php">Cerrar sesión</a>
          </div>
        </div>
      </div>
    </header>

    <section class="content">

      <form method="POST" enctype="multipart/form-data" class="form-admin">

        <input type="text" name="titulo" value="<?php echo htmlspecialchars($libro['titulo']); ?>" required>
        <input type="text" name="autor" value="<?php echo htmlspecialchars($libro['autor'] ?? ''); ?>">
        <textarea name="descripcion"><?php echo htmlspecialchars($libro['descripcion'] ?? ''); ?></textarea>
        <input type="text" name="editorial" value="<?php echo htmlspecialchars($libro['editorial'] ?? ''); ?>">
        <input type="number" name="anio" value="<?php echo htmlspecialchars($libro['anio_publicacion'] ?? ''); ?>">

        <select name="tipo" required>
          <option value="digital" <?php echo ($libro['tipo']=="digital" ? "selected" : ""); ?>>Digital</option>
          <option value="fisico"  <?php echo ($libro['tipo']=="fisico" ? "selected" : ""); ?>>Físico</option>
        </select>

        <select name="categoria">
          <option value="">Seleccionar Categoría</option>
          <?php while($cat = $categorias->fetch_assoc()){ ?>
            <option value="<?php echo $cat['id_categoria']; ?>"
              <?php echo ($libro['id_categoria']==$cat['id_categoria'] ? "selected" : ""); ?>>
              <?php echo htmlspecialchars($cat['nombre']); ?>
            </option>
          <?php } ?>
        </select>

        <label>Imagen actual:</label>
        <?php if(!empty($libro['imagen']) && file_exists($carpetaImg.$libro['imagen'])){ ?>
          <div style="margin-bottom:10px;">
            <img src="<?php echo $webImg.$libro['imagen']; ?>" style="max-width:140px; border-radius:10px;">
          </div>
        <?php } else { ?>
          <p style="margin-top:0;">(Sin imagen)</p>
        <?php } ?>

        <label>Subir nueva imagen (opcional):</label>
        <input type="file" name="imagen">

        <label>PDF actual (solo si es digital):</label>
        <?php if(!empty($libro['url_archivo']) && file_exists($carpetaPdf.$libro['url_archivo'])){ ?>
          <p style="margin-top:0;">
            <a href="<?php echo $webPdf.$libro['url_archivo']; ?>" target="_blank">Ver PDF actual</a>
          </p>
        <?php } else { ?>
          <p style="margin-top:0;">(Sin PDF)</p>
        <?php } ?>

        <label>Subir nuevo PDF (opcional):</label>
        <input type="file" name="archivo">

        <button type="submit" name="actualizar">Actualizar Libro</button>

      </form>

    </section>
  </main>
</div>
</body>
</html>