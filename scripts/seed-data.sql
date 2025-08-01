-- Insert sample menu items
INSERT INTO menu_items (name, description, price, category, image_url, rating, review_count) VALUES
('Classic Pasta Pomodoro', 'Fresh pasta with cherry tomatoes, basil, and extra virgin olive oil.', 16.99, 'main_course', 'images/menu/Classic Pasta Pomodoro.png', 4.8, 120),
('Grilled Salmon', 'Fresh Atlantic salmon with seasonal vegetables and lemon butter sauce.', 24.99, 'main_course', 'images/menu/Grilled Salmon.jpg', 4.9, 85),
('Chocolate Lava Cake', 'Warm chocolate cake with molten center, served with vanilla ice cream.', 8.99, 'desserts', 'images/menu/Chocolate Lava Cake.png', 4.7, 156),
('Chicken Caesar Salad', 'Crisp romaine, grilled chicken, croutons, and house-made Caesar dressing', 14.99, 'starters', 'images/menu/Chicken Caesar Salad.png', 4.6, 92),
('Margherita Pizza', 'Fresh mozzarella, tomatoes, and basil on our house-made crust', 18.99, 'main_course', 'images/menu/Margherita Pizza.jpg', 4.8, 178),
('Classic Tiramisu', 'Traditional Italian dessert with layers of coffee-soaked ladyfingers', 9.99, 'desserts', 'images/menu/Classic Tiramisu.png', 4.9, 134),
('Grilled Sea Bass', 'Fresh Mediterranean sea bass with herbs and lemon butter sauce', 42.00, 'main_course', 'images/food/pexels-crysnet-11653557.jpg', 4.9, 67),
('Prime Ribeye Steak', '28-day aged beef with roasted vegetables and red wine jus', 56.00, 'main_course', 'images/food/Steak.png', 4.8, 89),
('Chocolate Symphony', 'Dark chocolate mousse with berry compote and gold leaf', 18.00, 'desserts', 'images/food/chocolate.png', 4.7, 45);

-- Insert default admin user (password: admin123)
INSERT INTO admin_users (username, password, email, role) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@savoria.com', 'admin');
