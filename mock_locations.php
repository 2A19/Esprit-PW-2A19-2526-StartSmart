<?php
require_once 'config/Database.php';

$db = new Database();
// Connection automatically syncs schema
$conn = $db->getConnection();

// Mock data: A list of global tech hubs with latitude/longitude
$mockLocations = [
    ['city' => 'San Francisco', 'country' => 'USA', 'lat' => 37.7749, 'lng' => -122.4194],
    ['city' => 'London', 'country' => 'UK', 'lat' => 51.5074, 'lng' => -0.1278],
    ['city' => 'Berlin', 'country' => 'Germany', 'lat' => 52.5200, 'lng' => 13.4050],
    ['city' => 'Singapore', 'country' => 'Singapore', 'lat' => 1.3521, 'lng' => 103.8198],
    ['city' => 'Paris', 'country' => 'France', 'lat' => 48.8566, 'lng' => 2.3522],
    ['city' => 'Tel Aviv', 'country' => 'Israel', 'lat' => 32.0853, 'lng' => 34.7818],
    ['city' => 'Tokyo', 'country' => 'Japan', 'lat' => 35.6762, 'lng' => 139.6503],
    ['city' => 'Toronto', 'country' => 'Canada', 'lat' => 43.7001, 'lng' => -79.4163],
    ['city' => 'Sydney', 'country' => 'Australia', 'lat' => -33.8688, 'lng' => 151.2093],
    ['city' => 'Bangalore', 'country' => 'India', 'lat' => 12.9716, 'lng' => 77.5946],
    ['city' => 'Dubai', 'country' => 'UAE', 'lat' => 25.2048, 'lng' => 55.2708]
];

// Fetch all projects that don't have coordinates
$stmt = $conn->query("SELECT id FROM projet WHERE latitude IS NULL OR longitude IS NULL");
$projects = $stmt->fetchAll(PDO::FETCH_ASSOC);

$updateStmt = $conn->prepare("UPDATE projet SET city = ?, country = ?, latitude = ?, longitude = ? WHERE id = ?");

$count = 0;
foreach ($projects as $p) {
    // Pick a random location
    $loc = $mockLocations[array_rand($mockLocations)];
    
    // Add a slight random offset so markers in the same city don't perfectly overlap
    $latOffset = (mt_rand(-50, 50) / 1000);
    $lngOffset = (mt_rand(-50, 50) / 1000);
    
    $updateStmt->execute([
        $loc['city'],
        $loc['country'],
        $loc['lat'] + $latOffset,
        $loc['lng'] + $lngOffset,
        $p['id']
    ]);
    $count++;
}

echo "Schema synced and $count projects updated with mock locations.\n";
