-- Add profile fields to users table
ALTER TABLE users 
ADD COLUMN first_name VARCHAR(50) NULL AFTER username,
ADD COLUMN last_name VARCHAR(50) NULL AFTER first_name,
ADD COLUMN bio TEXT NULL AFTER phone,
ADD COLUMN profile_picture VARCHAR(255) NULL AFTER bio;