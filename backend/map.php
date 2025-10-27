<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/utils/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET["action"]) && $_GET["action"] == "getNests") {
    try {
        $query = "
            SELECT 
                l.id,
                l.username,
                l.total_points,
                l.nest_level,
                l.eggs,
                l.decorations,
                u.is_online,
                (SELECT COUNT(*) + 1 FROM wg_leaderboard WHERE total_points > l.total_points) as `rank`
            FROM wg_leaderboard l
            JOIN wg_users u ON l.user_id = u.id
            ORDER BY l.total_points DESC
        ";
        
        $result = mysqli_query($con, $query);
        
        $nests = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $nests[] = [
                'id' => $row['id'],
                'owner' => $row['username'],
                'totalPoints' => $row['total_points'],
                'nestLevel' => $row['nest_level'],
                'eggs' => $row['eggs'],
                'decorations' => $row['decorations'],
                'rank' => $row['rank'],
                'isOnline' => (bool)$row['is_online'],
                'x' => rand(150, 1050),
                'y' => rand(150, 650)
            ];
        }
        
        echo json_encode([
            'success' => true,
            'data' => $nests
        ]);
        
    } catch(Exception $e) {
        http_response_code(500);
        echo json_encode([
            'error' => true,
            'message' => 'Failed to fetch nests: ' . $e->getMessage()
        ]);
    }
    exit();
}