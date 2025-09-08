-- Create group_payments table to store payment receipts per group
CREATE TABLE `group_payments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `group_id` int(11) NOT NULL,
  `payment_type` enum('research_forum','pre_oral_defense','final_defense') NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `image_receipts` TEXT DEFAULT NULL COMMENT 'JSON array of image file paths',
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `uploaded_by` int(11) NOT NULL COMMENT 'Student ID who uploaded',
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_group_payment` (`group_id`, `payment_type`),
  KEY `group_id` (`group_id`),
  KEY `uploaded_by` (`uploaded_by`),
  CONSTRAINT `group_payments_ibfk_1` FOREIGN KEY (`group_id`) REFERENCES `groups` (`id`) ON DELETE CASCADE,
  CONSTRAINT `group_payments_ibfk_2` FOREIGN KEY (`uploaded_by`) REFERENCES `user_tbl` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Add image_receipts column to existing payments table if not exists
ALTER TABLE `payments` 
ADD COLUMN IF NOT EXISTS `image_receipts` TEXT DEFAULT NULL COMMENT 'JSON array of image file paths';