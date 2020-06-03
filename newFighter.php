<?php
$name = "";
$nameFlag = $ageFlag = $infoFlag = $winsFlag = $lossFlag = false;
$age = $wins = $loss = 0;
$info = "";
$picture = null;
$erroDesc = $infoDesc = "";
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

//ako je pozvana datoteka sa post-om nastavi sa radom
if ($_SERVER["REQUEST_METHOD"] == "POST") {
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
        //nastavi sa dodavanjem borca u bazu ako su osnovni podatci u redu
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
            $uplaodOK = false;
        }

        if (!$uploadOK) {
            //datoteka nije pravilna, nista se ne događa te upozori korisnika
            $erroDesc = $erroDesc . "File is not a proper picture <br>";
        } else {
            $infoDesc = $infoDesc . "The file " . basename($_FILES["picture_file"]["name"]) . " has been uploaded<br>";
            require_once "dbconnection.php";
            $dbconn = new DatabaseConnection();
            //pozovi glavnu funkciju i njoj predaj provjerene podatke
            $dbconn->insertFighter($name, $age, $info, $wins, $loss, $_FILES);
            $infoDesc = $infoDesc . "Fighter sent successfully<br>";
        }
    } else {
        $erroDesc = $erroDesc . "Error in given data<br>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New fighter</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous" />
</head>

<body>
    <div class="container">
        <h1>New fighter</h1>
        <div class="row">
            <div class="col"><!-- $_SERVER[PHP_SELF] znaci da ce samu sebe pozivati, time smanjujemo broj potrebnihdatoteka -->
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <div class="row">
                            <div class="col-md-3">
                                <label for="name">Name:</label>
                            </div>
                            <div class="col-md-9">
                                <input type="text" class="form-control" placeholder="name" id="name" name="name">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="row">
                            <div class="col-md-3">
                                <label for="age">Age:</label>
                            </div>
                            <div class="col-md-9">
                                <input type="number" class="form-control" placeholder="age" id="age" name="age">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="row">
                            <div class="col-md-3">
                                <label for="info">Fighter info:</label>
                            </div>
                            <div class="col-md-9">
                                <textarea type="text" class="form-control" placeholder="Write something" id="info" name="info"></textarea>
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
                                        <input type="number" class="form-control" placeholder="wins" id="wins" name="wins">
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
                                        <input type="number" class="form-control" placeholder="loss" id="loss" name="loss">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="custom-file">
                        <input type="file" class="custom-file-input" id="picture_file" name="picture_file">
                        <label class="custom-file-label" for="customFile">Choose picture (max. 60kB)</label>
                    </div>
                    <button type="submit" class="btn btn-primary mt-4">Submit</button>
                </form>
            </div>
            <div class="col">
                <div class="container">
                    <p>Maximum name length is 50 characters.</p>
                    <p>Age must be a non-zero positive integer number.</p>
                    <p>Info must not be above 250 characters long.</p>
                    <p>Wins and losses must be zero or positive integer numbers.</p>
                    <p>The picture file size must be below 60kB in total size.</p><!-- U ovom dijelu se korisniku javljaju informacije o postupku, ako se ništa ne dogodi u jednom onda se ništa ne pojavljuje -->
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