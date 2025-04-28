-- credisurgir_db.c19_garantes definition

CREATE TABLE `c19_garantes` (
  `garante_id` int(11) NOT NULL AUTO_INCREMENT,
  `loan_id` int(11) DEFAULT NULL,
  `nombre` varchar(250) DEFAULT NULL,
  `ci` varchar(255) DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `cellphone` varchar(255) DEFAULT NULL,
  `direccion_hogar` varchar(255) DEFAULT NULL,
  `direccion_trabajo` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`garante_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4;