-- Active: 1764304506890@@127.0.0.1@3306@achipano_travel

CREATE DATABASE Achipano_Travel;
USE Achipano_Travel;


CREATE TABLE turistas(
    id_turista INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(100) NOT NULL,
    apellido VARCHAR(100) NOT NULL,
    telefono VARCHAR(100) NOT NULL UNIQUE,
    correo VARCHAR(100) NOT NULL UNIQUE,
    ubicacion VARCHAR(100) NOT NULL
);

CREATE TABLE hoteles(
    id_hotel INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(100) NOT NULL,
    rif VARCHAR(100) NOT NULL UNIQUE,
    ubicacion VARCHAR(100) NOT NULL,

    CONSTRAINT uq_hotel_nombre_ubicacion
    UNIQUE (nombre,ubicacion)
);

CREATE TABLE tipo_habitaciones(
    id_tipo_habitacion INT PRIMARY KEY AUTO_INCREMENT,
    descripcion VARCHAR(100) NOT NULL,
    precio_base DECIMAL(10,2) NOT NULL CHECK ( precio_base > 0 ),
    id_hotel INT NOT NULL,


    CONSTRAINT fk_habitacion_hotel
    FOREIGN KEY (id_hotel) REFERENCES hoteles(id_hotel),

    CONSTRAINT uq_tipo_habitacion_hotel
    UNIQUE (descripcion,id_hotel)
);

CREATE TABLE tarifas(
    id_tarifa INT PRIMARY KEY AUTO_INCREMENT,
    multiplo_precio DECIMAL(4,2) NOT NULL , -- Este se multiplica por el precio base , ej: la fecha en sunsol del dia x al dia y cuesta el doble para habitaciones individuales
    inicio_temporada DATE NOT NULL,
    fin_temporada DATE NOT NULL CHECK( fin_temporada >= inicio_temporada ),

    id_tipo_habitacion INT NOT NULL,

    CONSTRAINT fk_tipo_habitacion_tarifa
    FOREIGN KEY (id_tipo_habitacion) REFERENCES tipo_habitaciones(id_tipo_habitacion)


);

CREATE TABLE habitaciones(
    id_habitacion INT PRIMARY KEY AUTO_INCREMENT,
    numero VARCHAR(100) NOT NULL,

    id_tipo_habitacion INT NOT NULL,


    CONSTRAINT fk_habitacion_y_su_tipo
    FOREIGN KEY (id_tipo_habitacion) REFERENCES  tipo_habitaciones(id_tipo_habitacion)

);

CREATE TABLE reservas (
    id_reserva INT PRIMARY KEY AUTO_INCREMENT,
    fecha_registro DATETIME NOT NULL DEFAULT(NOW()),
    fecha_hora_desde DATETIME NOT NULL,
    fecha_hora_hasta DATETIME NOT NULL CHECK ( fecha_hora_hasta > fecha_hora_desde ),
    cantidad_personas INT NOT NULL CHECK ( cantidad_personas > 0 ),
    monto_total DECIMAL(10,2) NOT NULL  CHECK ( monto_total > 0 ),

    id_turista INT NOT NULL,
    id_habitacion INT NOT NULL,

    CONSTRAINT fk_turista_reserva
    FOREIGN KEY (id_turista) REFERENCES turistas(id_turista),

    CONSTRAINT fk_habitacion_reserva
    FOREIGN KEY (id_habitacion) REFERENCES habitaciones(id_habitacion)
);