<?php
session_start();
include "conexion.php";

/* PROTECCIÓN DE SESIÓN */
if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.html");
    exit;
}

/* CREAR CARPETAS SI NO EXISTEN */
if(!is_dir("img_libros")){
    mkdir("img_libros", 0777, true);
}

if(!is_dir("pdf_libros")){
    mkdir("pdf_libros", 0777, true);
}

/* GUARDAR LIBRO */
if(isset($_POST['guardar'])){

    $titulo = $conn->real_escape_string($_POST['titulo']);
    $autor = $conn->real_escape_string($_POST['autor']);
    $descripcion = $conn->real_escape_string($_POST['descripcion']);
    $editorial = $conn->real_escape_string($_POST['editorial']);
    $anio = $_POST['anio'];
    $tipo = $_POST['tipo'];
    $categoria = $_POST['categoria'];

  /* ===== SUBIR IMAGEN ===== */
$imagenNombre = "";

if(isset($_FILES['imagen']) && $_FILES['imagen']['error'] == 0){

    $permitidos = ["jpg","jpeg","png","webp"];
    $extension = strtolower(pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION));

    if(in_array($extension, $permitidos)){

        $imagenNombre = time() . "_" . basename($_FILES['imagen']['name']);

        // carpeta física REAL (una carpeta arriba: ../img_libros)
        $carpetaImg = __DIR__ . "/../img_libros/";
        if(!is_dir($carpetaImg)){
            mkdir($carpetaImg, 0777, true);
        }

        $rutaImagenFisica = $carpetaImg . $imagenNombre;

        if(!move_uploaded_file($_FILES['imagen']['tmp_name'], $rutaImagenFisica)){
            die("Error al guardar la imagen en: " . $rutaImagenFisica);
        }

    } else {
        die("Formato de imagen no permitido.");
    }
}

   /* ===== SUBIR PDF (SI ES DIGITAL) ===== */
$archivoNombre = NULL;

if($tipo == "digital" && isset($_FILES['archivo']) && $_FILES['archivo']['error'] == 0){

    $extensionPdf = strtolower(pathinfo($_FILES['archivo']['name'], PATHINFO_EXTENSION));

    if($extensionPdf == "pdf"){

        $archivoNombre = time() . "_" . basename($_FILES['archivo']['name']);

        $carpetaPdf = __DIR__ . "/../pdf_libros/";
        if(!is_dir($carpetaPdf)){
            mkdir($carpetaPdf, 0777, true);
        }

        $rutaPdfFisica = $carpetaPdf . $archivoNombre;

        if(!move_uploaded_file($_FILES['archivo']['tmp_name'], $rutaPdfFisica)){
            die("Error al guardar el PDF en: " . $rutaPdfFisica);
        }

    } else {
        die("Solo se permiten archivos PDF.");
    }
}

    /* INSERTAR EN BD */
    $sql = "INSERT INTO libros 
    (titulo, autor, descripcion, editorial, anio_publicacion, tipo, id_categoria, url_archivo, imagen)
    VALUES
    ('$titulo','$autor','$descripcion','$editorial','$anio','$tipo','$categoria','$archivoNombre','$imagenNombre')";

    if($conn->query($sql)){
        echo "<script>alert('Libro agregado correctamente'); window.location='admin_agregar_libros.php';</script>";
    } else {
        echo "Error: " . $conn->error;
    }
}

/* CARGAR CATEGORIAS */
$categorias = $conn->query("SELECT * FROM categorias");
?>

<!doctype html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Agregar Libro</title>
  <link rel="stylesheet" href="../admin.css"/>
</head>

<body>
<div class="container">

  <!-- SIDEBAR -->
  <aside class="sidebar">
    <div class="logo">
      <img src="../img/logo_biblioteca.png">
    </div>

    <button onclick="window.location.href='admin_ver_libros.php'">
      Gestión Inventario
    </button>
  </aside>

  <!-- MAIN -->
  <main class="main">

    <!-- TOPBAR -->
    <header class="topbar">
      <h1>Panel Administrador</h1>

      <div class="user-icons">
        <div class="user-menu">
          <div class="user-trigger">
            <img src="../img/alumno_icono.png">
            <span class="user-name">
              <?php echo htmlspecialchars($_SESSION['nombre']); ?>
            </span>
          </div>

          <div class="dropdown">
            <a href="logout.php">Cerrar sesión</a>
          </div>
        </div>
      </div>
    </header>

    <!-- CONTENIDO -->
    <section class="content">


      <form method="POST" enctype="multipart/form-data" class="form-admin">

        <input type="text" name="titulo" placeholder="Título" required>

        <input type="text" name="autor" placeholder="Autor">

        <textarea name="descripcion" placeholder="Descripción"></textarea>

        <input type="text" name="editorial" placeholder="Editorial">

        <input type="number" name="anio" placeholder="Año">

        <select name="tipo" required>
            <option value="digital">Digital</option>
            <option value="fisico">Físico</option>
        </select>

        <select name="categoria" required>
            <option value="">Seleccionar Categoría</option>
            <?php while($cat = $categorias->fetch_assoc()){ ?>
                <option value="<?php echo $cat['id_categoria']; ?>">
                    <?php echo $cat['nombre']; ?>
                </option>
            <?php } ?>
        </select>

        <label>Imagen del libro:</label>
        <input type="file" name="imagen" required>

        <label>Archivo PDF (si es digital):</label>
        <input type="file" name="archivo">

        <button type="submit" name="guardar">Guardar Libro</button>

      </form>

    </section>
  </main>
</div>

</body>
</html>