CREATE DATABASE IF NOT EXISTS user_tracker_test
    DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

GRANT ALL PRIVILEGES ON user_tracker_test.* TO 'tracker_user'@'%';
