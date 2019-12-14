--
-- Current Database: `simpletodo`
--

CREATE DATABASE IF NOT EXISTS `simpletodo`;

USE `simpletodo`;

--
-- Table structure for table `todo_items`
--

DROP TABLE IF EXISTS `todo_items`;
CREATE TABLE `todo_items` (
  `todo_id` int(11) NOT NULL AUTO_INCREMENT,
  `item_text` varchar(255) NOT NULL,
  `is_complete` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`todo_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `todo_items`
--

INSERT INTO `todo_items` VALUES (1,'Test Item 1',0),(2,'Test Item 2',1),(3,'Test Item 3',0);