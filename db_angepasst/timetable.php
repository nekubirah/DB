<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="timetableStyle.css">
  <script src="https://kit.fontawesome.com/2af9ff65b1.js" crossorigin="anonymous"></script>
  <title>DB Navigator</title>
</head>

<body>



  <?php

  //Curl abfrage zur API der Deutschen Bahn mit allen Daten der Stadtionen in Deutschland
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
  $response = json_decode($response);

  // Vergleich aller Bahnhöfe mit dem, welchen der Nutzer ausgewählt hat und speichern der entsprechenden Parameter  
  $foundOneToOne = false;
  $foundPartial = false;
  foreach ($response->result as $ergebnis) {
    if (strcmp($ergebnis->name, $_POST["vonListe"]) == 0) {
      $foundOneToOne = true;
      $foundPartial = true;
      //echo "1:1";
      break;
    }
  }
  // Teilabfrage falls Nutzer nicht genau den Bahnhof angegeben hat (ist Case-Sensitive daher wird alles lowercased). 
  if ($foundOneToOne == false) {
    // echo "1:1 false";
    foreach ($response->result as $ergebnis) {
      if (str_contains(strtolower($ergebnis->name), strtolower($_POST["vonListe"])) !== false) {
        $foundPartial = true;
        //echo "teilübereinstimmung: ".$ergebnis->name ;
        break;
      }
    }
  }
  if ($foundPartial !== true) {
    echo "!Es wurde kein übereinstimmender Bahnhof gefunden!";
  }



  //Curl abfrage der Facilities aka Fahrstühle (elevator)
  $curl3 = curl_init();

  curl_setopt_array($curl3, [
    CURLOPT_URL => "https://apis.deutschebahn.com/db-api-marketplace/apis/fasta/v2/stations/" . $ergebnis->number,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => "",
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => "GET",
    CURLOPT_HTTPHEADER => [
      'DB-Client-Id: fef7e2d16dd19ee4dc3de32274c66d60',
      'DB-Api-Key: 2f97bf22fb1e4b02643f0ce3fc06bca8',
      "accept: application/json"
    ],
  ]);

  $response3 = curl_exec($curl3);
  $err = curl_error($curl3);

  curl_close($curl3);

  if ($err) {
    echo "cURL Error #:" . $err;
  } else {
    //echo $response3."<br><br>";
  }



  // Vergleich: Welches Gleis hat welche "Facility"
  $response3 = json_decode($response3);
  $gleiseWithFahrstuhlString = "";
  foreach ($response3->facilities as $facilities) {
    if ($facilities->type == "ELEVATOR") {
      $gleiseWithFahrstuhlString = $gleiseWithFahrstuhlString . $facilities->description;
    }
  }
  //Fahrstuhlgleise rausfiltern und in einem Array speichern
  preg_match_all('!\d+!', $gleiseWithFahrstuhlString, $gleiseWithFahrstuhlArray);


  //Datums Übergabe
  $date_i = $_POST["date"];
  $date = str_replace("-", "", $date_i);
  $date = substr($date, 2);

  //Zeit Übergabe
  $time_i = $_POST["time"];
  $time = explode(":", $time_i);

  //Abfrage der "Time Tables" API mit entsprechenden Daten wie Datum, Zeit und Eva Nummer des Bahnhofs,welchen der Benutzer ausgewählt hat
  $curl2 = curl_init();
  $link = "https://apis.deutschebahn.com/db-api-marketplace/apis/timetables/v1/plan/" . $ergebnis->evaNumbers[0]->number . "/" . $date . "/" . $time[0];
  curl_setopt_array($curl2, [
    CURLOPT_URL => $link,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => "",
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => "GET",
    CURLOPT_HTTPHEADER => [
      "DB-Api-Key: 2f97bf22fb1e4b02643f0ce3fc06bca8",
      "DB-Client-Id: fef7e2d16dd19ee4dc3de32274c66d60",
      "accept: application/xml"
    ],
  ]);

  $response2 = curl_exec($curl2);
  curl_close($curl2);

  $xml = simplexml_load_string($response2);
  ?>

  <script>//Darstellung der Webseite angefangen mit dem Namen des Bahnhofes</script>


  <div style="display: flex; align-items: center; justify-content: center; font-family: 'Roboto', sans-serif;">

    <div class="unsereItemsBox">
      <div class="header" style="text-align: center;">
        <h1>
          <?php echo $ergebnis->name; ?>
        </h1>

        <script>//Früher-Button (um Ergebnisse 1h Früher an diesem Bahnhof auszugeben)</script>
        <script>//Übergabe von "hidden" Variablen, welche für den Aufruf der Seite mit früherer Zeit benötigt wird</script>

        <div
          style="display: flex;justify-content: space-between; padding-right: 1rem;padding-left: 1rem; padding-bottom: 0.5rem; align-items: center;">
          <form action="timetable.php" method="post">
            <?php
            echo '<input type="hidden" id="vonListe" name="vonListe" value= "' . $ergebnis->name . '">';
            ?>
            <input type="hidden" id="date" name="date" value=<?php echo $date_i ?>>
            <input type="hidden" id="time" name="time" value=<?php echo ($time[0] - 1) . ":" . $time[1] ?>>

            <div>
              <?php
              //Einschränkung des Buttons, um zeiten früher als 3 Uhr nicht anzuzeigen, da diese von der API nicht ausgegeben werden
              if ($time[0] <= 4) {
                $disabled = "disabled";
              } else {
                $disabled = "";
              }
              ?>

              <button type="submit" class="rounded-button" <?php echo $disabled; ?>>
                <i class="fa-solid fa-arrow-right fa-rotate-180"></i> Früher
              </button>


            </div>
          </form>

          <script>//Schieberegler für Ankunft und Abfahrt</script>

          <label class="toggle">
            <input class="toggle-input" type="checkbox" id="toggleCheckbox" />
            <span class="toggle-bg"></span>
            <span class="toggle-label" data-off="Abfahrt" data-on="Ankunft"></span>
            <span class="toggle-handle"></span>
          </label>


          <script>//Später-Button (um Ergebnisse 1h Später an diesem Bahnhof auszugeben)</script>
          <script>//Auch hier: Übergabe von "hidden" Variablen, welche für den Aufruf der Seite mit späteren Zeit benötigt wird</script>

          <form action="timetable.php" method="post">
            <?php
            echo '<input type="hidden" id="vonListe" name="vonListe" value= "' . $ergebnis->name . '">';
            ?>
            <input type="hidden" id="date" name="date" value=<?php echo $date_i ?>>
            <input type="hidden" id="time" name="time" value=<?php echo ($time[0] + 1) . ":" . $time[1] ?>>


            <div>
              <?php
              //Einschränkung des Buttons, um zeiten später als 24 Uhr nicht anzuzeigen für diesen Tag. (da der Tag nur 24h hat)
              if ($time[0] >= 23) {
                $disabled = "disabled";
              } else {
                $disabled = "";
              }
              ?>
              <button type="submit" class="rounded-button" <?php echo $disabled; ?>>
                Später <i class="fa-solid fa-arrow-right"></i>
              </button>
            </div>
          </form>
        </div>
      </div>
      <?php
      ?>

      <?php

      /*Hier werden nun nach und nach die einzelnen Züge, samt Gleis und Zeit ausgegeben.
      Ebenfalls wird hier ein entsprechendes Symbol angezeigt, wenn es einen Fahrstuhl gibt.
      Ein entsprechender Schieberegler filtert die Ergebnisse nach einfahrenden und abfahrenden Zügen. 
      Für genauere Erklärung der einzelnen abfragen (z.B. $s->dp[l]) empfiehlt es sich die Dokumentation der API 
      auf der Webseite der DB zu Hilfe zu nehmen.*/
      foreach ($xml->s as $s) {
        //ar steht in diesem Fall für "Arrival", bzw. zu deutsch Ankuft
        if (isset($s->ar)) {
          ?>
          <div class="unsereItems" id="ankunft">
            <div>
              <?php
              $stationenVon = explode("|", $s->ar['ppth']);

              if ($s->dp['l'] != "") {
                $unserTrainNumber = $s->dp['l'];
              } elseif ($s->ar['l'] != "") {
                $unserTrainNumber = $s->ar['l'];
              } else {
                $unserTrainNumber = $s->tl['n'];
              }

              echo "<h3>" . $s->tl['c'] . " " . $unserTrainNumber . " aus " . $stationenVon[0] . " </h3> <br>";
              echo "Ankunft Zeit: " . substr($s->ar['pt'], 6, 2) . ":" . substr($s->ar['pt'], 8, 2) . "<br>";

              if ($s->ar['pp'] != "") {
                echo "Ankunft Gleis: " . $s->ar['pp'] . "<br>";
              } else if ($s->ar['l'] != "") {
                echo "Ankunft Gleis: " . $s->ar['l'] . "<br>";
              } else {
                echo "Ankunft Gleis: n.a.";
              }

              preg_match_all('!\d+!', (string) $s->ar['pp'], $gleisOhneBuchstabeEins);
              if (in_array($gleisOhneBuchstabeEins[0][0], $gleiseWithFahrstuhlArray[0])) {
                ?>
                <div style="align-items: center;">
                  <svg title="Fahrstuhl verfügbar!" style="height: 1.4rem; width: 1.4rem; margin-top: 0.33rem;"
                    xmlns="http://www.w3.org/2000/svg"
                    viewBox="0 0 512 512"><!--! Font Awesome Pro 6.4.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2023 Fonticons, Inc. -->
                    <path
                      d="M132.7 4.7l-64 64c-4.6 4.6-5.9 11.5-3.5 17.4s8.3 9.9 14.8 9.9H208c6.5 0 12.3-3.9 14.8-9.9s1.1-12.9-3.5-17.4l-64-64c-6.2-6.2-16.4-6.2-22.6 0zM64 128c-35.3 0-64 28.7-64 64V448c0 35.3 28.7 64 64 64H448c35.3 0 64-28.7 64-64V192c0-35.3-28.7-64-64-64H64zm96 96a48 48 0 1 1 0 96 48 48 0 1 1 0-96zM80 400c0-26.5 21.5-48 48-48h64c26.5 0 48 21.5 48 48v16c0 17.7-14.3 32-32 32H112c-17.7 0-32-14.3-32-32V400zm192 0c0-26.5 21.5-48 48-48h64c26.5 0 48 21.5 48 48v16c0 17.7-14.3 32-32 32H304c-17.7 0-32-14.3-32-32V400zm32-128a48 48 0 1 1 96 0 48 48 0 1 1 -96 0zM356.7 91.3c6.2 6.2 16.4 6.2 22.6 0l64-64c4.6-4.6 5.9-11.5 3.5-17.4S438.5 0 432 0H304c-6.5 0-12.3 3.9-14.8 9.9s-1.1 12.9 3.5 17.4l64 64z" />
                  </svg>
                </div>
                <?php
              }
              ?>
            </div>
          </div>
          <?php
        }

        ?>




        <?php
        //dp steht in deisem falle für "Departure" bzw. zu deutsch Abfahrt
        if (isset($s->dp)) {
          ?>
          <div class="unsereItems" id="abfahrt">
            <?php
            $stationen = explode("|", $s->dp['ppth']);
            $station_ar = explode("|", $s->ar['ppth']);

            if ($s->dp['l'] != "") {
              $unserTrainNumber = $s->dp['l'];
            } elseif ($s->ar['l'] != "") {
              $unserTrainNumber = $s->ar['l'];
            } else {
              $unserTrainNumber = $s->tl['n'];
            }

            echo "<h3>" . $s->tl['c'] . " " . $unserTrainNumber . " nach " . end($stationen) .
              "</h3> <br>";
            echo "Abfahrt Zeit: " . substr($s->dp['pt'], 6, 2) . ":" . substr($s->dp['pt'], 8, 2) . "<br>";
            if ($s->dp['pp'] != "") {
              echo "Ankunft Gleis: " . $s->dp['pp'] . "<br>";
            } else if ($s->dp['l'] != "") {
              echo "Ankunft Gleis: " . $s->dp['l'] . "<br>";
            } else {
              echo "Ankunft Gleis: n.a.";
            }
            preg_match_all('!\d+!', (string) $s->dp['pp'], $gleisOhneBuchstabeZwei);
            if (in_array($gleisOhneBuchstabeZwei[0][0], $gleiseWithFahrstuhlArray[0])) {
              ?>
              <svg title="Fahrstuhl verfügbar!" style="height: 1.4rem; width: 1.4rem; margin-top: 0.33rem;"
                xmlns="http://www.w3.org/2000/svg"
                viewBox="0 0 512 512"><!--! Font Awesome Pro 6.4.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2023 Fonticons, Inc. -->
                <path
                  d="M132.7 4.7l-64 64c-4.6 4.6-5.9 11.5-3.5 17.4s8.3 9.9 14.8 9.9H208c6.5 0 12.3-3.9 14.8-9.9s1.1-12.9-3.5-17.4l-64-64c-6.2-6.2-16.4-6.2-22.6 0zM64 128c-35.3 0-64 28.7-64 64V448c0 35.3 28.7 64 64 64H448c35.3 0 64-28.7 64-64V192c0-35.3-28.7-64-64-64H64zm96 96a48 48 0 1 1 0 96 48 48 0 1 1 0-96zM80 400c0-26.5 21.5-48 48-48h64c26.5 0 48 21.5 48 48v16c0 17.7-14.3 32-32 32H112c-17.7 0-32-14.3-32-32V400zm192 0c0-26.5 21.5-48 48-48h64c26.5 0 48 21.5 48 48v16c0 17.7-14.3 32-32 32H304c-17.7 0-32-14.3-32-32V400zm32-128a48 48 0 1 1 96 0 48 48 0 1 1 -96 0zM356.7 91.3c6.2 6.2 16.4 6.2 22.6 0l64-64c4.6-4.6 5.9-11.5 3.5-17.4S438.5 0 432 0H304c-6.5 0-12.3 3.9-14.8 9.9s-1.1 12.9 3.5 17.4l64 64z" />
              </svg>
              <?php
            }
            ?>
          </div>
          <?php
        }
      }
      ?>

    </div>
  </div>

</body>

</html>

<script>
  // Hier das entsprechende Script um zu entscheiden, ob Ankunft oder Abfahrt vom Nutzer ausgewählt wurde und hierrauf folgend die entsprechenden Daten anzuzeigen.

  document.querySelectorAll("#ankunft").forEach(function (item) {
    item.style.display = "none";
  });

  document.getElementById("toggleCheckbox").addEventListener("change", function () {
    const contentA = document.querySelectorAll("#abfahrt");
    const contentB = document.querySelectorAll("#ankunft");

    contentA.forEach(function (item) {
      if (this.checked) {
        item.style.display = "none";
      } else {
        item.style.display = "block";
      }
    }, this);

    contentB.forEach(function (item) {
      if (this.checked) {
        item.style.display = "block";
      } else {
        item.style.display = "none";
      }
    }, this);
  });

</script>‚