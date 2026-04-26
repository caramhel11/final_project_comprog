-- ============================================================
-- Isla Finds: Marinduque Souvenir Shop - Database Schema
-- ============================================================

CREATE DATABASE IF NOT EXISTS isla_finds CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE isla_finds;

-- Category Table
CREATE TABLE IF NOT EXISTS Category_Table (
    CategoryID INT PRIMARY KEY AUTO_INCREMENT,
    CategoryName VARCHAR(100) NOT NULL
);

-- Admin Table
CREATE TABLE IF NOT EXISTS Admin_Table (
    AdminID INT PRIMARY KEY AUTO_INCREMENT,
    Username VARCHAR(100) NOT NULL UNIQUE,
    Email VARCHAR(150) NOT NULL UNIQUE,
    Password VARCHAR(255) NOT NULL,
    Role ENUM('Owner', 'Cashier') DEFAULT 'Cashier'
);

-- Customer Table
CREATE TABLE IF NOT EXISTS Customer_Table (
    CustomerID INT PRIMARY KEY AUTO_INCREMENT,
    FullName VARCHAR(150) NOT NULL,
    Email VARCHAR(150) NOT NULL UNIQUE,
    Password VARCHAR(255) NOT NULL
);

-- Products Table
CREATE TABLE IF NOT EXISTS Products_Table (
    ProductID INT PRIMARY KEY AUTO_INCREMENT,
    ProductName VARCHAR(150) NOT NULL,
    CategoryID INT NOT NULL,
    Price DECIMAL(10,2) NOT NULL,
    StockQty INT NOT NULL DEFAULT 0,
    ImageURL VARCHAR(255) DEFAULT NULL,
    Description TEXT DEFAULT NULL,
    FOREIGN KEY (CategoryID) REFERENCES Category_Table(CategoryID)
);

-- Order Table
CREATE TABLE IF NOT EXISTS Order_Table (
    OrderID INT PRIMARY KEY AUTO_INCREMENT,
    CustomerID INT NOT NULL,
    AdminID INT DEFAULT NULL,
    OrderDate DATETIME DEFAULT CURRENT_TIMESTAMP,
    Status ENUM('Pending','Processing','Shipped','Delivered','Cancelled') DEFAULT 'Pending',
    TotalAmount DECIMAL(10,2) DEFAULT 0.00,
    FOREIGN KEY (CustomerID) REFERENCES Customer_Table(CustomerID),
    FOREIGN KEY (AdminID) REFERENCES Admin_Table(AdminID)
);

-- Order Details Table
CREATE TABLE IF NOT EXISTS OrderDetails_Table (
    DetailID INT PRIMARY KEY AUTO_INCREMENT,
    OrderID INT NOT NULL,
    ProductID INT NOT NULL,
    Quantity INT NOT NULL,
    UnitPrice DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (OrderID) REFERENCES Order_Table(OrderID),
    FOREIGN KEY (ProductID) REFERENCES Products_Table(ProductID)
);

-- ============================================================
-- Seed Data
-- ============================================================

INSERT INTO Category_Table (CategoryID, CategoryName) VALUES
(1, 'Delicacies'),
(2, 'Apparel'),
(3, 'Handicrafts'),
(4, 'Souvenirs');

-- Admin accounts (passwords are bcrypt hashes; raw: owner123, cash123)
INSERT INTO Admin_Table (AdminID, Username, Email, Password, Role) VALUES
(301, 'genelyn_esplana', 'genelyn@email.com', '$2y$12$eImiTXuWVxfM37uY4JANjOe5XwbmkwLSv0UOg1a2HkX1IK5rF6eYm', 'Owner'),
(302, 'claire_sambilay',  'claire@gmail.com',  '$2y$12$D9zV0hG1jQlkMsNpYtWbEu8XkL3fRzA5cH7mJvT2qP6yO1dW4iKne', 'Cashier');

