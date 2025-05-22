USE dapur;

-- Add user_role column if not exists
ALTER TABLE users 
ADD COLUMN IF NOT EXISTS user_role ENUM('admin', 'user') DEFAULT 'user';

-- Set user with ID 1 as admin
UPDATE users SET user_role = 'admin' WHERE user_id = 1; 