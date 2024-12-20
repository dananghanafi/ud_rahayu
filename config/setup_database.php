<?php
require_once 'mongodb.php';

try {
    $database = connectToMongoDB();

    // Create collections if they don't exist
    $collections = [
        'users' => [
            ['username' => 1],  // Index on username
            ['email' => 1]      // Index on email
        ],
        'products' => [
            ['name' => 1],      // Index on product name
            ['category' => 1]   // Index on category
        ],
        'carts' => [
            ['user_id' => 1]    // Index on user_id
        ],
        'orders' => [
            ['user_id' => 1],   // Index on user_id
            ['order_id' => 1]   // Index on order_id
        ],
        'notifications' => [
            ['user_id' => 1],   // Index on user_id
            ['created_at' => -1] // Index on created_at (descending)
        ]
    ];

    foreach ($collections as $collectionName => $indexes) {
        // Create collection
        if (!in_array($collectionName, $database->listCollectionNames())) {
            $database->createCollection($collectionName);
            echo "Collection '$collectionName' created successfully.<br>";
        }

        // Create indexes
        $collection = $database->$collectionName;
        foreach ($indexes as $index) {
            $collection->createIndex($index);
        }
    }

    // Insert sample data for testing
    // Sample products
    $products = [
        [
            'name' => 'Espresso',
            'description' => 'Strong black coffee in small serving',
            'price' => 18000,
            'stock' => 100,
            'category' => 'Kopi',
            'image' => '/assets/images/products/espresso.jpg',
            'created_at' => new MongoDB\BSON\UTCDateTime(),
            'updated_at' => new MongoDB\BSON\UTCDateTime()
        ],
        [
            'name' => 'Cappuccino',
            'description' => 'Espresso with steamed milk foam',
            'price' => 25000,
            'stock' => 100,
            'category' => 'Kopi',
            'image' => '/assets/images/products/cappuccino.jpg',
            'created_at' => new MongoDB\BSON\UTCDateTime(),
            'updated_at' => new MongoDB\BSON\UTCDateTime()
        ],
        [
            'name' => 'Green Tea Latte',
            'description' => 'Japanese green tea with milk',
            'price' => 23000,
            'stock' => 50,
            'category' => 'Teh',
            'image' => '/assets/images/products/green-tea-latte.jpg',
            'created_at' => new MongoDB\BSON\UTCDateTime(),
            'updated_at' => new MongoDB\BSON\UTCDateTime()
        ],
        [
            'name' => 'Croissant',
            'description' => 'Buttery, flaky pastry',
            'price' => 15000,
            'stock' => 20,
            'category' => 'Makanan',
            'image' => '/assets/images/products/croissant.jpg',
            'created_at' => new MongoDB\BSON\UTCDateTime(),
            'updated_at' => new MongoDB\BSON\UTCDateTime()
        ]
    ];

    foreach ($products as $product) {
        $database->products->insertOne($product);
    }
    echo "Sample products inserted successfully.<br>";

    // Create default users if not exist
    $defaultUsers = [
        [
            'username' => 'admin',
            'password' => 'admin123',
            'email' => 'admin@coffeeshop.com',
            'role' => 'admin'
        ],
        [
            'username' => 'user',
            'password' => 'user123',
            'email' => 'user@coffeeshop.com',
            'role' => 'customer'
        ],
        [
            'username' => 'kasir',
            'password' => 'kasir123',
            'email' => 'kasir@coffeeshop.com',
            'role' => 'kasir'
        ]
    ];

    foreach ($defaultUsers as $userData) {
        $existingUser = $database->users->findOne(['username' => $userData['username']]);
        if (!$existingUser) {
            $database->users->insertOne([
                'username' => $userData['username'],
                'password' => password_hash($userData['password'], PASSWORD_DEFAULT),
                'email' => $userData['email'],
                'role' => $userData['role'],
                'created_at' => new MongoDB\BSON\UTCDateTime(),
                'updated_at' => new MongoDB\BSON\UTCDateTime()
            ]);
            echo "User '{$userData['username']}' created successfully.<br>";
        }
    }

    echo "<br>Database setup completed successfully!<br>";
    echo "<br>Default login credentials:<br>";
    foreach ($defaultUsers as $user) {
        echo "<br>{$user['role']}:<br>";
        echo "Username: {$user['username']}<br>";
        echo "Password: {$user['password']}<br>";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
} 