-- Customer accounts (raw passwords: user123, user456, user789)
INSERT INTO Customer_Table (CustomerID, FullName, Email, Password) VALUES
(201, 'Jead Palmero',     'jead@email.com',   '$2y$12$eImiTXuWVxfM37uY4JANjOe5XwbmkwLSv0UOg1a2HkX1IK5rF6eYm'),
(202, 'Amanda Son',       'amanda@email.com', '$2y$12$D9zV0hG1jQlkMsNpYtWbEu8XkL3fRzA5cH7mJvT2qP6yO1dW4iKne'),
(203, 'Mhel John Pielago','mhel@email.com',   '$2y$12$9KqP4nB7mJvL2xCeRtYuA3wFhZiOdGsM5kN8pQ1lV6yT0bH4jU7re');

INSERT INTO Products_Table (ProductID, ProductName, CategoryID, Price, StockQty, Description) VALUES
(101, 'Arrowroot Cookies',  1, 450.00, 50,  'Crispy, light cookies made from Marinduque arrowroot — a beloved local delicacy.'),
(102, 'Bibingkang Lalaki',  1, 180.00, 100, 'A classic Marinduque sticky rice cake, sweet and chewy, baked the traditional way.'),
(103, 'Banana Chips',       1, 120.00, 150, 'Crunchy golden chips made from ripe local bananas, lightly salted or sweetened.'),
(104, 'Coco Jam',           1, 95.00,  120, 'Rich coconut caramel spread, slow-cooked to perfection — great on bread or pastries.'),
(105, 'Coconut Bagoong',    1, 85.00,  100, 'A savory coconut-based fermented shrimp paste unique to Marinduque cuisine.'),
(106, 'Buntal Bag',         3, 1500.00,75,  'Handwoven from fine buntal fibers by Marinduque artisans — elegant and durable.'),
(107, 'Tote Bag',           3, 350.00, 50,  'Locally crafted tote bag featuring Marinduque-inspired patterns and weaves.'),
(108, 'Wallet',             3, 280.00, 60,  'Hand-stitched leather wallet with traditional Marinduque motifs.'),
(109, 'Buri Bag',           3, 650.00, 50,  'Woven from buri palm leaves, this eco-friendly bag is a Marinduque craft staple.'),
(110, 'Stylish Nito',       3, 820.00, 70,  'Premium nito vine woven accessory — a testament to Marinduque craftsmanship.'),
(111, 'T-shirt Marinduque', 2, 350.00, 75,  'Soft, breathable cotton tee featuring iconic Marinduque landmarks and art.'),
(112, 'Moriones T-shirt',   2, 450.00, 80,  'Celebrate the famous Moriones Festival with this vibrant graphic tee.'),
(113, 'Marinduque Mug',     4, 180.00, 125, 'Ceramic mug featuring Marinduque scenery — perfect for your morning brew.'),
(114, 'Butter Fly Clock',   4, 420.00, 45,  'Decorative wall clock with butterfly motifs inspired by Marinduque''s natural beauty.'),
(115, 'Marinduque Pen',     4, 55.00,  200, 'Souvenir ballpen engraved with Marinduque landmarks — a simple, lasting keepsake.'),
(116, 'Keychain',           4, 45.00,  220, 'Compact metal keychain shaped like the island of Marinduque.'),
(117, 'Whine Bottle Holder',4, 380.00, 35,  'Hand-crafted rattan wine bottle holder with intricate Marinduque weaving patterns.');

INSERT INTO Order_Table (OrderID, CustomerID, AdminID, OrderDate, Status, TotalAmount) VALUES
(5001, 202, 301, '2026-04-24 14:30:00', 'Delivered', 1350.00),
(5002, 201, 302, '2026-03-28 10:26:00', 'Delivered', 350.00),
(5003, 203, 301, '2026-04-15 11:30:00', 'Processing', 170.00);

INSERT INTO OrderDetails_Table (DetailID, OrderID, ProductID, Quantity, UnitPrice) VALUES
(9001, 5001, 101, 3, 450.00),
(9002, 5002, 107, 1, 350.00),
(9003, 5003, 105, 2, 85.00);
