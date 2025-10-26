<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: *');
header('Access-Control-Allow-Methods: *');

if(isset($_GET["action"]) && $_GET["action"] == "leaderboard") {
    // Dummy data for leaderboard
    $leaderboard = [
        ["rank" => 1, "username" => "Player1", "points" => 1000],
        ["rank" => 2, "username" => "Player2", "points" => 800],
        ["rank" => 3, "username" => "Player3", "points" => 600],
    ];

    header('Content-Type: application/json');
    echo json_encode($leaderboard);
    exit();
}