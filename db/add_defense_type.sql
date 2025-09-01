-- Add defense_type column to defense_schedules table
-- Run this SQL script in your MySQL database

USE crad_system;

-- Add defense_type column if it doesn't exist
ALTER TABLE defense_schedules 
ADD COLUMN defense_type ENUM('pre_oral', 'final') DEFAULT 'pre_oral' AFTER status;

-- Update existing records to have pre_oral as default
UPDATE defense_schedules 
SET defense_type = 'pre_oral' 
WHERE defense_type IS NULL;

-- Show the updated table structure
DESCRIBE defense_schedules;