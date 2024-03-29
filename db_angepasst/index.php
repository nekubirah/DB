<head>

    <script>//Initialisierung der Fonts (Schrift) -> in diesem Fall von Google</script>
    <link href="https://fonts.googleapis.com/css2?family=<FONT-NAME>&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.1.3/dist/css/bootstrap.min.css"
        integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
    <link rel="stylesheet" href="indexStyle.css">
    <script src="https://kit.fontawesome.com/2af9ff65b1.js" crossorigin="anonymous"></script>

    <title>Bahnhöf</title>

    <meta charset="UTF-8" name="viewport" content="width=device-width, initial-scale=1" />

</head>
<?php

//Abfrage aller Daten der Stadtionen Deutshclands mit DB-API "Station-Data"
$ch = curl_init("https://apis.deutschebahn.com/db-api-marketplace/apis/station-data/v2/stations");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt(
    $ch,
    CURLOPT_HTTPHEADER,
    array(
        'Accept: application/json',
        'DB-Client-Id: fef7e2d16dd19ee4dc3de32274c66d60',
        'DB-Api-Key: 2f97bf22fb1e4b02643f0ce3fc06bca8'
    )
);


//execute
$response = curl_exec($ch);

//close
curl_close($ch);

//do

$filteredResponse = json_decode($response);

//Test ausgabe der angefragten Daten zur Überprüfung

//var_dump($filteredResponse);
/*
foreach($filteredResponse->result as $bahnhof) {
    var_dump($bahnhof->evaNumbers[0]->number);
    echo "<br>";
    echo "<br>";
  }
*/


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $InputValue = $_POST["vonListe"]; // liest den Wert des Eingabefelds aus
    // hier können Sie den Wert von $myInputValue verwenden
}

?>
<div style="display: flex; align-items: center; justify-content: center; height: 100vh;">

    <form action="timetable.php" method="post" style="width: 500px; margin: 20px auto;">

        <h1 style="margin: 2rem; font-size: 4rem;">Bahnhöf</h1>

        <datalist id="bahnhöfe">
            <?php
            /*Hier findet die Auflistung aller Bahnhöfe statt.
            Diese werden in einer Datalist angezeigt, sodass der Nutzer entsprechende Bahnhöfe vorgeschlagen bekommt.*/

            foreach ($filteredResponse->result as $bahnhof) {
                echo '<option value="' . $bahnhof->name . '">';
            }



            ?>
        </datalist>

        <script>//Initialisierung der Datalist und anzeige für den Nutzer in form einer Text eingabe</script>

        <div class="input-group mb-3">
            <div class="input-group-prepend">
                <span class="input-group-text" id="basic-addon1"
                    style="border-color: gray; background-color: white; border-top-left-radius: 20px; border-bottom-left-radius: 20px;"><i
                        class="fa-solid fa-train"></i></span>
            </div>
            <input type="text" style="border-left: none;" class="form-control" placeholder="Bahnhof" list="bahnhöfe"
                name="vonListe" required>
        </div>

        <script>//Initialisierung Datums Abfrage.</script>

        <div class="input-group mb-3">
            <div class="input-group-prepend">
                <span class="input-group-text"
                    style="border-color: gray; background-color: white; border-top-left-radius: 20px; border-bottom-left-radius: 20px;"><i
                        class="fa-regular fa-calendar"></i></span>
            </div>
            <input type="date" style="border-left: none;" id="date" name="date" class="form-control" required readonly
                min=<?php

                /*An dieser Stellen wird das Datum entsprechend eingeschränkt, da die API nur in einem 24Stunden Intervall 
                am Tag der Abfrage Daten zurückgibt.*/

                date_default_timezone_set('Europe/Berlin');
                $date_today = date('Y-m-d', time());
                echo $date_today;
                ?> max=<?php
                 date_default_timezone_set('Europe/Berlin');
                 $date_today = date('Y-m-d', time());
                 echo $date_today;
                 ?> value=<?php
                  date_default_timezone_set('Europe/Berlin');
                  $date_today = date('Y-m-d', time());
                  echo $date_today;
                  ?>>


<script>/*Initialisierung der Zeit Abfrage mit Einschränkung von "03:00" Uhr,
da die API nur im Zeitraum ab 3 Uhr Daten übermittelt*/</script>


            <div class="input-group-append">
                <span class="input-group-text"
                    style="border-right: none; border-color: gray; background-color: white;"><i
                        class="fa-regular fa-clock"></i></span>
            </div>
            <input type="time" style="border-left:none;" id="time" name="time" class="form-control" required
                min="03:00">
        </div>


        <div style="width: 100%; display:flex; justify-content: center;">
            <input type="submit" value="Absenden">
        </div>
    </form>

</div>