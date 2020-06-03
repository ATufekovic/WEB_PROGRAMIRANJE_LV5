<?php
$erroDesc = "";
$infoDesc = "";

/**
 * Prima neke podatke na ulazu pa ih čisti od nepotrebnih razmaka i spriječava jednostavne napade koristeći htmlspecialchars()
 */
function test_input($data)
{
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}
/**
 * Funkcija koja se poziva kad nije odabran checkbox za promjenu slike, pravi provjeru kao kod dodavanja borca ali bez dodanog dijela za obradu slike, šalje jednostavno null glavnoj funkciji za mijenjanje
 */
function editWithoutPicture($erroDesc, $infoDesc)
{
    $name = "";
    $nameFlag = $ageFlag = $infoFlag = $winsFlag = $lossFlag = false;
    $age = $wins = $loss = 0;
    $info = "";
    //provjeri sve ulazne podatke
    if (empty($_POST["name"])) {
        $erroDesc = $erroDesc . "Name is required <br>";
    } else {
        $name = test_input($_POST["name"]);
        $nameFlag = true;
    }
    //provjeravaju se godine dali su iznad nule
    if (empty($_POST["age"])) {
        $erroDesc = $erroDesc . "Age is required <br>";
    } else {
        $age = test_input($_POST["age"]);
        if ($age <= 0) {
            $erroDesc = $erroDesc . "Age must be above 0 <br>";
            $ageFlag = false;
        } else {
            $ageFlag = true;
        }
    }

    if (empty($_POST["info"])) {
        $erroDesc = $erroDesc . "Info is required <br>";
    } else {
        $info = test_input($_POST["info"]);
        $infoFlag = true;
    }
    //wins i loss nije važno dali je postavljeno, default je nula u svakom slucaju
    if (empty($_POST["wins"])) {
        $wins = 0;
        $winsFlag = true;
    } else {
        $wins = test_input($_POST["wins"]);
        $winsFlag = true;
    }

    if (empty($_POST["loss"])) {
        $loss = 0;
        $lossFlag = true;
    } else {
        $loss = test_input($_POST["loss"]);
        $lossFlag = true;
    }

    if ($nameFlag && $ageFlag && $infoFlag && $winsFlag && $lossFlag) {
        //nastavi sa obradom borca ako su osnovni podatci u redu
        require_once "dbconnection.php";
        $dbconn = new DatabaseConnection();
        //pozovi glavnu funkciju i njoj predaj provjerene podatke
        $dbconn->editFighter($_GET["id"], $name, $age, $info, $wins, $loss, null, false);
        header("Location: ./index.php");
        
    } else {
        $erroDesc = $erroDesc . "Error in given data<br>";
    }
}
/**
 * Funkcija koja se poziva kad jest odabran checkbox za promjenu slike, pravi provjeru kao kod dodavanja borca zajedno sa dodanim dijela za obradu slike, šalje sve glavnoj funkciji za mijenjanje
 */
function editWithPicture($erroDesc, $infoDesc)
{
    $name = "";
    $nameFlag = $ageFlag = $infoFlag = $winsFlag = $lossFlag = false;
    $age = $wins = $loss = 0;
    $info = "";
    //provjeri sve ulazne podatke
    if (empty($_POST["name"])) {
        $erroDesc = $erroDesc . "Name is required <br>";
    } else {
        $name = test_input($_POST["name"]);
        $nameFlag = true;
    }
    //provjeravaju se godine dali su iznad nule
    if (empty($_POST["age"])) {
        $erroDesc = $erroDesc . "Age is required <br>";
    } else {
        $age = test_input($_POST["age"]);
        if ($age <= 0) {
            $erroDesc = $erroDesc . "Age must be above 0 <br>";
            $ageFlag = false;
        } else {
            $ageFlag = true;
        }
    }

    if (empty($_POST["info"])) {
        $erroDesc = $erroDesc . "Info is required <br>";
    } else {
        $info = test_input($_POST["info"]);
        $infoFlag = true;
    }
    //wins i loss nije važno dali je postavljeno, default je nula u svakom slucaju
    if (empty($_POST["wins"])) {
        $wins = 0;
        $winsFlag = true;
    } else {
        $wins = test_input($_POST["wins"]);
        $winsFlag = true;
    }

    if (empty($_POST["loss"])) {
        $loss = 0;
        $lossFlag = true;
    } else {
        $loss = test_input($_POST["loss"]);
        $lossFlag = true;
    }

    if ($nameFlag && $ageFlag && $infoFlag && $winsFlag && $lossFlag) {
        //nastavi sa obradom borca ako su osnovni podatci u redu
        //obradi sliku
        $target_dir = "uploads/"; //za testiranje, db ce primati sliku direktno
        $uploadOK = true;
        $target_file = $target_dir . basename($_FILES["picture_file"]["name"]);
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        //provjeri dali postoji zapravo datoteka
        if (isset($_FILES["picture_file"])) {
            $check = getimagesize($_FILES["picture_file"]["tmp_name"]);
            if ($check !== false) {
                //datoteka je slika i nastavi
                $uploadOK = true;
            } else {
                //datoteka nije slika
                $erroDesc = $erroDesc . "File is not a picture<br>";
                $uploadOK = false;
            }
        }

        //provjeri velicinu datoteke, 60KB je cilj
        if ($_FILES["picture_file"]["size"] > 60000) {
            //datoteka je prevelika
            $erroDesc = $erroDesc . "File is too big <br>";
            $uploadOK = false;
        }

        //provjeri tip datoteke
        if ($imageFileType != "jpg" && $imageFileType != "jpeg" && $imageFileType != "png") {
            //datoteka nije jpg/jpeg/png format
            $erroDesc = $erroDesc . "File is not jpg/jpeg/png <br>";
            $uploadOK = false;
        }

        if (!$uploadOK) {
            //datoteka nije pravilna, nista se ne događa
            $erroDesc = $erroDesc . "File is not a proper picture <br>";
        } else {
            $infoDesc = $infoDesc . "The file " . basename($_FILES["picture_file"]["name"]) . " has been uploaded<br>";
            require_once "dbconnection.php";
            $dbconn = new DatabaseConnection();
            //pozovi glavnu funkciju i njoj predaj provjerene podatke
            $dbconn->editFighter($_GET["id"], $name, $age, $info, $wins, $loss, $_FILES, true);
            header("Location: ./index.php");
        }
    } else {
        $erroDesc = $erroDesc . "Error in given data<br>";
    }
}
/**
 * Funkcija koja preuzima id borca iz $_GET varijable, inače ne radi ništa te šalje ju dalje glavnoj funkciji za brisanje boraca
 */
