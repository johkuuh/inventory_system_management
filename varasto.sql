-- MySQL dump 10.13  Distrib 8.0.40, for Win64 (x86_64)
--
-- Host: 127.0.0.1    Database: varasto
-- ------------------------------------------------------
-- Server version	8.0.40

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `categories`
--

DROP TABLE IF EXISTS `categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `categories` (
  `category_id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `code_prefix` varchar(5) NOT NULL,
  `code_start` int NOT NULL DEFAULT '1001',
  PRIMARY KEY (`category_id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `categories`
--

LOCK TABLES `categories` WRITE;
/*!40000 ALTER TABLE `categories` DISABLE KEYS */;
INSERT INTO `categories` VALUES (1,'Päivittäistavarat','PT',1001),(2,'Elektroniikka','EL',1001),(3,'Koti ja keittiö','KK',1001),(4,'Rakentaminen ja työkalut','RT',1001),(5,'Vaatteet ja tekstiilit','VT',1001),(6,'Urheilu ja vapaa-aika','UV',1001),(7,'Retkeily ja ulkoilu','RE',1001),(8,'Puutarha','PU',1001),(11,'Kosmetiikka','KO',1001);
/*!40000 ALTER TABLE `categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `locations`
--

DROP TABLE IF EXISTS `locations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `locations` (
  `location_id` int NOT NULL AUTO_INCREMENT,
  `code` varchar(50) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`location_id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `locations`
--

LOCK TABLES `locations` WRITE;
/*!40000 ALTER TABLE `locations` DISABLE KEYS */;
INSERT INTO `locations` VALUES (1,'APU','Aputerminaali',1),(2,'LAHTO','Lähtöalue',1),(3,'HALLI2','Halli 2',1),(4,'HALLI1','Halli 1',1),(5,'PALLOKKI','Pallokki',1),(6,'RAK_VAR','Rakentaminen ja työkalut varasto',0),(8,'VAATE_VAR','Vaatteet ja tekstiilit varasto',0),(10,'URH_VAR','Urheilu ja vapaa-aika varasto',0),(11,'RETKI_VAR','Retkeily ja ulkoilu varasto',0),(12,'PUUT_VAR','Puutarha varasto',0),(13,'LAHET','Lähetysalue',0),(14,'PAKKANEN','Pakkanen -20',0);
/*!40000 ALTER TABLE `locations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `products`
--

DROP TABLE IF EXISTS `products`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `products` (
  `product_id` int NOT NULL AUTO_INCREMENT,
  `sku` varchar(50) DEFAULT NULL,
  `name` varchar(150) NOT NULL,
  `category_id` int DEFAULT NULL,
  `unit` varchar(20) DEFAULT NULL,
  `active` tinyint(1) DEFAULT '1',
  `reorder_point` int DEFAULT NULL,
  PRIMARY KEY (`product_id`),
  UNIQUE KEY `sku` (`sku`),
  KEY `category_id` (`category_id`),
  CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`)
) ENGINE=InnoDB AUTO_INCREMENT=33 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `products`
--

LOCK TABLES `products` WRITE;
/*!40000 ALTER TABLE `products` DISABLE KEYS */;
INSERT INTO `products` VALUES (1,'PT1001','Kahvipaketti 500g',1,'kpl',1,50),(2,'PT1002','Pasta 1kg',1,'kpl',0,60),(3,'PT1003','Riisi 1kg',1,'kpl',0,40),(4,'EL2001','Bluetooth kuulokkeet',2,'kpl',0,20),(5,'EL2002','USB-C laturi 30W',2,'kpl',1,25),(6,'EL2003','HDMI kaapeli 2m',2,'kpl',1,30),(7,'KK3001','Kahvinkeitin',3,'kpl',1,10),(8,'KK3002','Vedenkeitin',3,'kpl',1,12),(9,'KK3003','Paistinpannu 28cm',3,'kpl',1,15),(10,'RT4001','Akkuporakone 18V',4,'kpl',0,10),(11,'RT4002','Ruuvimeisselisarja',4,'kpl',1,15),(12,'RT4003','Vasara',4,'kpl',1,20),(13,'VT5001','T-paita musta',5,'kpl',1,30),(14,'VT5002','Työhanskat',5,'pari',1,40),(15,'VT5003','Sukat 5-pack',5,'pkt',1,35),(16,'UV6001','Jalkapallo',6,'kpl',1,20),(17,'UV6002','Juomapullo 0.7L',6,'kpl',0,25),(18,'RE7001','Teltta 2 hengelle',7,'kpl',1,8),(19,'RE7002','Retkikeitin',7,'kpl',1,10),(20,'PU8001','Kukkamulta 40L',8,'säkki',1,20),(21,'PU8002','Puutarhalapio',8,'kpl',1,12),(24,'EL2004','HP kannettava tietokone G10',2,'kpl',1,5),(25,'PT1004','Pasta Pirkka 1kg',1,'kpl',1,5),(26,'RT4004','Akkuporakone 18V',4,'kpl',1,10),(27,'RE7003','Onkivapa',7,'kpl',1,10),(28,'RT4005','A-tikkaat 4 askelmaa',4,'kpl',0,3),(29,'RT4006','A-tikkaat 4 askelmaa',4,'kpl',0,3),(30,'RT4007','A-tikkaat 4 askelmaa',4,'kpl',1,3),(31,'EL2005','Bluethooth kuulokkeet',2,'kpl',1,5),(32,'EL2006','Apple iPhone 16',2,'kpl',1,10);
/*!40000 ALTER TABLE `products` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stock_balances`
--

DROP TABLE IF EXISTS `stock_balances`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `stock_balances` (
  `product_id` int NOT NULL,
  `location_id` int NOT NULL,
  `quantity` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`product_id`,`location_id`),
  UNIQUE KEY `uq_product_location` (`product_id`,`location_id`),
  KEY `location_id` (`location_id`),
  CONSTRAINT `stock_balances_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`),
  CONSTRAINT `stock_balances_ibfk_2` FOREIGN KEY (`location_id`) REFERENCES `locations` (`location_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `stock_balances`
--

LOCK TABLES `stock_balances` WRITE;
/*!40000 ALTER TABLE `stock_balances` DISABLE KEYS */;
INSERT INTO `stock_balances` VALUES (1,4,180),(2,4,220),(3,4,160),(4,2,75),(4,3,1),(5,2,120),(5,3,20),(6,2,90),(7,4,35),(8,4,40),(9,4,48),(9,5,21),(10,3,28),(11,1,10),(11,3,42),(12,3,60),(13,1,50),(14,1,25),(14,3,10),(14,4,10),(17,4,0),(17,5,2),(18,1,15),(20,3,54),(21,3,32),(24,2,15),(25,4,10),(25,5,5),(26,3,12),(28,3,5),(30,4,2),(32,5,20);
/*!40000 ALTER TABLE `stock_balances` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stock_movements`
--

DROP TABLE IF EXISTS `stock_movements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `stock_movements` (
  `movement_id` int NOT NULL AUTO_INCREMENT,
  `movement_type` enum('IN','OUT','TRANSFER','ADJUST') NOT NULL,
  `created_by` int DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `note` text,
  PRIMARY KEY (`movement_id`),
  KEY `created_by` (`created_by`),
  CONSTRAINT `stock_movements_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=37 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `stock_movements`
--

LOCK TABLES `stock_movements` WRITE;
/*!40000 ALTER TABLE `stock_movements` DISABLE KEYS */;
INSERT INTO `stock_movements` VALUES (1,'IN',1,'2026-03-04 03:42:03','Tavaraa vastaanotettu toimittajalta'),(2,'TRANSFER',3,'2026-03-04 03:42:03','Siirto vastaanotosta varastoon'),(3,'TRANSFER',4,'2026-03-04 03:42:03','Täydennys keräilyalueelle'),(4,'OUT',5,'2026-03-04 03:42:03','Myymälälähetys'),(5,'ADJUST',2,'2026-03-04 03:42:03','Inventointikorjaus'),(6,'TRANSFER',1,'2026-03-05 00:00:28','siirto k & k keräilyyn'),(7,'IN',1,'2026-03-05 00:09:22',''),(8,'OUT',1,'2026-03-05 00:09:42',''),(9,'TRANSFER',1,'2026-03-05 00:10:22','siirto keräilyyn'),(10,'ADJUST',1,'2026-03-05 00:11:08','asiakas muutti tilausta'),(11,'IN',1,'2026-03-05 00:19:46','varastointi'),(12,'IN',2,'2026-03-09 19:06:45','toimitettu erä työhankoja, siirretään varastoon'),(13,'IN',1,'2026-03-27 16:39:36','varastointi'),(14,'IN',1,'2026-03-27 17:02:03','tilattua tuotetta lisätty varastoon'),(15,'ADJUST',1,'2026-03-27 17:03:09','korjattu tuotteen määrä (tilattu 10 kpl)'),(16,'IN',1,'2026-03-27 17:05:03','tilattu lisää pastaa, siirto keräilyalueelle'),(17,'IN',1,'2026-03-27 17:09:32','tilattu tuotetta lisää, siirretty varastoalueelle'),(18,'OUT',1,'2026-03-27 17:18:03','siirto toiseen varastoon'),(19,'ADJUST',1,'2026-03-27 17:22:29','kirjauksen korjaus'),(20,'OUT',1,'2026-03-27 17:23:11','lähetys asiakkaalle'),(21,'TRANSFER',1,'2026-03-27 17:24:21','lähetys toiseen yksikköön'),(22,'OUT',1,'2026-03-27 17:54:09','lähetys asiakkaalle'),(23,'IN',1,'2026-03-27 17:54:37','siirto varastoon'),(24,'ADJUST',1,'2026-03-27 17:54:58','korjattu tuotteen määrää'),(25,'IN',1,'2026-03-27 17:56:14','varastoon tuotetta'),(26,'IN',3,'2026-04-07 15:03:33','vastaanotettu saapunut erä tuotetta'),(27,'OUT',3,'2026-04-07 15:04:17','Lähetys asiakkaalle'),(28,'OUT',1,'2026-04-08 02:25:21','lähetys asiakkaalle'),(29,'IN',12,'2026-04-08 13:59:29','vastaanotettu saapunut erä tuotetta'),(30,'IN',3,'2026-04-08 14:36:40','vastaanotettu saapunut erä tuotetta'),(31,'IN',3,'2026-04-08 14:37:40','vastaanotettu saapunut erä tuotetta'),(32,'IN',12,'2026-04-08 18:31:51','siirretään pallokkiin asiakasnoutoa varten'),(33,'IN',3,'2026-04-08 20:51:24','Vastaanotettu tavara'),(34,'IN',3,'2026-04-08 21:07:43','Saapunut erä tuotetta'),(35,'IN',3,'2026-04-08 21:08:19','Saapunut lisää telttoja'),(36,'IN',3,'2026-04-08 21:08:42','-');
/*!40000 ALTER TABLE `stock_movements` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stock_movements_rows`
--

DROP TABLE IF EXISTS `stock_movements_rows`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `stock_movements_rows` (
  `row_id` int NOT NULL AUTO_INCREMENT,
  `movement_id` int DEFAULT NULL,
  `product_id` int DEFAULT NULL,
  `quantity` int NOT NULL,
  `from_location_id` int DEFAULT NULL,
  `to_location_id` int DEFAULT NULL,
  PRIMARY KEY (`row_id`),
  KEY `movement_id` (`movement_id`),
  KEY `product_id` (`product_id`),
  KEY `from_location_id` (`from_location_id`),
  KEY `to_location_id` (`to_location_id`),
  CONSTRAINT `stock_movements_rows_ibfk_1` FOREIGN KEY (`movement_id`) REFERENCES `stock_movements` (`movement_id`),
  CONSTRAINT `stock_movements_rows_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`),
  CONSTRAINT `stock_movements_rows_ibfk_3` FOREIGN KEY (`from_location_id`) REFERENCES `locations` (`location_id`),
  CONSTRAINT `stock_movements_rows_ibfk_4` FOREIGN KEY (`to_location_id`) REFERENCES `locations` (`location_id`)
) ENGINE=InnoDB AUTO_INCREMENT=45 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `stock_movements_rows`
--

LOCK TABLES `stock_movements_rows` WRITE;
/*!40000 ALTER TABLE `stock_movements_rows` DISABLE KEYS */;
INSERT INTO `stock_movements_rows` VALUES (7,1,5,30,NULL,1),(8,1,1,100,NULL,1),(9,2,5,30,1,2),(10,3,5,10,2,3),(11,4,5,6,3,13),(12,5,4,2,NULL,2),(14,6,9,6,4,5),(15,7,4,1,NULL,3),(16,8,9,1,4,NULL),(17,9,17,5,4,5),(18,10,17,3,NULL,5),(19,11,20,6,NULL,12),(20,12,14,10,NULL,6),(21,13,26,20,NULL,6),(22,14,24,5,NULL,2),(23,15,24,10,NULL,2),(24,16,25,5,NULL,5),(25,17,25,10,NULL,4),(26,18,16,10,10,NULL),(27,19,17,10,NULL,4),(28,20,17,5,4,NULL),(29,21,13,50,8,1),(30,22,6,50,2,NULL),(31,23,26,2,NULL,6),(32,24,26,1,6,NULL),(33,25,27,5,NULL,11),(34,26,4,10,NULL,2),(35,27,26,5,6,NULL),(36,28,26,16,6,NULL),(37,29,28,5,NULL,6),(38,30,26,10,NULL,6),(39,31,26,2,NULL,6),(40,32,32,20,NULL,5),(41,33,30,2,NULL,4),(42,34,18,5,NULL,1),(43,35,18,10,NULL,1),(44,36,14,10,NULL,4);
/*!40000 ALTER TABLE `stock_movements_rows` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `user_id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('ADMIN','VARASTO') NOT NULL,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'Admin Yksi','admin1@jtvarasto.fi','admin','ADMIN'),(2,'Admin Kaksi','admin2@jtvarasto.fi','admin','ADMIN'),(3,'Matti Meikäläinen','matti@jtvarasto.fi','123matti','VARASTO'),(4,'Liisa Laatikko','liisa@jtvarasto.fi','qwerty12','VARASTO'),(5,'Pekka Pakkaaja','pekka@jtvarasto.fi','pepa87','VARASTO'),(6,'Kalle Keräilijä','kalle@jtvarasto.fi','keke2','VARASTO'),(7,'Sari Siirtäjä','sari@jtvarasto.fi','sariS111','VARASTO'),(8,'Jari Järjestäjä','jari@jtvarasto.fi','jj20jj','VARASTO'),(9,'Tiina Toimittaja','tiina@jtvarasto.fi','123tito456','VARASTO'),(10,'Olli Operoija','olli@jtvarasto.fi','ooppe56','VARASTO'),(11,'Mikko Mallikas','mm@jtvarasto.fi','$2y$10$SaSiY4z1QRfCExDmZxklkOjsXCZvSAiIgCnm0fXRlNL6RmfCKQa4i','VARASTO'),(12,'Johanna Tikko','jt@jtvarasto.fi','johki','ADMIN');
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

-- Dump completed on 2026-04-09 21:48:19
