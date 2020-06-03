<?php
    require_once 'dbconnection.php';
    $id = $_GET['id'];
    $dbconn = new databaseConnection();
    $row = $dbconn->returnPicture($id);

    header("Content-type: image/jpeg");
    echo $row['picture'];
