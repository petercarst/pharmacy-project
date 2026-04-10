-- =========================
-- PHARMACY DATABASE STRUCTURE
-- =========================

CREATE TABLE customers (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  phone VARCHAR(20),
  email VARCHAR(100),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE suppliers (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  contact VARCHAR(50)
);

CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  full_name VARCHAR(100) NOT NULL,
  role ENUM('admin','pharmacist') DEFAULT 'pharmacist',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  upc_ean_isbn VARCHAR(50),
  product_id VARCHAR(50),
  item_name VARCHAR(100) NOT NULL,
  category VARCHAR(50) NOT NULL,
  product_unit VARCHAR(50),
  date_delivered DATE,
  expiration_date DATE,
  supplier_id INT,
  stock INT DEFAULT 0,
  selling_price DECIMAL(10,2) DEFAULT 1000.00,
  
  FOREIGN KEY (supplier_id) REFERENCES suppliers(id)
  ON DELETE SET NULL
);

CREATE TABLE receivings (
  id INT AUTO_INCREMENT PRIMARY KEY,
  item_id INT NOT NULL,
  quantity INT NOT NULL,
  date_received DATE DEFAULT CURRENT_DATE,
  supplier_id INT,

  FOREIGN KEY (item_id) REFERENCES items(id),
  FOREIGN KEY (supplier_id) REFERENCES suppliers(id)
);

CREATE TABLE sales (
  id INT AUTO_INCREMENT PRIMARY KEY,
  item_id INT NOT NULL,
  quantity INT NOT NULL,
  total DECIMAL(10,2) NOT NULL,
  sale_date DATE DEFAULT CURRENT_DATE,
  customer_id INT,

  FOREIGN KEY (item_id) REFERENCES items(id),
  FOREIGN KEY (customer_id) REFERENCES customers(id)
);

CREATE TABLE expenses (
  id INT AUTO_INCREMENT PRIMARY KEY,
  description VARCHAR(200) NOT NULL,
  amount DECIMAL(10,2) NOT NULL,
  expense_date DATE DEFAULT CURRENT_DATE
);