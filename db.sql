DROP TABLE IF EXISTS `t_products`;
CREATE TABLE `t_products` (
  `id` int(9) unsigned NOT NULL AUTO_INCREMENT,
  `customerId` int(10) NOT NULL,
  `userId` int(10) NOT NULL,
  `productName` char(50) DEFAULT NULL,
  PRIMARY KEY (`id`)
) AUTO_INCREMENT=1;

INSERT INTO `t_products` VALUES (1,1,1,'product1'),(2,1,1,'product2'),(3,1,1,'product3'),(4,1,2,'product4'),(5,1,2,'product5');

DROP TABLE IF EXISTS `t_users`;
CREATE TABLE `t_users` (
  `id` int(9) unsigned NOT NULL AUTO_INCREMENT,
  `role` varchar(10) DEFAULT 'user',
  `email` varchar(100) DEFAULT NULL,
  `active` tinyint(1) DEFAULT '0',
  `firstName` char(10) NOT NULL,
  `lastName` char(10) DEFAULT NULL,
  `password` text NOT NULL,
  `createdAt` datetime DEFAULT NULL,
  `updatedAt` datetime DEFAULT NULL,
  `expires` datetime DEFAULT NULL,
  `loginCount` int(10) DEFAULT '0',
  `lastlogindate` datetime DEFAULT NULL,
  `lastloginip` char(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `login` (`email`)
) AUTO_INCREMENT=1;

INSERT INTO `t_users` VALUES (1,'admin','admin@admin.com',0,'John','Doe','743139240ff612253817440d98acb2ce7939fbb4','2015-10-28 19:34:29','2015-11-09 02:30:50','2016-10-28 19:34:29',1,'2015-11-09 02:19:55','127.0.0.1'),(2,'user','user2',0,'Mike',NULL,'743139240ff612253817440d98acb2ce7939fbb4','2015-10-28 19:34:29','2015-10-28 19:34:29','2016-10-28 19:34:29',2,NULL,NULL),(3,'user','user3',1,'Pete','D','743139240ff612253817440d98acb2ce7939fbb4','2015-10-28 19:34:29','2015-10-28 19:34:29','2016-10-28 19:34:29',3,NULL,NULL);
