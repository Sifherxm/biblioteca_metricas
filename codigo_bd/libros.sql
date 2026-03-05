CREATE TABLE libros (
    id_libro INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(200) NOT NULL,
    autor VARCHAR(150),
    editorial VARCHAR(100),
    anio_publicacion INT,
    tipo ENUM('digital','fisico') NOT NULL,
    id_categoria INT,
    url_archivo VARCHAR(255),
    imagen VARCHAR(255), -- ruta o nombre del archivo de la imagen
    FOREIGN KEY (id_categoria) REFERENCES categorias(id_categoria)
);