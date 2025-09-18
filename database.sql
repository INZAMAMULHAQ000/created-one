-- Create database
CREATE DATABASE IF NOT EXISTS supermarket_billing;
USE supermarket_billing;

-- Create materials table
CREATE TABLE IF NOT EXISTS materials (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    hsn_code VARCHAR(20) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert some sample materials
INSERT INTO materials (name, price, hsn_code) VALUES
('Sample Material 1', 100.00, 'A1'),
('Sample Material 2', 150.00, 'B6'),
('Sample Material 3', 200.00, 'C3');

-- Create transports table
CREATE TABLE IF NOT EXISTS transports (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert some sample transport modes
INSERT INTO transports (name) VALUES
('Truck'),
('Van'),
('Bike');

-- Create purchase_orders table
CREATE TABLE IF NOT EXISTS purchase_orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    po_number VARCHAR(255) NOT NULL UNIQUE,
    seller_name VARCHAR(255) NOT NULL,
    seller_company VARCHAR(255) NOT NULL,
    buyer_name VARCHAR(255) NOT NULL,
    buyer_company VARCHAR(255) NOT NULL,
    po_date DATE NOT NULL,
    pdf_path VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;