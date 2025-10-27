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

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET["action"]) && $_GET["action"] == "leaderboard") {
    try {
        $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 50;
        $offset = isset($_GET['offset']) ? intval($_GET['offset']) : 0;
        
        $query = "
            SELECT 
                l.id,
                l.user_id,
                l.username,
                l.total_points,
                l.nest_level,
                l.eggs,
                l.decorations,
                l.highscore,
                l.games_played,
                l.last_updated,
                u.is_online,
                (SELECT COUNT(*) + 1 FROM wg_leaderboard WHERE total_points > l.total_points) as `rank`
            FROM wg_leaderboard l
            JOIN wg_users u ON l.user_id = u.id
            ORDER BY l.total_points DESC
            LIMIT ? OFFSET ?
        ";
        
        $stmt = mysqli_prepare($con, $query);
        mysqli_stmt_bind_param($stmt, 'ii', $limit, $offset);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        $leaderboard = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $leaderboard[] = $row;
        }
        
        $countResult = mysqli_query($con, "SELECT COUNT(*) as total FROM wg_leaderboard");
        $totalCount = mysqli_fetch_assoc($countResult)['total'];
        
        echo json_encode([
            'success' => true,
            'data' => $leaderboard,
            'total' => $totalCount,
            'limit' => $limit,
            'offset' => $offset
        ]);
        
    } catch(Exception $e) {
        http_response_code(500);
        echo json_encode([
            'error' => true,
            'message' => 'Failed to fetch leaderboard: ' . $e->getMessage()
        ]);
    }
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET["action"]) && $_GET["action"] == "player") {
    try {
        $username = isset($_GET['username']) ? mysqli_real_escape_string($con, $_GET['username']) : '';
        
        if (empty($username)) {
            throw new Exception('Username is required');
        }
        
        $query = "
            SELECT 
                l.*,
                u.is_online,
                u.last_login,
                u.created_at,
                (SELECT COUNT(*) + 1 FROM wg_leaderboard WHERE total_points > l.total_points) as `rank`,
                (SELECT COUNT(*) FROM wg_game_sessions WHERE user_id = l.user_id) as total_games
            FROM wg_leaderboard l
            JOIN wg_users u ON l.user_id = u.id
            WHERE l.username = ?
        ";
        
        $stmt = mysqli_prepare($con, $query);
        mysqli_stmt_bind_param($stmt, 's', $username);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $player = mysqli_fetch_assoc($result);
        
        if (!$player) {
            throw new Exception('Player not found');
        }
        
        echo json_encode([
            'success' => true,
            'data' => $player
        ]);
        
    } catch(Exception $e) {
        http_response_code(404);
        echo json_encode([
            'error' => true,
            'message' => $e->getMessage()
        ]);
    }
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] == 'update_leaderboard') {
    try {
        $username = isset($_POST['username']) ? mysqli_real_escape_string($con, $_POST['username']) : '';
        $totalPoints = isset($_POST['total_points']) ? intval($_POST['total_points']) : 0;
        $nestLevel = isset($_POST['nest_level']) ? intval($_POST['nest_level']) : 0;
        $eggs = isset($_POST['eggs']) ? intval($_POST['eggs']) : 0;
        $decorations = isset($_POST['decorations']) ? intval($_POST['decorations']) : 0;
        $highscore = isset($_POST['highscore']) ? intval($_POST['highscore']) : 0;
        $gamesPlayed = isset($_POST['games_played']) ? intval($_POST['games_played']) : 0;
        
        if (empty($username)) {
            throw new Exception('Username is required');
        }

        $userQuery = "INSERT INTO wg_users (username) VALUES (?)
                      ON DUPLICATE KEY UPDATE last_login = CURRENT_TIMESTAMP, is_online = TRUE";
        $stmt = mysqli_prepare($con, $userQuery);
        mysqli_stmt_bind_param($stmt, 's', $username);
        mysqli_stmt_execute($stmt);
        
        $userIdQuery = "SELECT id FROM wg_users WHERE username = ?";
        $stmt = mysqli_prepare($con, $userIdQuery);
        mysqli_stmt_bind_param($stmt, 's', $username);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $user = mysqli_fetch_assoc($result);
        $userId = $user['id'];
        
        $leaderboardQuery = "
            INSERT INTO wg_leaderboard (
                user_id, username, total_points, nest_level, eggs, decorations, highscore, games_played
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
                username = VALUES(username),
                total_points = VALUES(total_points),
                nest_level = VALUES(nest_level),
                eggs = VALUES(eggs),
                decorations = VALUES(decorations),
                highscore = GREATEST(highscore, VALUES(highscore)),
                games_played = VALUES(games_played),
                last_updated = CURRENT_TIMESTAMP
        ";
        
        $stmt = mysqli_prepare($con, $leaderboardQuery);
        mysqli_stmt_bind_param($stmt, 'isiiiiii', 
            $userId, $username, $totalPoints, $nestLevel, $eggs, $decorations, $highscore, $gamesPlayed
        );
        mysqli_stmt_execute($stmt);
        
        $getQuery = "
            SELECT 
                l.*,
                (SELECT COUNT(*) + 1 FROM wg_leaderboard WHERE total_points > l.total_points) as `rank`
            FROM wg_leaderboard l
            WHERE l.user_id = ?
        ";
        
        $stmt = mysqli_prepare($con, $getQuery);
        mysqli_stmt_bind_param($stmt, 'i', $userId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $updatedPlayer = mysqli_fetch_assoc($result);
        
        echo json_encode([
            'success' => true,
            'message' => 'Leaderboard updated successfully',
            'data' => $updatedPlayer
        ]);
        
    } catch(Exception $e) {
        http_response_code(500);
        echo json_encode([
            'error' => true,
            'message' => 'Failed to update leaderboard: ' . $e->getMessage()
        ]);
    }
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] == 'rename_user') {
    try {
        $userId = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
        $oldUsername = isset($_POST['old_username']) ? mysqli_real_escape_string($con, $_POST['old_username']) : '';
        $newUsername = isset($_POST['new_username']) ? mysqli_real_escape_string($con, $_POST['new_username']) : '';
        
        if (empty($newUsername)) {
            throw new Exception('New username is required');
        }
        
        $checkQuery = "SELECT id FROM wg_users WHERE username = ? AND id != ?";
        $stmt = mysqli_prepare($con, $checkQuery);
        mysqli_stmt_bind_param($stmt, 'si', $newUsername, $userId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if (mysqli_num_rows($result) > 0) {
            throw new Exception('Username already taken');
        }
        
        $updateUserQuery = "UPDATE wg_users SET username = ?, last_login = CURRENT_TIMESTAMP WHERE id = ?";
        $stmt = mysqli_prepare($con, $updateUserQuery);
        mysqli_stmt_bind_param($stmt, 'si', $newUsername, $userId);
        mysqli_stmt_execute($stmt);
        
        $updateLeaderboardQuery = "UPDATE wg_leaderboard SET username = ?, last_updated = CURRENT_TIMESTAMP WHERE user_id = ?";
        $stmt = mysqli_prepare($con, $updateLeaderboardQuery);
        mysqli_stmt_bind_param($stmt, 'si', $newUsername, $userId);
        mysqli_stmt_execute($stmt);
        
        echo json_encode([
            'success' => true,
            'message' => 'Username updated successfully'
        ]);
        
    } catch(Exception $e) {
        http_response_code(500);
        echo json_encode([
            'error' => true,
            'message' => $e->getMessage()
        ]);
    }
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] == 'save_game') {
    try {
        $username = isset($_POST['username']) ? mysqli_real_escape_string($con, $_POST['username']) : '';
        $score = isset($_POST['score']) ? intval($_POST['score']) : 0;
        $leavesCollected = isset($_POST['leaves_collected']) ? intval($_POST['leaves_collected']) : 0;
        $duration = isset($_POST['duration']) ? intval($_POST['duration']) : 0;
        
        if (empty($username)) {
            throw new Exception('Username is required');
        }
        
        $userQuery = "SELECT id FROM wg_users WHERE username = ?";
        $stmt = mysqli_prepare($con, $userQuery);
        mysqli_stmt_bind_param($stmt, 's', $username);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $user = mysqli_fetch_assoc($result);
        
        if (!$user) {
            throw new Exception('User not found');
        }
        
        $userId = $user['id'];
        
        $sessionQuery = "
            INSERT INTO wg_game_sessions (user_id, score, leaves_collected, duration)
            VALUES (?, ?, ?, ?)
        ";
        
        $stmt = mysqli_prepare($con, $sessionQuery);
        mysqli_stmt_bind_param($stmt, 'iiii', $userId, $score, $leavesCollected, $duration);
        mysqli_stmt_execute($stmt);
        
        echo json_encode([
            'success' => true,
            'message' => 'Game session saved',
            'session_id' => mysqli_insert_id($con)
        ]);
        
    } catch(Exception $e) {
        http_response_code(500);
        echo json_encode([
            'error' => true,
            'message' => 'Failed to save game session: ' . $e->getMessage()
        ]);
    }
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET["action"]) && $_GET["action"] == "map_players") {
    try {
        $query = "
            SELECT 
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
        
        $players = [];
        while ($row = mysqli_fetch_assoc($result)) {
            // Generate random position for each player on map
            $row['x'] = rand(150, 1050);
            $row['y'] = rand(150, 650);
            $players[] = $row;
        }
        
        echo json_encode([
            'success' => true,
            'data' => $players
        ]);
        
    } catch(Exception $e) {
        http_response_code(500);
        echo json_encode([
            'error' => true,
            'message' => 'Failed to fetch map players: ' . $e->getMessage()
        ]);
    }
    exit();
}