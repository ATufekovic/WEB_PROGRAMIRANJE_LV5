<?php
//kad se datoteka pozove, dohvaća sliku i pruža ju u predstavljivom obliku kao url
require_once 'dbconnection.php';
$id = $_GET['id'];
$dbconn = new databaseConnection();
$row = $dbconn->returnPicture($id);

header("Content-type: " . $row["pictureType"]);
echo $row['picture'];
