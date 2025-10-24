-- =====================================================
-- FIX MISSING IMAGES - MAZVIKADEI RESORT
-- =====================================================
-- This script fixes the 404 image errors by updating image paths

USE mazvikadei;

-- =====================================================
-- UPDATE ROOM IMAGES WITH EXISTING FILES
-- =====================================================

-- Update existing rooms with correct image paths
UPDATE rooms SET image = 'assets/rooms/ocean.jpg' WHERE id = 1;
UPDATE rooms SET image = 'assets/rooms/garden.jpg' WHERE id = 2;
UPDATE rooms SET image = 'assets/rooms/villa.jpg' WHERE id = 3;

-- Update new rooms with placeholder images (will create these)
UPDATE rooms SET image = 'assets/rooms/mountain.jpg' WHERE id = 4;
UPDATE rooms SET image = 'assets/rooms/standard.jpg' WHERE id = 5;

-- =====================================================
-- UPDATE ACTIVITY IMAGES WITH PLACEHOLDER
-- =====================================================

-- Update activities with default activity image
UPDATE activities SET image = 'assets/activities/default.jpg' WHERE image IS NULL OR image = '';

-- =====================================================
-- UPDATE EVENT IMAGES WITH PLACEHOLDER
-- =====================================================

-- Update events with default event image
UPDATE events SET image = 'assets/events/default.jpg' WHERE image IS NULL OR image = '';

-- =====================================================
-- COMPLETION MESSAGE
-- =====================================================

SELECT 'Image paths updated successfully!' as message,
       '404 errors should be resolved' as status;