function deleteFighter(){
    if(isset($_GET["id"])){
        $id = $_GET["id"];
        require_once "dbconnection.php";
        $dbconn = new DatabaseConnection();
        $dbconn->deleteFighter($id);
        header("Location: ./index.php");
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if(isset($_POST["submit"])){
        if (isset($_POST["changePicture"])) {
            editWithPicture($erroDesc, $infoDesc);
        } else {
            editWithoutPicture($erroDesc, $infoDesc);
        }
    }
    else if(isset($_POST["delete"])){
        deleteFighter();
    }
    
}
require_once "dbconnection.php";
$dbconn = new DatabaseConnection();
$row = $dbconn->getFighterData($_GET["id"]);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit fighter</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous" />
</head>

<body>
    <div class="container">
        <h1>Edit fighter</h1>
        <div class="row">
            <div class="col">
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . "?id=" . $_GET["id"]; ?>" method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <div class="row">
                            <div class="col-md-3">
                                <label for="name">Name:</label>
                            </div>
                            <div class="col-md-9">
                                <input type="text" class="form-control" placeholder="name" id="name" name="name" <?php
                                                                                                                    if (isset($row)) {
                                                                                                                        echo "value='" . $row["name"] . "'";
                                                                                                                    }
                                                                                                                    ?>>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="row">
                            <div class="col-md-3">
                                <label for="age">Age:</label>
                            </div>
                            <div class="col-md-9">
                                <input type="number" class="form-control" placeholder="age" id="age" name="age" <?php
                                                                                                                if (isset($row)) {
                                                                                                                    echo "value='" . $row["age"] . "'";
                                                                                                                }
                                                                                                                ?>>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="row">
                            <div class="col-md-3">
                                <label for="info">Fighter info:</label>
                            </div>
                            <div class="col-md-9">
                                <textarea type="text" class="form-control" placeholder="Write something" id="info" name="info"><?php if (isset($row)){echo $row["info"];}?></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <div class="row">
                                    <div class="col-md-6">
                                        <label for="wins">Wins:</label>
                                    </div>
                                    <div class="col-md-6">
                                        <input type="number" class="form-control" placeholder="wins" id="wins" name="wins" <?php
                                                                                                                            if (isset($row)) {
                                                                                                                                echo "value='" . $row["wins"] . "'";
                                                                                                                            }
                                                                                                                            ?>>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <div class="row">
                                    <div class="col-md-6">
                                        <label for="loss">Losses:</label>
                                    </div>
                                    <div class="col-md-6">
                                        <input type="number" class="form-control" placeholder="loss" id="loss" name="loss" <?php
                                                                                                                            if (isset($row)) {
                                                                                                                                echo "value='" . $row["loss"] . "'";
                                                                                                                            }
                                                                                                                            ?>>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-check">
                                <label class="form-check-label" for="changePicture">
                                    <input type="checkbox" class="form-check-input" name="changePicture" id="changePicture">Change picture?
                                </label>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="custom-file">
                                <input type="file" class="custom-file-input" id="picture_file" name="picture_file">
                                <label class="custom-file-label" for="customFile">Choose picture (max. 60kB)</label>
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary mt-4" name="submit">Submit</button>
                    <button type="submit" class="btn btn-danger mt-4" name="delete">Delete fighter</button>
                </form>
            </div>
            <div class="col">
                <div class="container">
                    <p>Maximum name length is 50 characters.</p>
                    <p>Age must be a non-zero positive integer number.</p>
                    <p>Info must not be above 250 characters long.</p>
                    <p>Wins and losses must be zero or positive integer numbers.</p>
                    <p>The picture file size must be below 60kB in total size.</p>
                    <p class="text-warning"><?php echo $erroDesc ?></p>
                    <p class="text-info"><?php echo $infoDesc ?></p>
                </div>
            </div>
        </div>
        <div class="container d-flex flex-column align-items-center mb-4">
            <a href="./index.php" class="btn btn-info" role="button">Go back</a>
        </div>
    </div>
</body>

</html>