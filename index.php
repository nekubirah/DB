<head>
    <link href="https://fonts.googleapis.com/css2?family=<FONT-NAME>&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.1.3/dist/css/bootstrap.min.css"
        integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
    <script src="https://kit.fontawesome.com/2af9ff65b1.js" crossorigin="anonymous"></script>
    <style>
    form {
        width: 500px;
        margin: 20px auto;
        text-align: center;
        padding: 20px;
    }

    label,
    input[type="submit"] {
        display: block;
        margin: 10px 0;
        font-family: 'Roboto', sans-serif;
    }

    input[type="text"],
    input[type="date"],
    input[type="time"] {
        font-size: 18px;
        border: 1px solid gray;
        width: 100%;
        border-radius: 20px;
        display: flex;
        justify-content: center;
    }

    input[type="submit"] {
        background-color: #f8f9fa;
        color: #3c4043;
        border: 1px solid #f8f9fa;
        border-radius: 4px;
        padding: 5px 15px;
        cursor: pointer;
    }

    input[type="submit"]:hover {
        box-shadow: 0px 1px 2px gray;
    }
    </style>

    <title>DB Navigator</title>

    <meta charset="UTF-8" name="viewport" content="width=device-width, initial-scale=1" />

</head>
<?php

$ch = curl_init("https://apis.deutschebahn.com/db-api-marketplace/apis/station-data/v2/stations");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Accept: application/json',
'DB-Client-Id: fef7e2d16dd19ee4dc3de32274c66d60',
'DB-Api-Key: 2f97bf22fb1e4b02643f0ce3fc06bca8'
));


//execute
$response = curl_exec($ch);

//close
curl_close($ch);

//do

$filteredResponse = json_decode($response);
//var_dump($filteredResponse);
/*
foreach($filteredResponse->result as $bahnhof) {
    var_dump($bahnhof->evaNumbers[0]->number);
    echo "<br>";
    echo "<br>";
  }
*/
?>
<div style="display: flex; align-items: center; justify-content: center; height: 100vh;">

    <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" style="width: 500px; margin: 20px auto;">

        <h1 style="margin: 2rem; font-size: 4rem;">Bahnhof</h1>

        <datalist id="bahnhöfe">
            <?php
        foreach($filteredResponse->result as $bahnhof) {
          echo '<option value="' . $bahnhof->name . '">';
        }
      ?>
        </datalist>


        <div class="input-group mb-3">
            <div class="input-group-prepend">
                <span class="input-group-text" id="basic-addon1"
                    style="border-color: gray; background-color: white; border-top-left-radius: 20px; border-bottom-left-radius: 20px;"><i
                        class="fa-solid fa-train"></i></span>
            </div>
            <input type="text" style="border-left: none;" class="form-control" placeholder="Bahnhof" list="bahnhöfe"
                required>
        </div>


        <div class="input-group mb-3">
            <div class="input-group-prepend">
                <span class="input-group-text"
                    style="border-color: gray; background-color: white; border-top-left-radius: 20px; border-bottom-left-radius: 20px;"><i
                        class="fa-regular fa-calendar"></i></span>
            </div>
            <input type="date" style="border-left: none;" id="date" name="date" class="form-control" required>
            <div class="input-group-append">
                <span class="input-group-text"
                    style="border-right: none; border-color: gray; background-color: white;"><i
                        class="fa-regular fa-clock"></i></span>
            </div>
            <input type="time" style="border-left:none;" id="time" name="time" class="form-control" required>
        </div>

        <div style="width: 100%; display:flex; justify-content: center;">
            <input type="submit" value="Absenden">
        </div>
    </form>

</div>
