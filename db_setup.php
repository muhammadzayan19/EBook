<?php
$servername = "localhost";
$username   = "root";
$password   = "";
$dbname     = "ebook";

$conn = new mysqli($servername, $username, $password);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "CREATE DATABASE IF NOT EXISTS $dbname CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
if ($conn->query($sql) === TRUE) {
    echo "✅ Database '$dbname' created or already exists.<br>";
} else {
    die("❌ Error creating database: " . $conn->error);
}

$conn->select_db($dbname);

$tables = [

"CREATE TABLE IF NOT EXISTS users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    address TEXT,
    phone VARCHAR(15),
    registered_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;",

"CREATE TABLE IF NOT EXISTS admins (
    admin_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL
) ENGINE=InnoDB;",

"CREATE TABLE IF NOT EXISTS books (
    book_id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(150) NOT NULL,
    author VARCHAR(100),
    category VARCHAR(50),
    description TEXT,
    price DECIMAL(8,2) DEFAULT 0,
    subscription_price DECIMAL(8,2) DEFAULT 0,
    type ENUM('pdf','cd','hardcopy') DEFAULT 'pdf',
    file_path VARCHAR(255),
    stock INT DEFAULT 0,
    is_free TINYINT(1) DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;",

"CREATE TABLE IF NOT EXISTS orders (
    order_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    book_id INT NOT NULL,
    quantity INT DEFAULT 1,
    order_type ENUM('pdf','cd','hardcopy') DEFAULT 'pdf',
    total_amount DECIMAL(8,2),
    status ENUM('pending','confirmed','paid','delivered') DEFAULT 'pending',
    order_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (book_id) REFERENCES books(book_id) ON DELETE CASCADE
) ENGINE=InnoDB;",

"CREATE TABLE IF NOT EXISTS payments (
    payment_id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    payment_method ENUM('credit_card','dd','cheque','vpp') DEFAULT 'credit_card',
    payment_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    amount DECIMAL(8,2),
    payment_status ENUM('pending','completed','failed') DEFAULT 'pending',
    FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE CASCADE
) ENGINE=InnoDB;",

"CREATE TABLE IF NOT EXISTS competitions (
    comp_id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(100) NOT NULL,
    type ENUM('essay','story') DEFAULT 'essay',
    topic VARCHAR(255),
    start_date DATETIME,
    end_date DATETIME,
    prize VARCHAR(255),
    status ENUM('active','upcoming','closed','completed') DEFAULT 'upcoming'
    description TEXT
) ENGINE=InnoDB;",

"CREATE TABLE IF NOT EXISTS submissions (
    submission_id INT AUTO_INCREMENT PRIMARY KEY,
    comp_id INT NOT NULL,
    user_id INT NOT NULL,
    file_path VARCHAR(255),
    submitted_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (comp_id) REFERENCES competitions(comp_id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB;",

"CREATE TABLE IF NOT EXISTS winners (
    winner_id INT AUTO_INCREMENT PRIMARY KEY,
    comp_id INT NOT NULL,
    user_id INT NOT NULL,
    position VARCHAR(10),
    prize VARCHAR(255),
    announced_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (comp_id) REFERENCES competitions(comp_id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB;",

"CREATE TABLE IF NOT EXISTS settings (
    setting_id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;",

"INSERT INTO settings (setting_key, setting_value) VALUES
('site_name', 'Online E-Book System'),
('site_email', 'info@ebookstore.com'),
('site_phone', '+1 234 567 8900'),
('site_address', '123 Book Street, City, Country'),
('site_description', 'Welcome to our online e-book store. We offer a wide selection of books across various categories.'),
('smtp_host', 'smtp.gmail.com'),
('smtp_port', '587'),
('smtp_username', 'noreply@ebookstore.com'),
('smtp_password', ''),
('email_notifications', '1'),
('email_users', '1'),
('payment_mode', 'test'),
('payment_gateway', 'stripe'),
('stripe_publishable', ''),
('stripe_secret', ''),
('auto_approve', '1')
ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value);"
];

foreach ($tables as $query) {
    if ($conn->query($query) === TRUE) {
        echo "✅ Table created successfully.<br>";
    } else {
        echo "❌ Error creating table: " . $conn->error . "<br>";
    }
}

$admin_check = $conn->query("SELECT * FROM admins WHERE username='admin'");
if ($admin_check->num_rows == 0) {
    $hashed = password_hash('admin123', PASSWORD_BCRYPT);
    $conn->query("INSERT INTO admins (username, password) VALUES ('admin', '$hashed')");
    echo "<br>👑 Default admin created — username: <b>admin</b>, password: <b>admin123</b><br>";
}

echo "<br>🎉 Setup completed successfully!";
$conn->close();
?>
