CREATE DATABASE IF NOT EXISTS kenanginkopi;
USE kenanginkopi;

-- USERS TABLE
CREATE TABLE Users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('Admin', 'User','guest') NOT NULL DEFAULT 'User'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Admin default (password: admin123)
INSERT INTO Users (full_name, username, email, password, role)
VALUES (
    'Admin Utama',
    'admin',
    'admin@kopi.com',
    '$2y$10$NFzTW/HM7O0bxb6bSLRx8eMUrUgWkmBx6ZtgxYAEUZF31qhrebgxu',
    'Admin'
);


-- STORE TABLE

CREATE TABLE Store (
    store_id INT AUTO_INCREMENT PRIMARY KEY,
    store_name VARCHAR(100) NOT NULL,
    store_location VARCHAR(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO Store (store_name, store_location) VALUES
('Morning Night Store', 'Kemang'),
('Dark Night Store', 'Senopati'),
('Tebet Near Station Store', 'Tebet'),
('Gajah Mada Store', 'Yogyakarta'),
('Everyday Is Monday Store', 'Kuningan'),
('Golden Age Store', 'Menteng');
('Dark age store', 'Bandung')


-- COFFEE TABLE

CREATE TABLE Coffee (
    coffee_id INT AUTO_INCREMENT PRIMARY KEY,
    coffee_name VARCHAR(100) NOT NULL,
    price INT NOT NULL,
    description TEXT NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO Coffee (coffee_name, price, description) VALUES
('Kopi Kenangan Mantan', 25000, 'Kopi susu gula aren premium'),
('Kopi Latte Manis', 30000, 'Latte lembut dengan susu fresh'),
('Americano Pahit', 20000, 'Espresso dengan air panas'),
('Cappuccino Cinta', 35000, 'Cappuccino lembut dengan foam tebal');


-- STORE-COFFEE RELATION (Many-to-many)

CREATE TABLE StoreCoffee (
    store_id INT,
    coffee_id INT,
    PRIMARY KEY (store_id, coffee_id),
    FOREIGN KEY (store_id) REFERENCES Store(store_id) ON DELETE CASCADE,
    FOREIGN KEY (coffee_id) REFERENCES Coffee(coffee_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO StoreCoffee (store_id, coffee_id) VALUES
(1, 1), (1, 2), (1, 3),
(2, 2), (2, 4),
(3, 1), (3, 4);


-- TRANSACTIONS

CREATE TABLE Transactions (
    transaction_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    store_id INT NOT NULL,
    transaction_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES Users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (store_id) REFERENCES Store(store_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- TRANSACTION DETAILS

CREATE TABLE TransactionDetails (
    detail_id INT AUTO_INCREMENT PRIMARY KEY,
    transaction_id INT NOT NULL,
    coffee_id INT NOT NULL,
    quantity INT NOT NULL,
    subtotal INT NOT NULL,
    FOREIGN KEY (transaction_id) REFERENCES Transactions(transaction_id) ON DELETE CASCADE,
    FOREIGN KEY (coffee_id) REFERENCES Coffee(coffee_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
