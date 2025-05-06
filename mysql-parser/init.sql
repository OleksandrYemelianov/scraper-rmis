USE `parser`;

SET NAMES utf8mb4;

# Дамп таблицы parse_state
# ------------------------------------------------------------

DROP TABLE IF EXISTS `parse_state`;

CREATE TABLE `parse_state` (
                               `id` int NOT NULL AUTO_INCREMENT,
                               `profile_id` int NOT NULL,
                               `update_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                               `last` int NOT NULL,
                               `all` int NOT NULL DEFAULT '0',
                               `current` int NOT NULL DEFAULT '0',
                               `complete` tinyint(1) NOT NULL DEFAULT '0',
                               PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

LOCK TABLES `parse_state` WRITE;
/*!40000 ALTER TABLE `parse_state` DISABLE KEYS */;

INSERT INTO `parse_state` (`id`, `profile_id`, `update_at`, `last`, `all`, `current`, `complete`)
VALUES
    (1,9,'2025-03-30 20:32:37',45,45,45,1),
    (2,10,'2025-03-11 11:30:19',0,1224,1224,1);

/*!40000 ALTER TABLE `parse_state` ENABLE KEYS */;
UNLOCK TABLES;


# Дамп таблицы profiles
# ------------------------------------------------------------

DROP TABLE IF EXISTS `profiles`;

CREATE TABLE `profiles` (
                            `id` int NOT NULL AUTO_INCREMENT,
                            `name` varchar(255) NOT NULL,
                            `login` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
                            `password` varchar(255) DEFAULT NULL,
                            `diler` varchar(100) DEFAULT NULL,
                            `nds` int DEFAULT NULL,
                            `parseDesc` tinyint(1) DEFAULT '1',
                            `parsePhoto` tinyint(1) DEFAULT '1',
                            PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

LOCK TABLES `profiles` WRITE;
/*!40000 ALTER TABLE `profiles` DISABLE KEYS */;

INSERT INTO `profiles` (`id`, `name`, `login`, `password`, `diler`, `nds`, `parseDesc`, `parsePhoto`)
VALUES
    (9,'gomobile','','','gomobile',18,1,0),
    (10,'morlevi','admin','perparol','morlevi',18,1,0);

/*!40000 ALTER TABLE `profiles` ENABLE KEYS */;
UNLOCK TABLES;


# Дамп таблицы profiles_categories
# ------------------------------------------------------------

DROP TABLE IF EXISTS `profiles_categories`;

CREATE TABLE `profiles_categories` (
                                       `id` int unsigned NOT NULL AUTO_INCREMENT,
                                       `profile_id` int NOT NULL,
                                       `name` varchar(150) DEFAULT NULL,
                                       `url` varchar(200) DEFAULT NULL,
                                       `collectionId` int DEFAULT NULL,
                                       PRIMARY KEY (`id`),
                                       KEY `profile_id` (`profile_id`),
                                       CONSTRAINT `profile` FOREIGN KEY (`profile_id`) REFERENCES `profiles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

LOCK TABLES `profiles_categories` WRITE;
/*!40000 ALTER TABLE `profiles_categories` DISABLE KEYS */;

INSERT INTO `profiles_categories` (`id`, `profile_id`, `name`, `url`, `collectionId`)
VALUES
    (15,10,'Motherboards','https://www.morlevi.co.il/Cat/421',458),
    (16,10,'GPU cards','https://www.morlevi.co.il/Cat/86',315),
    (25,9,'Aple','https://www.gomobile.co.il/category/%d7%9e%d7%9b%d7%a9%d7%99%d7%a8%d7%99-apple/',315),
    (26,9,'Samsung','https://www.gomobile.co.il/category/%d7%9e%d7%9b%d7%a9%d7%99%d7%a8%d7%99-samsung/',153);

/*!40000 ALTER TABLE `profiles_categories` ENABLE KEYS */;
UNLOCK TABLES;


# Дамп таблицы profiles_prices
# ------------------------------------------------------------

DROP TABLE IF EXISTS `profiles_prices`;

CREATE TABLE `profiles_prices` (
                                   `id` int unsigned NOT NULL AUTO_INCREMENT,
                                   `category_id` int unsigned NOT NULL,
                                   `condition` int DEFAULT NULL,
                                   `sign` varchar(1) DEFAULT NULL,
                                   `margin` int DEFAULT NULL,
                                   PRIMARY KEY (`id`),
                                   KEY `category_id` (`category_id`),
                                   CONSTRAINT `profiles_prices_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `profiles_categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

LOCK TABLES `profiles_prices` WRITE;
/*!40000 ALTER TABLE `profiles_prices` DISABLE KEYS */;

INSERT INTO `profiles_prices` (`id`, `category_id`, `condition`, `sign`, `margin`)
VALUES
    (38,15,100,'+',10),
    (39,15,300,'+',50),
    (40,16,1000,'+',200),
    (41,16,3000,'%',30),
    (42,16,5000,'+',1500),
    (51,26,5500,'%',20),
    (52,26,7000,'+',500);

/*!40000 ALTER TABLE `profiles_prices` ENABLE KEYS */;
UNLOCK TABLES;


# Дамп таблицы profiles_proxy
# ------------------------------------------------------------

DROP TABLE IF EXISTS `profiles_proxy`;

CREATE TABLE `profiles_proxy` (
                                  `id` int unsigned NOT NULL AUTO_INCREMENT,
                                  `profile_id` int NOT NULL,
                                  `host` varchar(200) DEFAULT NULL,
                                  `port` int DEFAULT NULL,
                                  `login` varchar(100) DEFAULT NULL,
                                  `password` varchar(100) DEFAULT NULL,
                                  PRIMARY KEY (`id`),
                                  KEY `profile_proxy` (`profile_id`),
                                  CONSTRAINT `profile_proxy` FOREIGN KEY (`profile_id`) REFERENCES `profiles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

LOCK TABLES `profiles_proxy` WRITE;
/*!40000 ALTER TABLE `profiles_proxy` DISABLE KEYS */;

INSERT INTO `profiles_proxy` (`id`, `profile_id`, `host`, `port`, `login`, `password`)
VALUES
    (5,10,'',0,'',''),
    (10,9,'',0,'','');

/*!40000 ALTER TABLE `profiles_proxy` ENABLE KEYS */;
UNLOCK TABLES;


# Дамп таблицы profiles_sku
# ------------------------------------------------------------

DROP TABLE IF EXISTS `profiles_sku`;

CREATE TABLE `profiles_sku` (
                                `id` int unsigned NOT NULL AUTO_INCREMENT,
                                `profile_id` int NOT NULL,
                                `begin` varchar(50) DEFAULT NULL,
                                `number` int DEFAULT NULL,
                                `end` varchar(50) DEFAULT NULL,
                                PRIMARY KEY (`id`),
                                KEY `profile_sku` (`profile_id`),
                                CONSTRAINT `profile_sku` FOREIGN KEY (`profile_id`) REFERENCES `profiles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

LOCK TABLES `profiles_sku` WRITE;
/*!40000 ALTER TABLE `profiles_sku` DISABLE KEYS */;

INSERT INTO `profiles_sku` (`id`, `profile_id`, `begin`, `number`, `end`)
VALUES
    (6,10,'ML',4444,'VI'),
    (11,9,'GM',222,'ttt');

/*!40000 ALTER TABLE `profiles_sku` ENABLE KEYS */;
UNLOCK TABLES;
