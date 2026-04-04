-- MySQL Optimization for Faster Migrations (Development Only)
-- Run this in MySQL console or phpMyAdmin

-- Increase innodb buffer pool size (adjust based on your RAM)
SET GLOBAL innodb_buffer_pool_size = 536870912; -- 512MB

-- Disable sync to disk for faster writes (development only!)
SET GLOBAL innodb_flush_log_at_trx_commit = 2;
SET GLOBAL sync_binlog = 0;

-- Increase log file size
SET GLOBAL innodb_log_file_size = 268435456; -- 256MB

-- Optimize for bulk operations
SET GLOBAL innodb_write_io_threads = 8;
SET GLOBAL innodb_read_io_threads = 8;

-- Disable foreign key checks temporarily (for migrations)
SET FOREIGN_KEY_CHECKS = 0;

-- After migrations, re-enable:
-- SET FOREIGN_KEY_CHECKS = 1;
