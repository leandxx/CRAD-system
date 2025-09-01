-- Modify payments table to support 3 payment types with PDF attachments
ALTER TABLE `payments` 
ADD COLUMN `payment_type` ENUM('research_forum', 'pre_oral_defense', 'final_defense') NOT NULL AFTER `student_id`,
ADD COLUMN `pdf_receipt` VARCHAR(255) NULL AFTER `amount`,
ADD COLUMN `admin_approved` TINYINT(1) DEFAULT 0 AFTER `status`,
MODIFY COLUMN `status` ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending';

-- Create payment_receipts directory structure
-- This will be handled by PHP code when uploading files