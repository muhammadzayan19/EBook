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
    image_path VARCHAR(255) NULL,
    stock INT DEFAULT 0,
    is_free TINYINT(1) DEFAULT 0,
    is_subscription_allowed TINYINT(1) DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;",

"CREATE TABLE IF NOT EXISTS orders (
    order_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    book_id INT NOT NULL,
    quantity INT DEFAULT 1,
    order_type ENUM('pdf','cd','hardcopy') DEFAULT 'pdf',
    total_amount DECIMAL(8,2),
    shipping_address TEXT,
    status ENUM('pending','confirmed','paid','delivered') DEFAULT 'pending',
    order_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (book_id) REFERENCES books(book_id) ON DELETE CASCADE
) ENGINE=InnoDB;",

"CREATE TABLE IF NOT EXISTS payments (
    payment_id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    payment_method ENUM('credit_card','paypal','bank_transfer','cod','debit_card') DEFAULT 'credit_card',
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
    status ENUM('active','upcoming','closed','completed') DEFAULT 'upcoming',
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
    position VARCHAR(50),
    prize VARCHAR(255),
    remarks TEXT NULL,
    announced_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (comp_id) REFERENCES competitions(comp_id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB;",

"CREATE TABLE IF NOT EXISTS download_logs (
    log_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    book_id INT NOT NULL,
    downloaded_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (book_id) REFERENCES books(book_id) ON DELETE CASCADE
) ENGINE=InnoDB;",

"CREATE TABLE IF NOT EXISTS subscriptions (
    subscription_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    plan_type ENUM('monthly','yearly') DEFAULT 'monthly',
    start_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    end_date DATETIME,
    status ENUM('active','cancelled','expired') DEFAULT 'active',
    amount DECIMAL(8,2),
    payment_method VARCHAR(50),
    auto_renew TINYINT(1) DEFAULT 1,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB;",

"CREATE TABLE IF NOT EXISTS subscription_access (
    access_id INT AUTO_INCREMENT PRIMARY KEY,
    subscription_id INT NOT NULL,
    book_id INT NOT NULL,
    granted_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (subscription_id) REFERENCES subscriptions(subscription_id) ON DELETE CASCADE,
    FOREIGN KEY (book_id) REFERENCES books(book_id) ON DELETE CASCADE
) ENGINE=InnoDB;",

"CREATE TABLE IF NOT EXISTS settings (
    setting_id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;"
];

foreach ($tables as $query) {
    if ($conn->query($query) === TRUE) {
        echo "✅ Table created successfully.<br>";
    } else {
        echo "❌ Error creating table: " . $conn->error . "<br>";
    }
}

// Insert default settings
$settings_data = [
    ['site_name', 'Online E-Book System'],
    ['site_email', 'info@ebookstore.com'],
    ['site_phone', '+1 234 567 8900'],
    ['site_address', '123 Book Street, City, Country'],
    ['site_description', 'Welcome to our online e-book store. We offer a wide selection of books across various categories.'],
    ['smtp_host', 'smtp.gmail.com'],
    ['smtp_port', '587'],
    ['smtp_username', 'noreply@ebookstore.com'],
    ['smtp_password', ''],
    ['email_notifications', '1'],
    ['email_users', '1'],
    ['payment_mode', 'test'],
    ['payment_gateway', 'stripe'],
    ['stripe_publishable', ''],
    ['stripe_secret', ''],
    ['auto_approve', '1'],
    ['monthly_subscription_price', '9.99'],
    ['yearly_subscription_price', '99.99']
];

$stmt = $conn->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value)");
foreach ($settings_data as $setting) {
    $stmt->bind_param("ss", $setting[0], $setting[1]);
    $stmt->execute();
}
$stmt->close();
echo "✅ Settings inserted successfully.<br>";

// Check and add missing columns
$columns_to_check = [
    ['table' => 'books', 'column' => 'subscription_price', 'definition' => 'DECIMAL(8,2) DEFAULT 0 AFTER price'],
    ['table' => 'orders', 'column' => 'shipping_address', 'definition' => 'TEXT AFTER total_amount']
];

foreach ($columns_to_check as $col) {
    $check = $conn->query("SHOW COLUMNS FROM {$col['table']} LIKE '{$col['column']}'");
    if ($check->num_rows == 0) {
        $alter = "ALTER TABLE {$col['table']} ADD COLUMN {$col['column']} {$col['definition']}";
        if ($conn->query($alter) === TRUE) {
            echo "✅ Added {$col['column']} column to {$col['table']} table.<br>";
        } else {
            echo "❌ Error adding {$col['column']} column: " . $conn->error . "<br>";
        }
    }
}

// Add indexes for better performance
$indexes = [
    "CREATE INDEX IF NOT EXISTS idx_orders_user_book ON orders(user_id, book_id)",
    "CREATE INDEX IF NOT EXISTS idx_orders_status ON orders(status)",
    "CREATE INDEX IF NOT EXISTS idx_subscriptions_user_active ON subscriptions(user_id, status, end_date)",
    "CREATE INDEX IF NOT EXISTS idx_books_category ON books(category)",
    "CREATE INDEX IF NOT EXISTS idx_books_type ON books(type)",
    "CREATE INDEX IF NOT EXISTS idx_submissions_comp_user ON submissions(comp_id, user_id)",
    "CREATE INDEX IF NOT EXISTS idx_payments_status ON payments(payment_status)"
];

foreach ($indexes as $index_query) {
    if ($conn->query($index_query) === TRUE) {
        echo "✅ Index created successfully.<br>";
    } else {
        // Silently skip if index already exists
        if (strpos($conn->error, 'Duplicate key name') === false) {
            echo "❌ Error creating index: " . $conn->error . "<br>";
        }
    }
}

// Create default admin account
$admin_check = $conn->query("SELECT * FROM admins WHERE username='admin'");
if ($admin_check->num_rows == 0) {
    $hashed = password_hash('admin123', PASSWORD_BCRYPT);
    $stmt = $conn->prepare("INSERT INTO admins (username, password) VALUES (?, ?)");
    $admin_user = 'admin';
    $stmt->bind_param("ss", $admin_user, $hashed);
    $stmt->execute();
    $stmt->close();
    echo "<br>👑 Default admin created — username: <b>admin</b>, password: <b>admin123</b><br>";
}

// Create sample competition if none exists
$comp_check = $conn->query("SELECT COUNT(*) as count FROM competitions");
$comp_count = $comp_check->fetch_assoc()['count'];

echo "<br>🎉 Setup completed successfully!";
echo "<br><br>📌 <b>Important:</b>";
echo "<br>• Admin Login: <b>admin</b> / <b>admin123</b>";
echo "<br>• Don't forget to change the admin password after first login!";
echo "<br>• Database name: <b>$dbname</b>";

$conn->close();
?>