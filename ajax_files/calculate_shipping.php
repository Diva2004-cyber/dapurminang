<?php
include "../connect.php";
include "../Includes/functions/functions.php";

// Restaurant's fixed location (Jalan Siliwangi No. 24 - UNSIL)
$restaurant_lat = -7.3275; // Example latitude
$restaurant_lng = 108.2207; // Example longitude

// Function to calculate distance using Haversine formula
function calculateDistance($lat1, $lon1, $lat2, $lon2) {
    $earth_radius = 6371; // Earth's radius in kilometers
    
    $dLat = deg2rad($lat2 - $lat1);
    $dLon = deg2rad($lon2 - $lon1);
    
    $a = sin($dLat/2) * sin($dLat/2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon/2) * sin($dLon/2);
    $c = 2 * asin(sqrt($a));
    $distance = $earth_radius * $c;
    
    return $distance;
}

// Function to check if current time is within rush hour
function isRushHour() {
    $current_time = date('H:i:s');
    $rush_hours = [
        ['start' => '07:00:00', 'end' => '09:00:00'],
        ['start' => '16:00:00', 'end' => '18:00:00']
    ];
    
    foreach ($rush_hours as $period) {
        if ($current_time >= $period['start'] && $current_time <= $period['end']) {
            return true;
        }
    }
    return false;
}

// Function to calculate shipping cost
function calculateShippingCost($distance, $is_rush_hour = false, $is_difficult_area = false, $is_heavy_load = false) {
    global $con;
    
    // Get base shipping cost from zones
    $stmt = $con->prepare("SELECT * FROM shipping_zones WHERE ? BETWEEN min_distance AND max_distance AND is_active = 1");
    $stmt->execute([$distance]);
    $zone = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$zone) {
        return ['error' => 'Lokasi pengiriman terlalu jauh atau tidak terjangkau'];
    }
    
    // Calculate base cost
    $base_cost = $zone['base_cost'];
    $cost_per_km = $zone['cost_per_km'];
    $total_cost = $base_cost + ($distance * $cost_per_km);
    
    // Add surcharges
    $surcharges = [];
    
    // Rush hour surcharge
    if ($is_rush_hour) {
        $stmt = $con->prepare("SELECT surcharge_amount FROM shipping_surcharges WHERE surcharge_type = 'rush_hour' AND is_active = 1");
        $stmt->execute();
        $rush_hour_surcharge = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($rush_hour_surcharge) {
            $total_cost += $rush_hour_surcharge['surcharge_amount'];
            $surcharges[] = ['type' => 'Rush Hour', 'amount' => $rush_hour_surcharge['surcharge_amount']];
        }
    }
    
    // Difficult area surcharge
    if ($is_difficult_area) {
        $stmt = $con->prepare("SELECT surcharge_amount FROM shipping_surcharges WHERE surcharge_type = 'difficult_area' AND is_active = 1");
        $stmt->execute();
        $difficult_area_surcharge = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($difficult_area_surcharge) {
            $total_cost += $difficult_area_surcharge['surcharge_amount'];
            $surcharges[] = ['type' => 'Area Sulit', 'amount' => $difficult_area_surcharge['surcharge_amount']];
        }
    }
    
    // Heavy load surcharge
    if ($is_heavy_load) {
        $stmt = $con->prepare("SELECT surcharge_amount FROM shipping_surcharges WHERE surcharge_type = 'heavy_load' AND is_active = 1");
        $stmt->execute();
        $heavy_load_surcharge = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($heavy_load_surcharge) {
            $total_cost += $heavy_load_surcharge['surcharge_amount'];
            $surcharges[] = ['type' => 'Beban Berat', 'amount' => $heavy_load_surcharge['surcharge_amount']];
        }
    }
    
    return [
        'distance' => round($distance, 2),
        'base_cost' => $base_cost,
        'cost_per_km' => $cost_per_km,
        'surcharges' => $surcharges,
        'total_cost' => round($total_cost, 2)
    ];
}

// Handle AJAX request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $response = [
        'distance' => 0,
        'base_cost' => 15000,
        'cost_per_km' => 0,
        'surcharges' => [],
        'total_cost' => 15000
    ];
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
} 