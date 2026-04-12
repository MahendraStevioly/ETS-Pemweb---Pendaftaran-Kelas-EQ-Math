-- Migration: Add OTP Columns to Users Table
-- EQ Math - Pendaftaran Kelas Matematika
-- Date: 2026-04-12

-- Backup dulu database sebelum menjalankan migration ini!

-- Add OTP-related columns to users table
ALTER TABLE `users`
ADD COLUMN `otp_code` VARCHAR(6) NULL COMMENT 'Kode OTP 6 digit' AFTER `no_wa`,
ADD COLUMN `otp_expires_at` DATETIME NULL COMMENT 'Waktu kedaluwarsa OTP (5 menit)' AFTER `otp_code`,
ADD COLUMN `otp_created_at` DATETIME NULL COMMENT 'Waktu pembuatan OTP' AFTER `otp_expires_at`,
ADD COLUMN `otp_attempts` INT DEFAULT 0 COMMENT 'Jumlah percobaan OTP yang salah' AFTER `otp_created_at`,
ADD COLUMN `otp_locked_until` DATETIME NULL COMMENT 'Waktu lock sementara jika terlalu banyak percobaan' AFTER `otp_attempts`;

-- Add index for faster OTP lookup
ALTER TABLE `users`
ADD INDEX `idx_otp_lookup` (`otp_code`, `otp_expires_at`);

-- Add composite index for email/phone + OTP validation
ALTER TABLE `users`
ADD INDEX `idx_user_otp` (`email`, `otp_code`, `otp_expires_at`);

-- Verify columns added
-- DESC users;
