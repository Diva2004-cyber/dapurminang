<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json');

include '../connect.php';

function getSurcharge($con, $type) {
    $stmt = $con->prepare("SELECT surcharge_amount FROM shipping_surcharges WHERE surcharge_type = ? AND is_active = 1");
    $stmt->execute([$type]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row ? floatval($row['surcharge_amount']) : 0;
}

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $distance = isset($_POST['distance']) ? floatval($_POST['distance']) : 0;
        $is_difficult_area = !empty($_POST['is_difficult_area']);
        $is_heavy_load = !empty($_POST['is_heavy_load']);
        $is_rush_hour = !empty($_POST['is_rush_hour']);
        $is_bad_weather = !empty($_POST['is_bad_weather']);

        // Get shipping zone
        $stmt = $con->prepare("SELECT * FROM shipping_zones WHERE ? BETWEEN min_distance AND max_distance AND is_active = 1");
        $stmt->execute([$distance]);
        $zone = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$zone) {
            echo json_encode(['error' => 'Jarak di luar jangkauan layanan.']);
            exit;
        }
        $base_cost = floatval($zone['base_cost']);
        $cost_per_km = floatval($zone['cost_per_km']);
        $total_cost = $base_cost + ($distance * $cost_per_km);
        $surcharges = [];

        // Surcharges
        if ($is_rush_hour) {
            $amount = getSurcharge($con, 'rush_hour');
            if ($amount > 0) {
                $total_cost += $amount;
                $surcharges[] = ['type' => 'Jam Sibuk', 'amount' => $amount];
            }
        }
        if ($is_bad_weather) {
            $amount = getSurcharge($con, 'bad_weather');
            if ($amount > 0) {
                $total_cost += $amount;
                $surcharges[] = ['type' => 'Cuaca Buruk', 'amount' => $amount];
            }
        }
        if ($is_difficult_area) {
            $amount = getSurcharge($con, 'difficult_area');
            if ($amount > 0) {
                $total_cost += $amount;
                $surcharges[] = ['type' => 'Area Sulit', 'amount' => $amount];
            }
        }
        if ($is_heavy_load) {
            $amount = getSurcharge($con, 'heavy_load');
            if ($amount > 0) {
                $total_cost += $amount;
                $surcharges[] = ['type' => 'Beban Berat', 'amount' => $amount];
            }
        }

        echo json_encode([
            'distance' => round($distance, 2),
            'base_cost' => $base_cost,
            'cost_per_km' => $cost_per_km,
            'surcharges' => $surcharges,
            'total_cost' => round($total_cost),
        ]);
        exit;
    } else {
        echo json_encode(['error' => 'Invalid request method']);
        exit;
    }
} catch (Throwable $e) {
    echo json_encode(['error' => 'PHP Error: ' . $e->getMessage()]);
    exit;
} 