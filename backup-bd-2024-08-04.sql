/*M!999999\- enable the sandbox mode */ 
-- MariaDB dump 10.19  Distrib 10.6.22-MariaDB, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: On_tickets_no
-- ------------------------------------------------------
-- Server version	10.6.22-MariaDB-0ubuntu0.22.04.1

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `cache`
--

DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache`
--

LOCK TABLES `cache` WRITE;
/*!40000 ALTER TABLE `cache` DISABLE KEYS */;
INSERT INTO `cache` VALUES ('ticketspro_cache_356a192b7913b04c54574d18c28d46e6395428ab','i:1;',1753273026),('ticketspro_cache_356a192b7913b04c54574d18c28d46e6395428ab:timer','i:1753273026;',1753273026),('ticketspro_cache_livewire-rate-limiter:cb65ac0eb93e0e9a96110ab89069b1ea4fd6ffee','i:1;',1753321600),('ticketspro_cache_livewire-rate-limiter:cb65ac0eb93e0e9a96110ab89069b1ea4fd6ffee:timer','i:1753321600;',1753321600),('ticketspro_cache_livewire-rate-limiter:eb5d609fe655d032d3fb40aabb5b5f7af4ebcbb5','i:1;',1753321721),('ticketspro_cache_livewire-rate-limiter:eb5d609fe655d032d3fb40aabb5b5f7af4ebcbb5:timer','i:1753321721;',1753321721),('ticketspro_cache_spatie.permission.cache','a:3:{s:5:\"alias\";a:0:{}s:11:\"permissions\";a:0:{}s:5:\"roles\";a:0:{}}',1753569801),('ticketspro_cache_testmail@mail.com|191.80.133.104','i:2;',1753322346),('ticketspro_cache_testmail@mail.com|191.80.133.104:timer','i:1753322346;',1753322346);
/*!40000 ALTER TABLE `cache` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache_locks`
--

DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache_locks`
--

LOCK TABLES `cache_locks` WRITE;
/*!40000 ALTER TABLE `cache_locks` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache_locks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `entradas`
--

DROP TABLE IF EXISTS `entradas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `entradas` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `evento_id` bigint(20) unsigned NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `stock_inicial` int(11) NOT NULL,
  `stock_actual` int(11) NOT NULL,
  `max_por_compra` int(11) NOT NULL DEFAULT 1,
  `precio` decimal(10,2) NOT NULL,
  `valido_todo_el_evento` tinyint(1) NOT NULL DEFAULT 1,
  `disponible_desde` datetime DEFAULT NULL,
  `disponible_hasta` datetime DEFAULT NULL,
  `tipo` enum('digital','fisico') NOT NULL DEFAULT 'digital',
  `visible` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `entradas_evento_id_foreign` (`evento_id`),
  CONSTRAINT `entradas_evento_id_foreign` FOREIGN KEY (`evento_id`) REFERENCES `eventos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `entradas`
--

LOCK TABLES `entradas` WRITE;
/*!40000 ALTER TABLE `entradas` DISABLE KEYS */;
INSERT INTO `entradas` VALUES (1,1,'Early Birds','Entrada VIP',2,0,3,500.00,1,NULL,NULL,'digital',1,'2025-07-22 14:55:15','2025-07-25 22:41:49'),(2,2,'GENERAL 1','Entrada General',5,5,3,2500.00,1,NULL,NULL,'digital',1,'2025-07-22 20:52:13','2025-07-22 20:52:13'),(3,3,'GENERAL 1',NULL,5,5,3,2500.00,1,NULL,NULL,'digital',1,'2025-07-22 20:55:49','2025-07-22 20:55:49'),(4,4,'GENERAL 1',NULL,5,5,3,39000.00,1,NULL,NULL,'digital',1,'2025-07-22 20:59:17','2025-07-22 20:59:17');
/*!40000 ALTER TABLE `entradas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `eventos`
--

