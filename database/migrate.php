<?php
use App\Core\DB;
$auto = 'INT AUTO_INCREMENT PRIMARY KEY';
$tables = [
    'content_translations' => "entity VARCHAR(32) NOT NULL,entity_id INT NOT NULL,locale VARCHAR(2) NOT NULL,field VARCHAR(100) NOT NULL,value TEXT NOT NULL,PRIMARY KEY(entity,entity_id,locale,field)",
    'order_events' => "id $auto,order_id INT NOT NULL,status VARCHAR(30) NOT NULL,created_at VARCHAR(30) NOT NULL,INDEX(order_id)",
    'users' => "id $auto,name TEXT NOT NULL,email VARCHAR(190) NOT NULL UNIQUE,password VARCHAR(255) NOT NULL,role VARCHAR(20) NOT NULL,session_version INT NOT NULL DEFAULT 1,created_at VARCHAR(30) NOT NULL",
    'categories' => "id $auto,name TEXT NOT NULL,slug VARCHAR(150) NOT NULL UNIQUE,description TEXT NOT NULL,image TEXT NOT NULL,sort_order INT NOT NULL DEFAULT 0",
    'media' => "id $auto,url VARCHAR(255) NOT NULL UNIQUE,original_name VARCHAR(190) NOT NULL,alt TEXT NOT NULL,width INT NOT NULL,height INT NOT NULL,created_at VARCHAR(30) NOT NULL",
    'faqs' => "id $auto,title TEXT NOT NULL,answer TEXT NOT NULL,sort_order INT NOT NULL DEFAULT 0",
    'products' => "id $auto,category_id INT NOT NULL,name TEXT NOT NULL,slug VARCHAR(190) NOT NULL UNIQUE,description TEXT NOT NULL,price INT NOT NULL,images TEXT NOT NULL,ingredients TEXT NOT NULL,preparation_minutes INT NOT NULL DEFAULT 15,available INT NOT NULL DEFAULT 1,featured INT NOT NULL DEFAULT 0,badge VARCHAR(100) NOT NULL,sort_order INT NOT NULL DEFAULT 0,FOREIGN KEY(category_id) REFERENCES categories(id)",
    'features' => "id $auto,title TEXT NOT NULL,description TEXT NOT NULL,sort_order INT NOT NULL DEFAULT 0",
    'gallery' => "id $auto,title TEXT NOT NULL,image TEXT NOT NULL,sort_order INT NOT NULL DEFAULT 0",
    'reviews' => "id $auto,name TEXT NOT NULL,comment TEXT NOT NULL,rating INT NOT NULL,published INT NOT NULL DEFAULT 0,sort_order INT NOT NULL DEFAULT 0",
    'settings' => 'setting_key VARCHAR(100) PRIMARY KEY,value TEXT NOT NULL',
    'orders' => "id $auto,number VARCHAR(40) NOT NULL UNIQUE,access_token VARCHAR(64) NOT NULL UNIQUE,idempotency_key VARCHAR(64) NOT NULL UNIQUE,customer_name TEXT NOT NULL,email VARCHAR(190) NOT NULL,phone VARCHAR(20) NOT NULL,address TEXT NOT NULL,order_type VARCHAR(20) NOT NULL,notes TEXT NOT NULL,subtotal INT NOT NULL,delivery_fee INT NOT NULL,total INT NOT NULL,status VARCHAR(30) NOT NULL DEFAULT 'Pending',payment_method VARCHAR(20) NOT NULL,payment_status VARCHAR(30) NOT NULL DEFAULT 'unpaid',created_at VARCHAR(30) NOT NULL",
    'order_items' => "id $auto,order_id INT NOT NULL,product_id INT,name TEXT NOT NULL,price INT NOT NULL,quantity INT NOT NULL,FOREIGN KEY(order_id) REFERENCES orders(id)",
    'payments' => "id $auto,order_id INT NOT NULL UNIQUE,transaction_id VARCHAR(80) NOT NULL UNIQUE,provider_reference VARCHAR(190) UNIQUE,session_key VARCHAR(190),gateway_url TEXT,status VARCHAR(30) NOT NULL DEFAULT 'pending',amount INT NOT NULL,currency VARCHAR(5) NOT NULL DEFAULT 'BDT',created_at VARCHAR(30) NOT NULL,updated_at VARCHAR(30) NOT NULL,FOREIGN KEY(order_id) REFERENCES orders(id)",
    'reservations' => "id $auto,name TEXT NOT NULL,email VARCHAR(190) NOT NULL,phone VARCHAR(20) NOT NULL,reserved_at VARCHAR(30) NOT NULL,guests INT NOT NULL,notes TEXT NOT NULL,status VARCHAR(30) NOT NULL DEFAULT 'Pending',created_at VARCHAR(30) NOT NULL",
    'messages' => "id $auto,name TEXT NOT NULL,email VARCHAR(190) NOT NULL,message TEXT NOT NULL,status VARCHAR(20) NOT NULL DEFAULT 'Unread',created_at VARCHAR(30) NOT NULL",
    'activities' => "id $auto,user_id INT,action TEXT NOT NULL,created_at VARCHAR(30) NOT NULL",
    'rate_limits' => 'bucket VARCHAR(190) PRIMARY KEY,hits INT NOT NULL,expires_at INT NOT NULL',
    'password_resets' =>
        'email VARCHAR(190) PRIMARY KEY,token_hash VARCHAR(64) NOT NULL,expires_at INT NOT NULL',
];
foreach ($tables as $name => $columns) {
    DB::connection()->exec(
        "CREATE TABLE IF NOT EXISTS $name ($columns)" .
            (env('DB_CONNECTION') === 'mysql' ? ' ENGINE=InnoDB DEFAULT CHARSET=utf8mb4' : ''),
    );
}

$columns = DB::all("SHOW COLUMNS FROM products");
$names = array_column($columns, 'Field');
foreach (['allergens','dietary'] as $field) if (!in_array($field, $names)) DB::connection()->exec("ALTER TABLE products ADD $field TEXT NULL");
foreach (['products'=>'name','categories'=>'name','features'=>'title','gallery'=>'title','reviews'=>'name'] as $table=>$field) DB::connection()->exec("ALTER TABLE $table MODIFY $field TEXT NOT NULL");

$itemColumns = array_column(DB::all('SHOW COLUMNS FROM order_items'), 'Field');
if (!in_array('name_translations', $itemColumns, true)) DB::connection()->exec('ALTER TABLE order_items ADD name_translations TEXT NULL');
