<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: *');
header('Access-Control-Allow-Methods: *');

if(isset($_GET["action"]) && $_GET["action"] == "getNests") {
    // Dummy data for nests
    $nests = [
        ["id" => 1, "owner" => "Player1", "latitude" => 52.5200, "longitude" => 13.4050],
        ["id" => 2, "owner" => "Player2", "latitude" => 48.8566, "longitude" => 2.3522],
        ["id" => 3, "owner" => "Player3", "latitude" => 51.5074, "longitude" => -0.1278],
    ];

    header('Content-Type: application/json');
    echo json_encode($nests);
    exit();
}