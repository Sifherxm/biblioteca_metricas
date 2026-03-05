CREATE TABLE inventario (
    id_inventario INT AUTO_INCREMENT PRIMARY KEY,
    id_libro INT,
    codigo_ejemplar VARCHAR(50) UNIQUE,
    estado ENUM('disponible','prestado','reservado') DEFAULT 'disponible',
    FOREIGN KEY (id_libro) REFERENCES libros(id_libro)
);