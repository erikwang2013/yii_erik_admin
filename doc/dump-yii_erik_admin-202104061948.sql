-- MySQL dump 10.13  Distrib 8.0.20, for Linux (x86_64)
--
-- Host: localhost    Database: yii_erik_admin
-- ------------------------------------------------------
-- Server version	8.0.20

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `erik_admin`
--

DROP TABLE IF EXISTS `erik_admin`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `erik_admin` (
  `id` bigint NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL COMMENT '用户名',
  `hash` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '校验hash',
  `password` varchar(100) COLLATE utf8mb4_general_ci NOT NULL COMMENT '密码',
  `status` tinyint NOT NULL DEFAULT '1' COMMENT '0=开启 1=禁止',
  `access_token` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='用户';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `erik_admin`
--

LOCK TABLES `erik_admin` WRITE;
/*!40000 ALTER TABLE `erik_admin` DISABLE KEYS */;
INSERT INTO `erik_admin` VALUES (6770338174544642049,'诸葛亮','$2y$13$Rd7Hv.j5VB6GRk53NE7eZuccH814/Lsr78R3yXrz6iSfMXjtLi2r6','123133w',0,NULL),(6770339464288927745,'哪吒','$2y$13$jxBPD2wwlGLY6phf5TG/NevGa/btpTvdNdzxTWBoTldTRquJp2wmm','123123',1,NULL),(6770375661681901569,'张胜男q','$2y$13$R31jEJXK.0ERB.SXGBGsh.mEAtM6CYYL91qdP1nP/fFTTq9/ehV1S','123456',0,NULL),(6772517604436213761,'erik','$2y$13$6VSI9aErAf86Ei.tnfcPWOaVLC3ED8PvC9x.kIYyb3d/ZnMT444.S','123456',1,NULL),(6785151491628859393,'路飞了','$2y$13$YVWcdb0P/.rt8G1VujqdhuEKzoaGKoTGuKoPZqSmlTUVH1zj353IS','123123',1,NULL);
/*!40000 ALTER TABLE `erik_admin` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `erik_admin_authority`
--

DROP TABLE IF EXISTS `erik_admin_authority`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `erik_admin_authority` (
  `id` bigint NOT NULL,
  `parent_id` bigint NOT NULL DEFAULT '0' COMMENT '父级  0=顶级',
  `code` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT '编码',
  `name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL COMMENT '名称',
  `show` tinyint NOT NULL DEFAULT '0' COMMENT '是否显示 0=显示 1=隐藏',
  `status` tinyint NOT NULL DEFAULT '0' COMMENT '状态 0=开启 1=禁止',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='权限表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `erik_admin_authority`
--

LOCK TABLES `erik_admin_authority` WRITE;
/*!40000 ALTER TABLE `erik_admin_authority` DISABLE KEYS */;
INSERT INTO `erik_admin_authority` VALUES (6778326059675811841,0,'order_list','订单列表',1,0),(6778326700091506689,0,'sdasd','用户列表',0,1),(6783347249670782977,6778326700091506689,'user_add','新增用户',1,1);
/*!40000 ALTER TABLE `erik_admin_authority` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `erik_admin_info`
--

DROP TABLE IF EXISTS `erik_admin_info`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `erik_admin_info` (
  `id` bigint NOT NULL,
  `real_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '姓名',
  `sex` tinyint NOT NULL DEFAULT '1' COMMENT '性别 0=女 1=男',
  `phone` char(11) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '手机号',
  `email` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '邮箱',
  `img` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '头像',
  `create_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='用户详情';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `erik_admin_info`
--

LOCK TABLES `erik_admin_info` WRITE;
/*!40000 ALTER TABLE `erik_admin_info` DISABLE KEYS */;
INSERT INTO `erik_admin_info` VALUES (6770338174544642049,'',1,'','','','2021-02-24 13:46:48','2021-02-24 13:46:48'),(6770339464288927745,'',1,'','','','2021-02-24 13:51:55','2021-02-24 13:51:55'),(6770375661681901569,'朱湖',1,'','','','2021-02-24 16:15:45','2021-02-24 16:15:45'),(6772517604436213761,'李飞',1,'','','','2021-03-02 14:07:04','2021-03-02 14:07:04'),(6785151491628859393,'',1,'','','','2021-04-06 10:49:38','2021-04-06 10:49:38');
/*!40000 ALTER TABLE `erik_admin_info` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `erik_admin_role`
--

DROP TABLE IF EXISTS `erik_admin_role`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `erik_admin_role` (
  `admin_id` bigint NOT NULL COMMENT '用户id',
  `role_id` bigint NOT NULL COMMENT '角色id'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='用户角色关系';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `erik_admin_role`
--

LOCK TABLES `erik_admin_role` WRITE;
/*!40000 ALTER TABLE `erik_admin_role` DISABLE KEYS */;
INSERT INTO `erik_admin_role` VALUES (6785151491628859393,4),(6785151491628859393,5),(6785151491628859393,6);
/*!40000 ALTER TABLE `erik_admin_role` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `erik_admin_role_authority`
--

DROP TABLE IF EXISTS `erik_admin_role_authority`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `erik_admin_role_authority` (
  `role_id` bigint NOT NULL COMMENT '角色id',
  `authority_id` bigint NOT NULL COMMENT '权限id'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='角色权限关系表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `erik_admin_role_authority`
--

LOCK TABLES `erik_admin_role_authority` WRITE;
/*!40000 ALTER TABLE `erik_admin_role_authority` DISABLE KEYS */;
INSERT INTO `erik_admin_role_authority` VALUES (6785130231465246721,4),(6785130231465246721,5),(6785130231465246721,6);
/*!40000 ALTER TABLE `erik_admin_role_authority` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `erik_admin_role_info`
--

DROP TABLE IF EXISTS `erik_admin_role_info`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `erik_admin_role_info` (
  `id` bigint NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL COMMENT '角色名称',
  `status` tinyint NOT NULL DEFAULT '1' COMMENT '角色状态 0=开启 1=禁止',
  `create_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='角色';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `erik_admin_role_info`
--

LOCK TABLES `erik_admin_role_info` WRITE;
/*!40000 ALTER TABLE `erik_admin_role_info` DISABLE KEYS */;
INSERT INTO `erik_admin_role_info` VALUES (6783291724690096129,'经理',0,'2021-04-01 07:39:34'),(6785130231465246721,'业务员',1,'2021-04-06 09:25:08');
/*!40000 ALTER TABLE `erik_admin_role_info` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping routines for database 'yii_erik_admin'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2021-04-06 19:48:47
