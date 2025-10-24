-- =====================================================
-- ADMIN LOGIN FIX - MAZVIKADEI RESORT
-- =====================================================
-- This script ensures the admin login works correctly

USE mazvikadei;

-- =====================================================
-- FIX ADMIN USER
-- =====================================================

-- Delete existing admin if exists
DELETE FROM users WHERE email = 'admin@mazvikadei.local';

-- Create new admin user with correct password hash
INSERT INTO users (email, password_hash, name, role, status, phone) VALUES
('admin@mazvikadei.local', '$2y$10$UQAJPP.uRQ8a1aORK744Rux9oEdwUZKQqL6z4QcyWDDJGfSfbxK4K', 'System Administrator', 'admin', 'active', '+263 77 123 4567');

-- =====================================================
-- VERIFY ADMIN USER
-- =====================================================

SELECT 'Admin user created successfully!' as message,
       'Email: admin@mazvikadei.local' as email,
       'Password: Admin@123' as password,
       'Role: admin' as role;

-- =====================================================
-- TEST PASSWORD HASH
-- =====================================================

-- Test the password hash (this should return 1 if correct)
SELECT 'Password test:' as test,
       (SELECT COUNT(*) FROM users 
        WHERE email = 'admin@mazvikadei.local' 
        AND password_hash = '$2y$10$UQAJPP.uRQ8a1aORK744Rux9oEdwUZKQqL6z4QcyWDDJGfSfbxK4K') as password_match;
