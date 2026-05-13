<?php
require 'config/Database.php';

$db = (new Database())->getConnection();

// Function to fetch coordinates
function get_coordinates($city, $country) {
    $query = urlencode($city . ', ' . $country);
    $url = "https://nominatim.openstreetmap.org/search?q={$query}&format=json&limit=1";
    
    $options = [
        "http" => [
            "header" => "User-Agent: StartSmartApp/1.0\r\n"
        ]
    ];
    $context = stream_context_create($options);
    $result = @file_get_contents($url, false, $context);
    
    if ($result) {
        $data = json_decode($result, true);
        if (!empty($data) && isset($data[0]['lat']) && isset($data[0]['lon'])) {
            return ['lat' => $data[0]['lat'], 'lng' => $data[0]['lon']];
        }
    }
    
    // Fallback to just city
    $query = urlencode($city);
    $url = "https://nominatim.openstreetmap.org/search?q={$query}&format=json&limit=1";
    $result = @file_get_contents($url, false, $context);
    if ($result) {
        $data = json_decode($result, true);
        if (!empty($data) && isset($data[0]['lat']) && isset($data[0]['lon'])) {
            return ['lat' => $data[0]['lat'], 'lng' => $data[0]['lon']];
        }
    }
    return false;
}

$stmt = $db->query('SELECT id, city, country FROM projet');
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $id = $row['id'];
    $city = $row['city'];
    $country = $row['country'];
    
    if (empty($city)) continue;
    
    echo "Updating project ID $id ($city, $country)...\n";
    $coords = get_coordinates($city, $country);
    
    if ($coords) {
        $lat = $coords['lat'];
        $lng = $coords['lng'];
        $updateStmt = $db->prepare('UPDATE projet SET latitude = ?, longitude = ? WHERE id = ?');
        $updateStmt->execute([$lat, $lng, $id]);
        echo " -> Success: Lat $lat, Lng $lng\n";
    } else {
        echo " -> Failed to geocode.\n";
    }
    
    // Respect Nominatim rate limit (1 req/sec)
    sleep(1);
}
echo "Done.\n";
