-- Update payments table to support multiple image attachments
ALTER TABLE `payments` 
ADD COLUMN `image_receipts` TEXT DEFAULT NULL COMMENT 'JSON array of image file paths',
MODIFY COLUMN `pdf_receipt` VARCHAR(255) DEFAULT NULL COMMENT 'Legacy PDF receipt field';