DROP TABLE IF EXISTS `eventos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `eventos` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `ubicacion` varchar(255) NOT NULL,
  `fecha_inicio` datetime NOT NULL,
  `fecha_fin` datetime NOT NULL,
  `descripcion` text DEFAULT NULL,
  `imagen` varchar(255) DEFAULT NULL,
  `estado` varchar(255) NOT NULL DEFAULT 'active',
  `restringir_edad` tinyint(1) NOT NULL DEFAULT 0,
  `edad_min_hombres` int(11) DEFAULT NULL,
  `edad_min_mujeres` int(11) DEFAULT NULL,
  `requerir_dni` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `organizador_id` bigint(20) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `eventos_slug_unique` (`slug`),
  KEY `eventos_organizador_id_foreign` (`organizador_id`),
  CONSTRAINT `eventos_organizador_id_foreign` FOREIGN KEY (`organizador_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `eventos`
--

LOCK TABLES `eventos` WRITE;
/*!40000 ALTER TABLE `eventos` DISABLE KEYS */;
INSERT INTO `eventos` VALUES (1,'PANTERA CLUB','pantera-club','Punta Carrasco\nAv Rafael Obligado 2221\nCapital Federal, C.A.B.A.','2025-10-29 22:22:00','2025-11-30 22:22:00','FIESTA PRIVADA','eventos/pantera.jpg','activo',0,NULL,NULL,0,'2025-07-22 14:53:57','2025-07-22 14:53:57',1),(2,'Banda Broder Bastos convidando Músicas de Brasil','banda-broder-bastos-convidando-musicas-de-brasil','La Biblioteca Café\nMarcelo T. de Alvear 1155\nCapital Federal, C.A.B.A.','2025-11-30 22:22:00','2025-11-30 23:59:00','La Biblioteca Café\nLa Biblioteca Café inició sus actividades en septiembre de 2000. El objetivo de su creadora, Edith Margulis, era -y es - abrir un espacio con cabida a espectáculos musicales, teatrales y todo tipo de actividades culturales, incluyendo dentro de lo cultural una propuesta gastronómica, acompañada de una ambientación armónica y atención personalizada.\\\\r\\\\n\\\\r\\\\n','eventos/BANDA BRODER.png','activo',0,NULL,NULL,0,'2025-07-22 20:51:37','2025-07-22 20:51:37',1),(3,'Somos Las Chicas de la Culpa ','somos-las-chicas-de-la-culpa','VIA STREAMING','2025-11-11 21:00:00','2025-11-12 22:00:00','Malena Guinzburg, Fernanda Metilli, Connie Ballarini y Natalia Carulias SOMOS LAS CHICAS DE LA CULPA\nEllas rompieron con los formatos de comedia, y la rompieron toda!\nLlegan al teatro El Nacional Sancor Seguros todos los Martes desde el 9 de Abril, presencial Y POR STREAMING!\nTodas las funciones son distintas y delirantes! No es una obra de teatro, no es stand up, SOMOS Las Chicas de la Culpa, una juntada con tus amigas más zarpadas!','eventos/streaming.png','activo',0,NULL,NULL,0,'2025-07-22 20:55:42','2025-07-22 20:55:42',1),(4,'Pintando el grito de Edvard - Pintura Fluo','pintando-el-grito-de-edvard-pintura-fluo','Las Meninas Galeria\nGaleria y Taller de Experiencias de arte, creada y dirigida por artistas apasionados.','2025-11-10 18:00:00','2025-11-10 20:00:00','Evento de 2 horas con guia artistica y pintura fluo, para que puedas recrear tu version de esta obra de Edvard Munch. No se requiere tener conocimientos ni experiencia previa. Incluye todos los materiales, bebida agua cafe o vino y snack.','eventos/EL GRITO.png','activo',0,NULL,NULL,0,'2025-07-22 20:59:06','2025-07-22 20:59:06',1);
/*!40000 ALTER TABLE `eventos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `failed_jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `failed_jobs`
--

LOCK TABLES `failed_jobs` WRITE;
/*!40000 ALTER TABLE `failed_jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `failed_jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `job_batches`
--

DROP TABLE IF EXISTS `job_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `job_batches`
--

LOCK TABLES `job_batches` WRITE;
/*!40000 ALTER TABLE `job_batches` DISABLE KEYS */;
/*!40000 ALTER TABLE `job_batches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jobs`
--

DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) unsigned NOT NULL,
  `reserved_at` int(10) unsigned DEFAULT NULL,
  `available_at` int(10) unsigned NOT NULL,
  `created_at` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jobs`
--

LOCK TABLES `jobs` WRITE;
/*!40000 ALTER TABLE `jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `magic_links`
--

DROP TABLE IF EXISTS `magic_links`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `magic_links` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `expires_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `magic_links_token_unique` (`token`),
  KEY `magic_links_email_index` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `magic_links`
--

LOCK TABLES `magic_links` WRITE;
/*!40000 ALTER TABLE `magic_links` DISABLE KEYS */;
INSERT INTO `magic_links` VALUES (1,'neuquenrenault@gmail.com','3uUi8mCry3JveOuV1pxw1lqTPbIfcu9Yr1Wre5DoOPzcovVUTVFSqRjrcmdFu4sP','2025-07-23 02:39:59','2025-07-23 00:39:59','2025-07-23 00:39:59'),(2,'ezedecandido@gmail.com','PiP3hyKqAvDMPxwj7mRZl180rTyJdjrzSefDFvchcXLYKS2Epa4L5YmVNBQ656ZX','2025-07-23 02:40:25','2025-07-23 00:40:25','2025-07-23 00:40:25'),(3,'tres@gmail.com','4GqnZUlvMq0mG6Oo1vF1VjyGS40k1QsHtQ1AnN0QHvd2RFVAfJSgLEVDLFWOv5fU','2025-07-23 12:27:13','2025-07-23 10:27:13','2025-07-23 10:27:13'),(4,'neuquenrenault@gmail.com','G11SHcEjTLkDfnuc0Y6lxIEhzmhjoaJ6smFanCAzFpT1r2QufWji6WSRVzBuFSCv','2025-07-23 12:55:25','2025-07-23 10:55:25','2025-07-23 10:55:25');
/*!40000 ALTER TABLE `magic_links` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'0001_01_01_000000_create_users_table',1),(2,'0001_01_01_000001_create_cache_table',1),(3,'0001_01_01_000002_create_jobs_table',1),(4,'2025_05_14_013812_create_eventos_table',1),(5,'2025_05_14_013850_create_entradas_table',1),(6,'2025_05_20_011215_add_visible_to_entradas_table',1),(7,'2025_05_23_181350_add_mercadopago_fields_to_users_table',1),(8,'2025_05_24_022250_add_organizador_id_to_eventos_table',1),(9,'2025_05_25_223328_create_orders_table',1),(10,'2025_05_25_223341_create_purchased_tickets_table',1),(11,'2025_05_26_010914_add_items_data_to_orders_table',1),(12,'2025_06_04_013518_add_scan_date_to_purchased_tickets_table',1),(13,'2025_06_05_000402_alter_estado_column_in_eventos_table',1),(14,'2025_06_19_000854_add_email_sent_at_to_orders_table',1),(15,'2025_06_19_122837_create_permission_tables',1),(16,'2025_06_19_160920_add_buyer_name_and_ticket_type_to_purchased_tickets_table',1),(17,'2025_07_01_153602_add_age_and_dni_to_eventos_table',1),(18,'2025_07_01_171622_add_valido_todo_el_evento_to_entradas_table',1),(19,'2025_07_08_101505_add_short_code_to_purchased_tickets_table',1),(20,'2025_07_08_105056_modify_short_code_length_on_purchased_tickets',1),(21,'2025_07_14_130622_create_magic_links_table',1),(22,'2025_07_15_220118_add_slug_to_eventos_table',1);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `model_has_permissions`
--

DROP TABLE IF EXISTS `model_has_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `model_has_permissions` (
  `permission_id` bigint(20) unsigned NOT NULL,
  `model_type` varchar(255) NOT NULL,
  `model_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
  KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`),
  CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `model_has_permissions`
--

LOCK TABLES `model_has_permissions` WRITE;
/*!40000 ALTER TABLE `model_has_permissions` DISABLE KEYS */;
/*!40000 ALTER TABLE `model_has_permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `model_has_roles`
--

DROP TABLE IF EXISTS `model_has_roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `model_has_roles` (
  `role_id` bigint(20) unsigned NOT NULL,
  `model_type` varchar(255) NOT NULL,
  `model_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`),
  CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `model_has_roles`
--

LOCK TABLES `model_has_roles` WRITE;
/*!40000 ALTER TABLE `model_has_roles` DISABLE KEYS */;
INSERT INTO `model_has_roles` VALUES (1,'App\\Models\\User',1),(1,'App\\Models\\User',7),(1,'App\\Models\\User',8),(2,'App\\Models\\User',2),(3,'App\\Models\\User',3),(3,'App\\Models\\User',4),(3,'App\\Models\\User',5),(3,'App\\Models\\User',6),(3,'App\\Models\\User',9),(3,'App\\Models\\User',10);
/*!40000 ALTER TABLE `model_has_roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `orders`
--

DROP TABLE IF EXISTS `orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `orders` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `event_id` bigint(20) unsigned NOT NULL,
  `buyer_full_name` varchar(255) NOT NULL,
  `buyer_email` varchar(255) NOT NULL,
  `buyer_phone` varchar(255) DEFAULT NULL,
  `buyer_dni` varchar(255) DEFAULT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `items_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`items_data`)),
  `payment_status` varchar(255) NOT NULL DEFAULT 'pending',
  `mp_payment_id` varchar(255) DEFAULT NULL,
  `mp_preference_id` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `email_sent_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `orders_event_id_foreign` (`event_id`),
  CONSTRAINT `orders_event_id_foreign` FOREIGN KEY (`event_id`) REFERENCES `eventos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `orders`
--

LOCK TABLES `orders` WRITE;
/*!40000 ALTER TABLE `orders` DISABLE KEYS */;
INSERT INTO `orders` VALUES (10,1,'prueba1','neuquenrenault@gmail.com',NULL,NULL,1000.00,'[{\"entrada_id\":1,\"cantidad\":\"2\",\"precio_unitario\":\"500.00\"}]','approved','118994401931',NULL,'2025-07-22 15:42:38','2025-07-22 15:42:49',NULL),(11,3,'Test','test@test.com','112345678','Test',2500.00,'[{\"entrada_id\":3,\"cantidad\":\"1\",\"precio_unitario\":\"2500.00\"}]','pending',NULL,NULL,'2025-07-24 15:14:17','2025-07-24 15:14:17',NULL),(12,1,'prueba1','neuquenrenault@gmail.com',NULL,NULL,1000.00,'[{\"entrada_id\":1,\"cantidad\":\"2\",\"precio_unitario\":\"500.00\"}]','approved','119389892795',NULL,'2025-07-25 22:41:28','2025-07-25 22:41:49',NULL);
/*!40000 ALTER TABLE `orders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_reset_tokens`
--

LOCK TABLES `password_reset_tokens` WRITE;
/*!40000 ALTER TABLE `password_reset_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_reset_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `permissions`
--

DROP TABLE IF EXISTS `permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `permissions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `guard_name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `permissions`
--

LOCK TABLES `permissions` WRITE;
/*!40000 ALTER TABLE `permissions` DISABLE KEYS */;
/*!40000 ALTER TABLE `permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `purchased_tickets`
--

DROP TABLE IF EXISTS `purchased_tickets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `purchased_tickets` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` bigint(20) unsigned NOT NULL,
  `entrada_id` bigint(20) unsigned NOT NULL,
  `unique_code` char(36) NOT NULL,
  `short_code` varchar(5) DEFAULT NULL,
  `qr_path` varchar(255) DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'valid',
  `scan_date` timestamp NULL DEFAULT NULL,
  `scanned_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `buyer_name` varchar(255) DEFAULT NULL,
  `ticket_type` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `purchased_tickets_unique_code_unique` (`unique_code`),
  UNIQUE KEY `purchased_tickets_short_code_unique` (`short_code`),
  KEY `purchased_tickets_order_id_foreign` (`order_id`),
  KEY `purchased_tickets_entrada_id_foreign` (`entrada_id`),
  CONSTRAINT `purchased_tickets_entrada_id_foreign` FOREIGN KEY (`entrada_id`) REFERENCES `entradas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `purchased_tickets_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `purchased_tickets`
--

LOCK TABLES `purchased_tickets` WRITE;
/*!40000 ALTER TABLE `purchased_tickets` DISABLE KEYS */;
INSERT INTO `purchased_tickets` VALUES (11,10,1,'d488689a-3be0-4451-869a-65ce46173579','5B1EM','qrcodes/d488689a-3be0-4451-869a-65ce46173579.png','valid',NULL,NULL,'2025-07-22 15:42:49','2025-07-22 15:42:49','prueba1','Early Birds'),(12,10,1,'69b70341-826d-4e33-9f72-1b8691fbb23d','1LQYS','qrcodes/69b70341-826d-4e33-9f72-1b8691fbb23d.png','valid',NULL,NULL,'2025-07-22 15:42:49','2025-07-22 15:42:49','prueba1','Early Birds'),(13,12,1,'964497a9-b668-430e-baa3-63496ec41811','SLSCK','qrcodes/964497a9-b668-430e-baa3-63496ec41811.png','valid',NULL,NULL,'2025-07-25 22:41:49','2025-07-25 22:41:49','prueba1','Early Birds'),(14,12,1,'ef081a83-6173-4280-8d26-6b7122e1f5b8','LGQJC','qrcodes/ef081a83-6173-4280-8d26-6b7122e1f5b8.png','used','2025-07-25 22:43:53',NULL,'2025-07-25 22:41:49','2025-07-25 22:43:53','prueba1','Early Birds');
/*!40000 ALTER TABLE `purchased_tickets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `role_has_permissions`
--

DROP TABLE IF EXISTS `role_has_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `role_has_permissions` (
  `permission_id` bigint(20) unsigned NOT NULL,
  `role_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`role_id`),
  KEY `role_has_permissions_role_id_foreign` (`role_id`),
  CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `role_has_permissions`
--

LOCK TABLES `role_has_permissions` WRITE;
/*!40000 ALTER TABLE `role_has_permissions` DISABLE KEYS */;
/*!40000 ALTER TABLE `role_has_permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `roles` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `guard_name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `roles_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `roles`
--

LOCK TABLES `roles` WRITE;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
INSERT INTO `roles` VALUES (1,'productor','web','2025-07-22 02:34:34','2025-07-22 02:34:34'),(2,'admin','web','2025-07-22 13:39:01','2025-07-22 13:39:01'),(3,'cliente','web','2025-07-22 15:31:31','2025-07-22 15:31:31');
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sessions`
--

LOCK TABLES `sessions` WRITE;
/*!40000 ALTER TABLE `sessions` DISABLE KEYS */;
INSERT INTO `sessions` VALUES ('3s2rXPB3AWNrel4sb2darMnikM03W1KLpuPNrykl',NULL,'80.82.77.202','fasthttp','YTozOntzOjY6Il90b2tlbiI7czo0MDoidmc0SWlPTDY4Um9iZ0M2TUczMWRacjlURDluUEk0ZVVrMXlnWHlNZSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MjM6Imh0dHBzOi8vMjE2LjIzOC4xMjIuMTI0Ijt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1754335818),('6MzbnUwrfQewnzL8WlQAZOke9lOHegi4QnQXyrw0',NULL,'51.68.111.212','Mozilla/5.0 (compatible; MJ12bot/v2.0.2; http://mj12bot.com/)','YTozOntzOjY6Il90b2tlbiI7czo0MDoibmFySlZxckVwR3BiN2o0R0hKQkZVajhUNzQyNDdRTHhuTDFKZnREcCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MjU6Imh0dHBzOi8vdGlja2V0c3Byby5vbmxpbmUiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1754336708),('C9v8yiuG8Aj1r4cGPdLieBU85VwMAfaanajMmTAe',NULL,'205.210.31.54','Hello from Palo Alto Networks, find out more about our scans in https://docs-cortex.paloaltonetworks.com/r/1/Cortex-Xpanse/Scanning-activity','YTozOntzOjY6Il90b2tlbiI7czo0MDoiZUFoVGlMV2NPQ1NpaDl3cWpYdE9PSTlFZ0xZdDBjRmVrNHR5VUtPNCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mjk6Imh0dHBzOi8vd3d3LnRpY2tldHNwcm8ub25saW5lIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1754340087),('dbr0nPJTayIZbn5yZrupZlBOK2WC51AeBNLvyzoM',NULL,'34.122.133.51','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/74.0.3729.169 Safari/537.36','YToyOntzOjY6Il90b2tlbiI7czo0MDoib2U5TWdZM1BoN3plek9XenBZZlk3OHBaTDJpckpmY2J4TDZqQzZQcyI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1754343078),('pVjvIAZ55xHkbcWcF3ENTDjLV1K168nv9VSVx5F7',NULL,'51.68.111.203','Mozilla/5.0 (compatible; MJ12bot/v2.0.2; http://mj12bot.com/)','YTozOntzOjY6Il90b2tlbiI7czo0MDoiUzBaNFU0c2VMckx4U1REdHN6WmpBclpJOXpzTE5LUW9aak5YV3lQNyI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MjU6Imh0dHBzOi8vdGlja2V0c3Byby5vbmxpbmUiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1754342807),('RhPLMBYtHahvV8Zlcs8FofRLslg4lexvT7wpwIvH',NULL,'51.68.107.154','Mozilla/5.0 (compatible; MJ12bot/v2.0.2; http://mj12bot.com/)','YTozOntzOjY6Il90b2tlbiI7czo0MDoiRk9rWENnZUJQUm11RzZDTkpZcm1GMXI0bzNWM0lzaTRvUVlrQk9BNiI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MjU6Imh0dHBzOi8vdGlja2V0c3Byby5vbmxpbmUiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1754348113),('seFsMCvZCt7XX1DAuxIf4ZiSsrFcZBxH5Qtcs438',NULL,'121.29.51.28','Mozilla/5.0 (Linux; U; Android 7.1.1; zh-CN; MX6 Build/NMF26O) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/57.0.2987.108 UCBrowser/11.8.0.960 UWS/2.12.1.18 Mobile Safari/537.36 AliApp(TB/7.6.1.55) UCBS/2.11.1.1 WindVane/8.3.0 1080X1920','YTozOntzOjY6Il90b2tlbiI7czo0MDoiS0RWeWlqaDlSalROUzl3cGxOenZNSlB1RHQ4eE1hazJPQlpPZFl2WCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MjU6Imh0dHBzOi8vdGlja2V0c3Byby5vbmxpbmUiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1754346182),('sqSbP5gkCa36Ggpuj2uP5bhZxy6WjTgBznebquVN',NULL,'162.62.213.187','Mozilla/5.0 (iPhone; CPU iPhone OS 13_2_3 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/13.0.3 Mobile/15E148 Safari/604.1','YTozOntzOjY6Il90b2tlbiI7czo0MDoiaWRBc0RUMVgwUnkwSkg0Zk5LazRMeXduT1Q1RWR0WGJFdnFXZGlqRSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mjk6Imh0dHBzOi8vd3d3LnRpY2tldHNwcm8ub25saW5lIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1754340295),('V1wHxJ891jEtr11MbCrXbLhOrekHrq6IpoW3redw',NULL,'3.131.215.38','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) Chrome/126.0.0.0 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiRm1xdnU4Qkp4WFJTaWZ0WTNZSkRUTHp2ZGJXU1FMQlU5ZTBVckRNMSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MjM6Imh0dHBzOi8vMjE2LjIzOC4xMjIuMTI0Ijt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1754342369),('WwsES0kXMbCXc8lO9wdq5ANoOYpMNlMMkNlGiMor',NULL,'182.44.10.67','Mozilla/5.0 (iPhone; CPU iPhone OS 13_2_3 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/13.0.3 Mobile/15E148 Safari/604.1','YTozOntzOjY6Il90b2tlbiI7czo0MDoicmVLV0ttY3M1UjhEMFgzT2ZxS0VjaXdLblBiaktDRTJoZ0pLd3RUYiI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mjk6Imh0dHBzOi8vd3d3LnRpY2tldHNwcm8ub25saW5lIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1754340122),('YDdazMYXg5enPxQUF9kSj002ZPC0X2ZHzTjHgROp',NULL,'185.224.128.88','libwww-perl/6.78','YTozOntzOjY6Il90b2tlbiI7czo0MDoiVUl3MEo1Z25XM1lnVUFWVTZSa1RFZk5vS2ZqdGt3cFB0SVJ0TDlJRCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MjM6Imh0dHBzOi8vMjE2LjIzOC4xMjIuMTI0Ijt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1754341127),('YZNWmXXTlrZWbUhW1KFewXAYM8tbjqtQ3zw3Frzc',NULL,'43.135.142.37','Mozilla/5.0 (iPhone; CPU iPhone OS 13_2_3 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/13.0.3 Mobile/15E148 Safari/604.1','YTozOntzOjY6Il90b2tlbiI7czo0MDoidjRjVTd2VFRPQm9YZzlFb2VzeFVZT2E5ZEhGOXlpUFJTd1lKb2VCcyI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mjk6Imh0dHBzOi8vd3d3LnRpY2tldHNwcm8ub25saW5lIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1754341500);
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `mp_access_token` varchar(255) DEFAULT NULL,
  `mp_refresh_token` varchar(255) DEFAULT NULL,
  `mp_public_key` varchar(255) DEFAULT NULL,
  `mp_user_id` bigint(20) DEFAULT NULL,
  `mp_expires_in` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'test','test@mail.com',NULL,'$2y$12$GCND5CxHQASxRXUvOw0ihe8L3dR2Njjr4o4jfncQspgiS05UmHXVu',NULL,'APP_USR-2327064545199856-072210-f18ac3fba99ddd76bf5692943f8fae59-2375268028','TG-687fa47e75dd010001223e1b-2375268028',NULL,2375268028,'2026-01-18 14:47:26','2025-07-22 02:33:46','2025-07-22 14:47:26'),(2,'Super Admin','admin@ticketspro.online',NULL,'$2y$12$Pxb3eFpYfmTKP7NUXS5XHe.YGO1QK6Sv51NFYVRSuxsseqQP3zgYa',NULL,NULL,NULL,NULL,NULL,NULL,'2025-07-22 13:38:13','2025-07-22 13:38:13'),(8,'Eze','ezedecandido@gmail.com','2025-07-23 00:41:42','$2y$12$YSBxvqVNmEZN/y4d1wZfgO/J7fPIXhNAK7GpG/VaPBUQybODO3NAO','Zt9zva6YXxaMoYeIEAnzXnuhCjWMQxoxm8e3BVErJKzBKapLv9e5LAaGl2mI',NULL,NULL,NULL,NULL,NULL,'2025-07-23 00:40:25','2025-07-23 00:42:39'),(9,'aaatest','tres@gmail.com',NULL,'$2y$12$vM5Nn9FYwltR5zPgoWEFUOXCCPc7pwTAcK2ThH4bHF7OqcN4v0rnK',NULL,NULL,NULL,NULL,NULL,NULL,'2025-07-23 10:27:13','2025-07-23 10:27:13'),(10,'aaatest','neuquenrenault@gmail.com','2025-07-23 10:55:37','$2y$12$gmCFrRbInELQpeQkaUyGPeDkAzYYadAeJ23l5/dyWlcF3BiEOipXm','S2BzEhwkzOVr9RdadyvWnvnTdxN4Q3FBxM1I3u6Vs3IwX4uBEaOJi5PGqPIY',NULL,NULL,NULL,NULL,NULL,'2025-07-23 10:55:25','2025-07-23 10:55:51');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-08-04 22:57:52
