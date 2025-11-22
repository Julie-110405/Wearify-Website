-- Wearify Database Schema
-- Run this SQL in phpMyAdmin to create the necessary tables for outfits functionality

-- Table: outfits
-- Stores saved outfits for each user
CREATE TABLE IF NOT EXISTS `outfits` (
  `outfit_id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) NOT NULL,
  `created_at` DATETIME NOT NULL,
  PRIMARY KEY (`outfit_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `outfits_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table: outfit_items
-- Junction table linking outfits to items (many-to-many relationship)
CREATE TABLE IF NOT EXISTS `outfit_items` (
  `outfit_item_id` INT(11) NOT NULL AUTO_INCREMENT,
  `outfit_id` INT(11) NOT NULL,
  `item_id` INT(11) NOT NULL,
  PRIMARY KEY (`outfit_item_id`),
  KEY `outfit_id` (`outfit_id`),
  KEY `item_id` (`item_id`),
  UNIQUE KEY `unique_outfit_item` (`outfit_id`, `item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

