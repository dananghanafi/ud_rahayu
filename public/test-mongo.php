<?php
// Tampilkan semua error
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>MongoDB Extension Test</h1>";

// Cek apakah ekstensi MongoDB terinstal
if (extension_loaded('mongodb')) {
    echo "MongoDB extension is installed.<br>";
    echo "Version: " . phpversion('mongodb') . "<br>";
    
    // Coba koneksi ke MongoDB
    try {
        $client = new MongoDB\Driver\Manager("mongodb://localhost:27017");
        echo "Successfully connected to MongoDB!<br>";
        
        // Coba list database
        $command = new MongoDB\Driver\Command(['listDatabases' => 1]);
        $result = $client->executeCommand('admin', $command);
        $databases = current($result->toArray());
        
        echo "<h2>Available Databases:</h2>";
        foreach ($databases->databases as $db) {
            echo "- " . $db->name . "<br>";
        }
        
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage();
    }
} else {
    echo "MongoDB extension is NOT installed!";
} 