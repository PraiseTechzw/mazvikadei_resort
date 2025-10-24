-- =====================================================
-- FIX IMAGE PATHS - USE EXISTING IMAGES
-- =====================================================
-- This script updates the database to use the correct existing images

USE mazvikadei;

-- =====================================================
-- UPDATE ROOM IMAGES WITH EXISTING FILES
-- =====================================================

-- Update existing rooms with correct image paths that actually exist
UPDATE rooms SET image = 'assets/rooms/ocean.jpg' WHERE id = 1;
UPDATE rooms SET image = 'assets/rooms/garden.jpg' WHERE id = 2;
UPDATE rooms SET image = 'assets/rooms/villa.jpg' WHERE id = 3;
UPDATE rooms SET image = 'assets/rooms/mountain.jpg' WHERE id = 4;
UPDATE rooms SET image = 'assets/rooms/standard.jpg' WHERE id = 5;

-- =====================================================
-- UPDATE ACTIVITY IMAGES
-- =====================================================

-- Set all activities to use a default image or remove image requirement
UPDATE activities SET image = NULL WHERE image IS NULL OR image = '';

-- =====================================================
-- UPDATE EVENT IMAGES
-- =====================================================

-- Set all events to use a default image or remove image requirement
UPDATE events SET image = NULL WHERE image IS NULL OR image = '';

-- =====================================================
-- COMPLETION MESSAGE
-- =====================================================

SELECT 'Image paths updated to use existing images!' as message,
       '404 errors should be resolved' as status;
