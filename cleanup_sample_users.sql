-- SQL commands to remove sample/test users from the database
-- Keep only real users like Adam Hymus

-- First, let's see all users to identify which ones to remove
-- SELECT id, first_name, last_name, email, role FROM users;

-- Remove sample users (adjust IDs based on your database)
-- Keep Adam Hymus (ID: 1) and remove the sample users

DELETE FROM users WHERE email IN (
    'alice.walker@example.com',
    'ben.carter@example.com', 
    'chloe.reed@example.com',
    'daniel.brown@example.com',
    'ella.green@example.com'
);

-- Alternative: Remove by ID if you know the specific IDs
-- DELETE FROM users WHERE id IN (2, 3, 4, 5, 6);

-- Verify remaining users
-- SELECT id, first_name, last_name, email, role FROM users;

-- Note: If you have any diet plans or other data linked to these sample users,
-- you may want to clean those up too:
-- DELETE FROM diet_plans WHERE client_id IN (2, 3, 4, 5, 6);
-- DELETE FROM diet_plan_items WHERE diet_plan_id IN (SELECT id FROM diet_plans WHERE client_id IN (2, 3, 4, 5, 6));
