<?php
require_once 'env.php';

class DatabaseConnection
{
    protected $conn;
    private $tableName = "members";

    public function __construct()
    {
        $env = new connectionSettings();
        $this->conn = new mysqli($env->serverName, $env->userName, $env->password, $env->dbName);
        if ($this->conn->connect_error) {
            die("Connection failed: " . $this->conn->connection_error);
        }
    }

    public function returnPicture($id)
    {
        $sql = "SELECT picture FROM " . $this->tableName . " WHERE id=" . $id;
        $result = $this->conn->query($sql);
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            return $row;
        }
    }

    public function populateFighters()
    {
        $sql = "SELECT *  FROM " . $this->tableName;
        $result = $this->conn->query($sql);
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $imgsource = "<img src=\"getImage.php?id=" . $row["id"] . "\" alt=\"Figter Box " . $row["id"] . "\" width=\"150\" height=\"150\" />";
                $editButton = "<a class='btn btn-secondary' href='editFighter.php?id=" . $row["id"] . "'>Edit fighter</a>";
                $final =
                    "<div class=\"col-lg-4 mb-1\">
                        <div class=\"fighter-box\"
                        data-info = '{
                            \"id\": " . $row["id"] . ",
                            \"name\": \"" . $row["name"] . "\" ,
                            \"age\" : " . $row["age"] . ",
                            \"catInfo\": \"" . $row["info"] . "\",
                            \"record\" : {
                                \"wins\":  " . $row["wins"] . ",
                                \"loss\": " . $row["loss"] . "
                            }
                        }'> " . $imgsource . $editButton . "
                        </div>
                    </div>";
                echo $final;
            }
        } else {
            echo "0 results";
        }
    }

    public function updateFighterWL($id, $result)
    {
        $sql = "";
        if ($result == "win") {
            $sql = "UPDATE " . $this->tableName . " SET wins = wins + 1 WHERE id='" . $id . "'";
            $this->conn->query($sql);
        } else if ($result == "loss") {
            $sql = "UPDATE " . $this->tableName . " SET loss = loss + 1 WHERE id='" . $id . "'";
            $this->conn->query($sql);
        }
        var_dump($sql);
    }

    public function getFighterData($id){
        $sql = "SELECT name, age, info, wins, loss FROM " . $this->tableName . " WHERE id = '" . $id . "';";
        $result = $this->conn->query($sql);
        if($result->num_rows > 0){
            $row = $result->fetch_assoc();
            return $row;
        }
        else return null;
    }

    public function insertFighter($name, $age, $info, $wins, $loss, $picture)
    {
        $pictureData = addslashes(file_get_contents($picture["picture_file"]["tmp_name"]));
        $sql = "INSERT INTO " . $this->tableName . " (name, age, info, wins, loss, picture) VALUES ('" . $name . "','" . $age . "','" . $info . "','" . $wins . "','" . $loss . "','" . $pictureData . "');";
        //echo $sql;
        $this->conn->query($sql);
    }

    public function editFighter($id, $name, $age, $info, $wins, $loss, $picture, $pictureCheck){
        if($pictureCheck){
            $pictureData = addslashes(file_get_contents($picture["picture_file"]["tmp_name"]));
            $sql = "UPDATE " . $this->tableName . " SET name = '" . $name . "', age = '" . $age . "', info = '" . $info . "', wins = '" . $wins . "', loss = '" . $loss . "', picture = '" . $pictureData . "' WHERE id = '" . $id . "';";
            //echo $sql;
            $this->conn->query($sql);
        }
        else{
            $sql = "UPDATE " . $this->tableName . " SET name = '" . $name . "', age = '" . $age . "', info = '" . $info . "', wins = '" . $wins . "', loss = '" . $loss . "' WHERE id = '" . $id . "';";
            //echo $sql;
            $this->conn->query($sql);
        }
    }
}
