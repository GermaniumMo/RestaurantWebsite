-- Add more diverse menu items to Savoria restaurant database

USE savoria_db;

-- Additional Appetizers
INSERT INTO menu_items (category_id, name, description, price, is_available, is_featured, display_order) VALUES 
(1, 'Truffle Arancini', 'Crispy risotto balls stuffed with truffle and parmesan, served with aioli', 16.00, TRUE, FALSE, 1),
(1, 'Pan-Seared Scallops', 'Fresh Atlantic scallops with cauliflower purée and pancetta crisps', 22.00, TRUE, TRUE, 2),
(1, 'Burrata Caprese', 'Creamy burrata with heirloom tomatoes, basil oil, and aged balsamic', 18.00, TRUE, FALSE, 3),
(1, 'Tuna Tartare', 'Yellowfin tuna with avocado, citrus, and sesame wafer', 24.00, TRUE, FALSE, 4),
(1, 'Wild Mushroom Bruschetta', 'Toasted sourdough with sautéed wild mushrooms and herb ricotta', 14.00, TRUE, FALSE, 5),
(1, 'Crispy Calamari', 'Fresh squid rings with spicy marinara and lemon aioli', 15.00, TRUE, FALSE, 6);

-- Additional Main Courses
INSERT INTO menu_items (category_id, name, description, price, is_available, is_featured, display_order) VALUES 
(2, 'Lobster Ravioli', 'House-made pasta filled with Maine lobster in saffron cream sauce', 38.00, TRUE, TRUE, 4),
(2, 'Duck Confit', 'Slow-cooked duck leg with cherry gastrique and roasted root vegetables', 34.00, TRUE, FALSE, 5),
(2, 'Osso Buco', 'Braised veal shank with saffron risotto and gremolata', 45.00, TRUE, FALSE, 6),
(2, 'Chilean Sea Bass', 'Miso-glazed sea bass with bok choy and jasmine rice', 44.00, TRUE, FALSE, 7),
(2, 'Rack of Lamb', 'Herb-crusted lamb with ratatouille and rosemary jus', 48.00, TRUE, TRUE, 8),
(2, 'Chicken Saltimbocca', 'Pan-seared chicken breast with prosciutto, sage, and white wine sauce', 28.00, TRUE, FALSE, 9),
(2, 'Vegetarian Wellington', 'Puff pastry filled with roasted vegetables, quinoa, and herb cashew cream', 26.00, TRUE, FALSE, 10),
(2, 'Salmon Teriyaki', 'Grilled Atlantic salmon with teriyaki glaze and Asian vegetables', 32.00, TRUE, FALSE, 11),
(2, 'Beef Tenderloin', 'Filet mignon with truffle mashed potatoes and red wine reduction', 52.00, TRUE, TRUE, 12),
(2, 'Pork Belly', 'Slow-braised pork belly with apple slaw and bourbon glaze', 30.00, TRUE, FALSE, 13);

-- Additional Desserts
INSERT INTO menu_items (category_id, name, description, price, is_available, is_featured, display_order) VALUES 
(3, 'Classic Tiramisu', 'Traditional Italian dessert with espresso-soaked ladyfingers and mascarpone', 12.00, TRUE, FALSE, 4),
(3, 'Crème Brûlée', 'Vanilla bean custard with caramelized sugar and fresh berries', 14.00, TRUE, TRUE, 5),
(3, 'New York Cheesecake', 'Rich cheesecake with graham cracker crust and berry compote', 13.00, TRUE, FALSE, 6),
(3, 'Lemon Tart', 'Buttery pastry shell filled with tangy lemon curd and meringue', 11.00, TRUE, FALSE, 7),
(3, 'Chocolate Lava Cake', 'Warm chocolate cake with molten center and vanilla ice cream', 15.00, TRUE, TRUE, 8),
(3, 'Panna Cotta', 'Silky vanilla panna cotta with seasonal fruit coulis', 10.00, TRUE, FALSE, 9),
(3, 'Apple Tarte Tatin', 'Upside-down apple tart with cinnamon ice cream', 13.00, TRUE, FALSE, 10),
(3, 'Gelato Selection', 'Three scoops of artisanal gelato - vanilla, chocolate, and pistachio', 9.00, TRUE, FALSE, 11);

-- Beverages
INSERT INTO menu_items (category_id, name, description, price, is_available, is_featured, display_order) VALUES 
(4, 'House Wine - Red', 'Cabernet Sauvignon from Napa Valley', 12.00, TRUE, FALSE, 1),
(4, 'House Wine - White', 'Chardonnay from Sonoma County', 11.00, TRUE, FALSE, 2),
(4, 'Champagne', 'Dom Pérignon Vintage', 45.00, TRUE, TRUE, 3),
(4, 'Craft Beer Selection', 'Local brewery rotating taps', 8.00, TRUE, FALSE, 4),
(4, 'Savoria Signature Cocktail', 'Gin, elderflower, cucumber, and lime', 14.00, TRUE, TRUE, 5),
(4, 'Old Fashioned', 'Premium bourbon with house-made bitters', 16.00, TRUE, FALSE, 6),
(4, 'Espresso Martini', 'Vodka, coffee liqueur, and fresh espresso', 15.00, TRUE, FALSE, 7),
(4, 'Fresh Juice', 'Orange, apple, or cranberry', 6.00, TRUE, FALSE, 8),
(4, 'Sparkling Water', 'San Pellegrino or Perrier', 4.00, TRUE, FALSE, 9),
(4, 'Coffee Selection', 'Espresso, cappuccino, or latte', 5.00, TRUE, FALSE, 10),
(4, 'Premium Tea', 'Earl Grey, Green Tea, or Chamomile', 4.00, TRUE, FALSE, 11),
(4, 'Mocktail - Virgin Mojito', 'Fresh mint, lime, and soda water', 8.00, TRUE, FALSE, 12);
