CREATE DATABASE IF NOT EXISTS hardware_store
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_general_ci;

USE hardware_store;

CREATE TABLE users (
  user_id          INT AUTO_INCREMENT PRIMARY KEY,
  username         VARCHAR(50) NOT NULL UNIQUE,
  password         VARCHAR(255) NOT NULL,
  role             ENUM('admin', 'customer') NOT NULL DEFAULT 'customer',
  reset_requested  TINYINT(1) NOT NULL DEFAULT 0,
  created_at       TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE customer (
  customer_id  INT AUTO_INCREMENT PRIMARY KEY,
  user_id      INT NOT NULL UNIQUE,
  fname        VARCHAR(45) NOT NULL,
  lname        VARCHAR(45) NOT NULL,
  address      VARCHAR(150) NOT NULL,
  contact      VARCHAR(11) NOT NULL,
  CONSTRAINT fk_customer_user
    FOREIGN KEY (user_id) REFERENCES users(user_id)
    ON DELETE CASCADE
);

CREATE TABLE category (
  category_id    INT AUTO_INCREMENT PRIMARY KEY,
  category_name  VARCHAR(100) NOT NULL,
  description    TEXT NULL
);

CREATE TABLE item (
  item_id      INT AUTO_INCREMENT PRIMARY KEY,
  category_id  INT NOT NULL,
  item_name    VARCHAR(100) NOT NULL,
  description  TEXT NULL,
  price        DECIMAL(8,2) NOT NULL,
  image_path   VARCHAR(150) NOT NULL DEFAULT 'default.png',
  is_hidden    TINYINT(1) NOT NULL DEFAULT 0,
  CONSTRAINT fk_item_category
    FOREIGN KEY (category_id) REFERENCES category(category_id)
);

CREATE TABLE stock (
  item_id        INT PRIMARY KEY,
  quantity       INT NOT NULL DEFAULT 0,
  reorder_level  INT NOT NULL DEFAULT 5,
  CONSTRAINT fk_stock_item
    FOREIGN KEY (item_id) REFERENCES item(item_id)
    ON DELETE CASCADE
);

CREATE TABLE orderinfo (
  order_id       INT AUTO_INCREMENT PRIMARY KEY,
  customer_id    INT NOT NULL,
  order_date     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  shipping_fee   DECIMAL(8,2) NOT NULL DEFAULT 50.00,
  status         ENUM('processing', 'delivered', 'canceled') NOT NULL DEFAULT 'processing',
  CONSTRAINT fk_order_customer
    FOREIGN KEY (customer_id) REFERENCES customer(customer_id)
);

CREATE TABLE orderline (
  orderline_id  INT AUTO_INCREMENT PRIMARY KEY,
  order_id      INT NOT NULL,
  item_id       INT NOT NULL,
  quantity      INT NOT NULL,
  price_each    DECIMAL(8,2) NOT NULL,
  CONSTRAINT fk_orderline_order
    FOREIGN KEY (order_id) REFERENCES orderinfo(order_id)
    ON DELETE CASCADE,
  CONSTRAINT fk_orderline_item
    FOREIGN KEY (item_id) REFERENCES item(item_id)
);

CREATE VIEW salesperorder AS
SELECT
  o.order_id,
  o.customer_id,
  o.order_date,
  o.status,
  SUM(ol.quantity * ol.price_each)            AS items_subtotal,
  o.shipping_fee,
  SUM(ol.quantity * ol.price_each) + o.shipping_fee AS grand_total
FROM orderinfo o
JOIN orderline ol ON ol.order_id = o.order_id
GROUP BY o.order_id, o.customer_id, o.order_date, o.status, o.shipping_fee;

CREATE VIEW orderdetails AS
SELECT
  o.order_id,
  o.order_date,
  o.status,
  c.fname,
  c.lname,
  c.address,
  c.contact,
  i.item_name,
  ol.quantity,
  ol.price_each,
  (ol.quantity * ol.price_each) AS line_total,
  o.shipping_fee
FROM orderinfo o
JOIN customer c  ON c.customer_id = o.customer_id
JOIN orderline ol ON ol.order_id = o.order_id
JOIN item i       ON i.item_id = ol.item_id;

INSERT INTO users (username, password, role) VALUES
('admin', '$2y$10$tX/L9jReTVG1LXr2mB0URuyybuAGRzvcdRJAYzyr1vHz/aKNoGkES', 'admin');

INSERT INTO users (username, password, role) VALUES
('jdela_cruz', '$2y$10$nVlLnSiZ16K4xgAqdZzrH.E0mLYLGxjZgI36iDQSyxa.0y0OU/KCW', 'customer');

INSERT INTO customer (user_id, fname, lname, address, contact) VALUES
(2, 'Juan', 'Dela Cruz', '123 Mabini St, Taguig City', '09171234567');

INSERT INTO category (category_name, description) VALUES
('Tools', 'Hand tools and power tools'),
('Plumbing', 'Pipes, fittings, and fixtures'),
('Electrical', 'Wiring, outlets, and lighting');

INSERT INTO item (category_id, item_name, description, price, image_path) VALUES
(1, 'Claw Hammer', '16 oz steel head', 189.00, 'claw-hammer.jpg'),
(1, 'Adjustable Wrench', '10 inch', 225.00, 'adjustable-wrench.jpg'),
(1, 'Cordless Drill', '12V with battery pack', 1850.00, 'cordless-drill.jpg'),
(2, 'PVC Pipe Coupling', '1 inch', 25.00, 'pvc-coupling.jpg'),
(2, 'Faucet Valve', 'Brass', 150.00, 'faucet-valve.jpg'),
(3, 'LED Bulb 9W', 'Daylight', 85.00, 'led-bulb.jpg'),
(3, 'Extension Cord', '5 meters', 210.00, 'extension-cord.jpg'),
(3, 'Circuit Breaker', '20A', 320.00, 'circuit-breaker.jpg');

INSERT INTO stock (item_id, quantity, reorder_level) VALUES
(1, 40, 10),
(2, 25, 10),
(3, 8, 5),
(4, 100, 20),
(5, 30, 10),
(6, 60, 15),
(7, 0, 10),
(8, 12, 5);