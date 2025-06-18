-- SQL to add sample users for testing diet plan generation
-- Run this in your SQLite database or via Laravel

INSERT OR IGNORE INTO users (
    id, 
    first_name, 
    last_name, 
    email, 
    phone, 
    location, 
    gym, 
    date_of_birth, 
    password, 
    role, 
    created_at, 
    updated_at
) VALUES 
(1, 'John', 'Trainer', 'trainer@example.com', '+1234567890', 'New York', 'FitGym', '1985-06-15', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'trainer', datetime('now'), datetime('now')),
(2, 'Jane', 'Client', 'client1@example.com', '+1234567891', 'Los Angeles', 'FitGym', '1990-03-20', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'client', datetime('now'), datetime('now')),
(3, 'Mike', 'Johnson', 'client2@example.com', '+1234567892', 'Chicago', 'PowerGym', '1988-11-10', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'client', datetime('now'), datetime('now')),
(4, 'Sarah', 'Williams', 'client3@example.com', '+1234567893', 'Miami', 'HealthClub', '1995-07-25', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'client', datetime('now'), datetime('now')),
(5, 'Admin', 'User', 'admin@example.com', '+1234567894', 'Dallas', 'HeadQuarters', '1980-01-01', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', datetime('now'), datetime('now'));

-- Also create the gyms table if it doesn't exist (referenced by users.gym_id)
INSERT OR IGNORE INTO gyms (id, name, created_at, updated_at) VALUES 
(1, 'FitGym', datetime('now'), datetime('now')),
(2, 'PowerGym', datetime('now'), datetime('now')),
(3, 'HealthClub', datetime('now'), datetime('now'));

-- Verify the data
-- SELECT id, first_name, last_name, email, role, date_of_birth FROM users;
