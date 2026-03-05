<?php
include "conexion.php";

$id = $_GET['id'];

$sql = "SELECT * FROM libros WHERE id_libro = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$libro = $stmt->get_result()->fetch_assoc();
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title><?php echo $libro['titulo']; ?></title>
  <link rel="stylesheet" href="../libro.css">
</head>

<body>

<div class="container">

  <!-- IMAGEN -->
  <div class="book-img">
    <img src="/biblioteca/img_libros/<?php echo $libro['imagen']; ?>"
         alt="<?php echo $libro['titulo']; ?>">
  </div>

  <!-- INFO -->
  <div class="book-info">
    <h1><?php echo $libro['titulo']; ?></h1>

    <p class="autor">
      <strong>Autor:</strong> <?php echo $libro['autor']; ?>
    </p>

    <ul class="datos">
      <li><strong>Editorial:</strong> <?php echo $libro['editorial']; ?></li>
      <li><strong>Año:</strong> <?php echo $libro['anio_publicacion']; ?></li>
      <li><strong>Tipo:</strong> <?php echo ucfirst($libro['tipo']); ?></li>
    </ul>

    <!-- DESCRIPCIÓN -->
    <?php if (!empty($libro['descripcion'])) { ?>
      <p class="descripcion">
        <?php echo nl2br($libro['descripcion']); ?>
      </p>
    <?php } ?>

    <!-- ACCIONES -->
    <div class="acciones">
      <a href="../principal.php" class="btn volver">← Volver</a>

      <?php if ($libro['url_archivo']) { ?>

        <!-- VISUALIZAR -->
        <a href="/biblioteca/pdf_libros/<?php echo $libro['url_archivo']; ?>"
           class="btn visualizar"
           target="_blank">
          👁️ Visualizar
        </a>

        <!-- DESCARGAR FORZADO -->
        <a href="download.php?id=<?php echo $libro['id_libro']; ?>"
           class="btn descargar">
          📥 Descargar
        </a>

      <?php } else { ?>
        <span class="no-disponible">Archivo no disponible</span>
      <?php } ?>
    </div>

  </div>

</div>

</body>
</html>
