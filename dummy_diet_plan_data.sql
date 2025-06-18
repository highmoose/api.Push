-- SQL INSERT queries for adding dummy data to diet plan tables

-- First, let's add some dummy data to diet_plans table
-- Note: Make sure you have valid trainer_id and client_id values from your users table

INSERT INTO diet_plans (
    trainer_id, 
    client_id, 
    title, 
    description, 
    plan_type, 
    meals_per_day, 
    meal_complexity, 
    total_calories, 
    generated_by_ai, 
    ai_prompt, 
    ai_response, 
    created_at, 
    updated_at
) VALUES 
(1, 2, 'Weight Loss Plan - Week 1', 'Custom weight loss diet plan designed for gradual fat loss', 'weight_loss', 5, 'moderate', 1800, 0, NULL, NULL, NOW(), NOW()),
(1, 3, 'Muscle Building Plan', 'High protein diet plan for muscle gain', 'muscle_gain', 6, 'complex', 2500, 0, NULL, NULL, NOW(), NOW()),
(1, 4, 'Maintenance Plan', 'Balanced diet plan for weight maintenance', 'maintenance', 4, 'simple', 2000, 0, NULL, NULL, NOW(), NOW()),
(1, 2, 'AI Generated Keto Plan', 'AI-generated ketogenic diet plan', 'ketogenic', 3, 'moderate', 1600, 1, 'Create a ketogenic diet plan with 3 meals per day', '{"ai_response": "sample"}', NOW(), NOW());

-- Now add corresponding diet_plan_items for each plan
-- Items for Plan 1 (Weight Loss Plan - Week 1)
INSERT INTO diet_plan_items (
    diet_plan_id, 
    meal_name, 
    meal_type, 
    ingredients, 
    instructions, 
    calories, 
    protein, 
    carbs, 
    fats, 
    meal_order, 
    created_at, 
    updated_at
) VALUES 
-- Plan 1 meals
(1, 'Greek Yogurt Parfait', 'breakfast', '["Greek yogurt", "berries", "granola", "honey"]', 'Layer Greek yogurt with berries and granola, drizzle with honey', 300, 20, 35, 8, 1, NOW(), NOW()),
(1, 'Grilled Chicken Salad', 'lunch', '["chicken breast", "mixed greens", "cucumber", "tomatoes", "olive oil", "lemon"]', 'Grill chicken breast, serve over mixed greens with vegetables and dressing', 400, 35, 15, 20, 2, NOW(), NOW()),
(1, 'Apple with Almond Butter', 'snack', '["apple", "almond butter"]', 'Slice apple and serve with 2 tbsp almond butter', 200, 8, 25, 12, 3, NOW(), NOW()),
(1, 'Baked Salmon with Vegetables', 'dinner', '["salmon fillet", "broccoli", "sweet potato", "olive oil"]', 'Bake salmon with herbs, steam broccoli, roast sweet potato', 450, 30, 40, 18, 4, NOW(), NOW()),
(1, 'Protein Smoothie', 'snack', '["protein powder", "banana", "spinach", "almond milk"]', 'Blend all ingredients until smooth', 250, 25, 20, 5, 5, NOW(), NOW()),

-- Plan 2 meals (Muscle Building Plan)
(2, 'Oatmeal with Protein Powder', 'breakfast', '["oats", "protein powder", "banana", "peanut butter"]', 'Cook oats, mix in protein powder, top with banana and peanut butter', 500, 30, 50, 18, 1, NOW(), NOW()),
(2, 'Turkey and Avocado Wrap', 'lunch', '["whole wheat tortilla", "turkey breast", "avocado", "spinach", "hummus"]', 'Spread hummus on tortilla, add turkey, avocado, and spinach, roll up', 450, 35, 40, 15, 2, NOW(), NOW()),
(2, 'Chocolate Protein Shake', 'snack', '["whey protein", "milk", "banana", "cocoa powder"]', 'Blend all ingredients with ice', 300, 25, 20, 8, 3, NOW(), NOW()),
(2, 'Beef Stir Fry with Rice', 'dinner', '["lean beef", "brown rice", "mixed vegetables", "soy sauce", "ginger"]', 'Stir fry beef with vegetables, serve over brown rice', 600, 40, 50, 20, 4, NOW(), NOW()),
(2, 'Cottage Cheese with Nuts', 'snack', '["cottage cheese", "mixed nuts", "berries"]', 'Serve cottage cheese topped with nuts and berries', 250, 20, 15, 12, 5, NOW(), NOW()),
(2, 'Tuna Sandwich', 'snack', '["whole grain bread", "tuna", "avocado", "lettuce"]', 'Make sandwich with tuna, avocado, and lettuce', 350, 25, 30, 12, 6, NOW(), NOW()),

-- Plan 3 meals (Maintenance Plan)
(3, 'Scrambled Eggs with Toast', 'breakfast', '["eggs", "whole grain bread", "butter", "spinach"]', 'Scramble eggs with spinach, serve with buttered toast', 400, 18, 30, 22, 1, NOW(), NOW()),
(3, 'Quinoa Bowl', 'lunch', '["quinoa", "chickpeas", "vegetables", "tahini dressing"]', 'Cook quinoa, top with chickpeas and vegetables, drizzle with dressing', 500, 20, 60, 18, 2, NOW(), NOW()),
(3, 'Mixed Nuts and Fruit', 'snack', '["almonds", "walnuts", "orange"]', 'Serve mixed nuts with fresh orange', 300, 10, 20, 22, 3, NOW(), NOW()),
(3, 'Grilled Fish with Quinoa', 'dinner', '["white fish", "quinoa", "asparagus", "lemon"]', 'Grill fish with lemon, serve with quinoa and steamed asparagus', 450, 35, 45, 12, 4, NOW(), NOW()),

-- Plan 4 meals (AI Generated Keto Plan)
(4, 'Keto Avocado Eggs', 'breakfast', '["avocado", "eggs", "bacon", "cheese"]', 'Bake eggs in avocado halves, serve with bacon and cheese', 600, 25, 8, 52, 1, NOW(), NOW()),
(4, 'Keto Chicken Caesar Salad', 'lunch', '["chicken thighs", "romaine lettuce", "parmesan", "caesar dressing"]', 'Grill chicken thighs, serve over romaine with parmesan and dressing', 550, 40, 10, 42, 2, NOW(), NOW()),
(4, 'Keto Beef and Broccoli', 'dinner', '["ground beef", "broccoli", "cheese", "butter"]', 'Brown ground beef, steam broccoli, combine with cheese and butter', 650, 35, 12, 55, 3, NOW(), NOW());

-- You can verify the data was inserted correctly with these SELECT queries:
-- SELECT * FROM diet_plans;
-- SELECT * FROM diet_plan_items ORDER BY diet_plan_id, meal_order;
-- SELECT dp.title, dpi.meal_name, dpi.meal_type, dpi.calories 
-- FROM diet_plans dp 
-- JOIN diet_plan_items dpi ON dp.id = dpi.diet_plan_id 
-- ORDER BY dp.id, dpi.meal_order;
