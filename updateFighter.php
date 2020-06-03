<?php
//kad se datoteka pozove samo proslijedi podatke dalje glavnoj funkciji u dbconnection.php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    require_once 'dbconnection.php';
    $id = $_POST["id"];
    $result = $_POST["result"];
    $dbconn = new DatabaseConnection();

    $dbconn->updateFighterWL($id, $result);
}
