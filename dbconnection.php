<?php
//u env.php se nalaze varijable za spajanje na odgovarajući server
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
    /**
     * Funkcija koja vraća podatke o slici koja se nalazi u mysql bazi -> getImage.php
     */
    public function returnPicture($id)
    {
        $sql = "SELECT picture, pictureType FROM " . $this->tableName . " WHERE id=" . $id;
        $result = $this->conn->query($sql);
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            return $row;
        }
    }
    /**
     * Funkcija koja ispisuje sve borce nekom pripadajućom šablonom (u ovom slucaju bootstrap 4).
     */
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
    /**
     * Funkcija koja povećava broj pobjeda/gubitaka borca sa id-a ovisno o zadanim varijablama
     */
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
    /**
     * Funkcija koja prima id borca te vraca njegove podatke u polju, ako nema tog borca vraca null
     */
    public function getFighterData($id)
    {
        $sql = "SELECT name, age, info, wins, loss FROM " . $this->tableName . " WHERE id = '" . $id . "';";
        $result = $this->conn->query($sql);
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            return $row;
        } else return null;
    }
    /**
     * Funkcija koja prima sve potrebne podatke borca te umece u bazu novog borca. $picture se odnosi na $_FILES tamo di se poziva.
     */
    public function insertFighter($name, $age, $info, $wins, $loss, $picture)
    {
        $pictureData = addslashes(file_get_contents($picture["picture_file"]["tmp_name"]));
        $pictureProperties = getimagesize($picture["picture_file"]["tmp_name"]);
        $sql = "INSERT INTO " . $this->tableName . " (name, age, info, wins, loss, picture, pictureType) VALUES ('" . $name . "','" . $age . "','" . $info . "','" . $wins . "','" . $loss . "','" . $pictureData . "','" . $pictureProperties["mime"] . "');";
        //echo $sql;
        $this->conn->query($sql);
        //ovo je potrebno jer clearDB ima increment od 10 naspram 1, takvo ponasanje ce pokidati funkcionalnost data-info dijela, ovako se cijela tablica ocisti
        $sqlCleanUp = "SET @count = 0;
        UPDATE " . $this->tableName . " SET id = @count := @count + 1;
        ALTER TABLE ". $this->tableName ." AUTO_INCREMENT = 1;";
        $this->conn->multi_query($sqlCleanUp);
    }
    /**
     * Funkcija koja prima id i sve podatke borca kojemu se trebaju mijenjati podatci. Ovisno o pictureCheck mijenjat ce i sliku ili ne.
     */
    public function editFighter($id, $name, $age, $info, $wins, $loss, $picture, $pictureCheck)
    {
        if ($pictureCheck) {
            $pictureData = addslashes(file_get_contents($picture["picture_file"]["tmp_name"]));
            $pictureProperties = getimagesize($picture["picture_file"]["tmp_name"]);
            $sql = "UPDATE " . $this->tableName . " SET name = '" . $name . "', age = '" . $age . "', info = '" . $info . "', wins = '" . $wins . "', loss = '" . $loss . "', picture = '" . $pictureData . "', pictureType = '" . $pictureProperties["mime"] . "' WHERE id = '" . $id . "';";
            //echo $sql;
            $this->conn->query($sql);
        } else {
            $sql = "UPDATE " . $this->tableName . " SET name = '" . $name . "', age = '" . $age . "', info = '" . $info . "', wins = '" . $wins . "', loss = '" . $loss . "' WHERE id = '" . $id . "';";
            //echo $sql;
            $this->conn->query($sql);
        }
    }
    /**
     * Funkcija koja prima id borca kojega se mora obrisati. Zato sto Javascript koristi data-info moraju se za svaki slucaj ponovno sloziti svi borci natrag po indeksu pocevsi od 1. Dodatno se mora auto increment promijeniti da pocinje od 1, sto ce kod MySQL automatski postaviti na MAX(id)+1.
     */
    public function deleteFighter($id)
    {
        $sql = "DELETE FROM " . $this->tableName . " WHERE id='" . $id . "';";
        //poredaj ponovno da se lijepo poslozi sve za data-info, nema toliko podataka da ce utjecati na performanse
        $sqlCleanUp = "SET @count = 0;
        UPDATE " . $this->tableName . " SET id = @count := @count + 1;
        ALTER TABLE ". $this->tableName ." AUTO_INCREMENT = 1;";
        //echo $sql;
        $this->conn->query($sql);
        $this->conn->multi_query($sqlCleanUp);
    }
}
