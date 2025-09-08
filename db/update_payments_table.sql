-- Update payments table to support multiple image attachments
ALTER TABLE `payments` 
ADD COLUMN `image_receipts` TEXT DEFAULT NULL COMMENT 'JSON array of image file paths',
MODIFY COLUMN `pdf_receipt` VARCHAR(255) DEFAULT NULL COMMENT 'Legacy PDF receipt field';

-- Create payment_attachments table for better structure
CREATE TABLE `payment_attachments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `payment_id` int(11) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_type` varchar(50) NOT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `payment_id` (`payment_id`),
  CONSTRAINT `payment_attachments_ibfk_1` FOREIGN KEY (`payment_id`) REFERENCES `payments` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;