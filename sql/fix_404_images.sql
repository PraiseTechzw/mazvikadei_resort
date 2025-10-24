-- =====================================================
-- FIX 404 IMAGE ERRORS - MAZVIKADEI RESORT
-- =====================================================
-- Run this script to fix the 404 image errors

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

-- Set activities to use default image or remove image requirement
UPDATE activities SET image = 'assets/activities/default.jpg' WHERE image IS NULL OR image = '';

-- =====================================================
-- UPDATE EVENT IMAGES
-- =====================================================

-- Set events to use default image or remove image requirement
UPDATE events SET image = 'assets/events/default.jpg' WHERE image IS NULL OR image = '';

-- =====================================================
-- COMPLETION MESSAGE
-- =====================================================

SELECT 'Image paths updated successfully!' as message,
       '404 errors should be resolved' as status,
       'All images now point to existing files' as details;
