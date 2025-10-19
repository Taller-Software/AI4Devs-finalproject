USE astillero_tools;

-- ======================================
-- INSERTS EN TABLA usuarios
-- ======================================
INSERT INTO usuarios (uuid, nombre, email)
VALUES 
(UUID(), 'Daniel Sánchez Ruiz', 'daniel.sanchez.ruiz.1991@gmail.com'),
(UUID(), 'El Taller del Software', 'eltallerdelsoftware@gmail.com'),
(UUID(), 'Daniel Sánchez - Septeo', 'daniel.sanchez@septeo.com');

-- ======================================
-- INSERTS EN TABLA ubicaciones
-- ======================================
-- 30 ubicaciones de almacén
INSERT INTO ubicaciones (nombre)
VALUES
('Principal A1'),
('Principal A2'),
('Principal A3'),
('Principal A4'),
('Principal A5'),
('Pintura P1'),
('Pintura P2'),
('Carpintería C1'),
('Carpintería C2'),
('Carpintería C3'),
('Soldadura S1'),
('Soldadura S2'),
('Soldadura S3'),
('Electricidad E1'),
('Electricidad E2'),
('Electricidad E3'),
('General Norte'),
('General Sur'),
('General Este'),
('General Oeste'),
('Auxiliar 1'),
('Auxiliar 2'),
('Herramientas Portátiles'),
('Materiales Pesados'),
('de Repuestos'),
('de Cabos y Cables'),
('de Motores'),
('de Equipos de Seguridad'),
('Temporal'),
('Externo');

-- 10 ubicaciones de puestos de trabajo
INSERT INTO ubicaciones (nombre)
VALUES
('Puesto Soldadura 1'),
('Puesto Soldadura 2'),
('Puesto Electricidad 1'),
('Puesto Electricidad 2'),
('Puesto Carpintería 1'),
('Puesto Carpintería 2'),
('Puesto Mantenimiento General'),
('Puesto Montaje de Cubierta'),
('Puesto Control de Calidad'),
('Puesto Dirección Técnica');

-- ======================================
-- INSERTS EN TABLA herramientas
-- ======================================
INSERT INTO herramientas (nombre, codigo)
VALUES
('Taladro Inalámbrico Bosch GSR 12V', 'HR001'),
('Amoladora Angular Makita GA5030', 'HR002'),
('Atornillador Impacto DeWalt DCF887', 'HR003'),
('Sierra Circular Bosch GKS 18V', 'HR004'),
('Multiherramienta Oscilante Dremel 8260', 'HR005'),
('Llave de Impacto Milwaukee M18', 'HR006'),
('Taladro Percutor Hitachi DV18', 'HR007'),
('Pulidora Metabo PE 12-175', 'HR008'),
('Lijadora Orbital Makita BO3710', 'HR009'),
('Cortadora de Metal Bosch GCO 14-24', 'HR010');
