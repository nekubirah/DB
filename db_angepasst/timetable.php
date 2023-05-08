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

  //Curl
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
  // OK, keine Ahnung, warum das geht, aber wir haben aus versehen die Zählvariable ergebnis im weiteren Code verwendet und es funktioniert
  foreach ($response->result as $ergebnis) {

    if (strcmp($ergebnis->name, $_POST["vonListe"]) == 0) {
      break;
    }

  }


  //Facilities aka Fahrstühle (elevator)
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



  // Welches Gleis hat welche "Facility"
  $response3 = json_decode($response3);
  $gleiseWithFahrstuhlString = "";
  foreach ($response3->facilities as $facilities) {
    if ($facilities->type == "ELEVATOR") {
      $gleiseWithFahrstuhlString = $gleiseWithFahrstuhlString . $facilities->description;
    }
  }
  //Fahrstuhlgleise rausfiltern und in einem Array speichern
  preg_match_all('!\d+!', $gleiseWithFahrstuhlString, $gleiseWithFahrstuhlArray);


  //datum Übergabe
  $date_i = $_POST["date"];
  $date = str_replace("-", "", $date_i);
  $date = substr($date, 2);

  //time Übergabe
  $time_i = $_POST["time"];
  $time = explode(":", $time_i);

  //Abfrage der Time Tables
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

  <div style="display: flex; align-items: center; justify-content: center; font-family: 'Roboto', sans-serif;">

    <div class="unsereItemsBox">
      <div class="header" style="text-align: center;">
        <h1>
          <?php echo $ergebnis->name; ?>
        </h1>

        <div
          style="display: flex;justify-content: space-between; padding-right: 1rem;padding-left: 1rem; padding-bottom: 0.5rem; align-items: center;">
          <form action="timetable.php" method="post">
            <?php
            $value_encoded = htmlspecialchars($xml['station']);
            echo '<input type="hidden" id="vonListe" name="vonListe" value= "' . $ergebnis->name . '">';
            ?>
            <input type="hidden" id="date" name="date" value=<?php echo $date_i ?>>
            <input type="hidden" id="time" name="time" value=<?php echo ($time[0] - 1) . ":" . $time[1] ?>>


            <div>
              <?php
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


          <label class="toggle">
            <input class="toggle-input" type="checkbox" id="toggleCheckbox" />
            <span class="toggle-bg"></span>
            <span class="toggle-label" data-off="Abfahrt" data-on="Ankunft"></span>
            <span class="toggle-handle"></span>
          </label>


          <?php /*  später button:  */?>

          <form action="timetable.php" method="post">
            <?php
            $value_encoded = htmlspecialchars($xml['station']);
            echo '<input type="hidden" id="vonListe" name="vonListe" value= "' . $ergebnis->name . '">';
            ?>
            <input type="hidden" id="date" name="date" value=<?php echo $date_i ?>>
            <input type="hidden" id="time" name="time" value=<?php echo ($time[0] + 1) . ":" . $time[1] ?>>


            <div>
              <?php
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
      foreach ($xml->s as $s) {

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

              echo "<h3>" . $s->tl['c'] . " " . $unserTrainNumber . " aus " . $stationenVon[0] . " nach " . end($stationenVon) . " </h3> <br>";
              echo "Ankunft Zeit: " . substr($s->ar['pt'], 6, 2) . ":" . substr($s->ar['pt'], 8, 2) . "<br>";

              if ($s->ar['pp'] == "") {
                echo "Ankunft Gleis: " . $s->ar['l'] . "<br>";
              } else {
                echo "Ankunft Gleis: " . $s->ar['pp'] . "<br>";
              }

              preg_match_all('!\d+!', (string) $s->ar['pp'], $gleisOhneBuchstabeEins);
              if (in_array($gleisOhneBuchstabeEins[0][0], $gleiseWithFahrstuhlArray[0])) {
                ?>
                <div style="align-items: center;">
                  <i title="Fahrstuhl verfügbar!" class="fa-regular fa-elevator" style="margin-top: 0.33rem;"></i>
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
        if (isset($s->dp)) {
          ?>
          <div class="unsereItems" id="abfahrt">
            <?php
            $stationen = explode("|", $s->dp['ppth']);

            if ($s->dp['l'] != "") {
              $unserTrainNumber = $s->dp['l'];
            } elseif ($s->ar['l'] != "") {
              $unserTrainNumber = $s->ar['l'];
            } else {
              $unserTrainNumber = $s->tl['n'];
            }

            echo "<h3>" . $s->tl['c'] . " " . $unserTrainNumber . " nach " . end($stationen) . " aus " . $stationen[0] . "</h3> <br>";
            echo "Abfahrt Zeit: " . substr($s->dp['pt'], 6, 2) . ":" . substr($s->dp['pt'], 8, 2) . "<br>";
            if ($s->dp['pp'] == "") {
              echo "Ankunft Gleis: " . $s->dp['l'] . "<br>";
            } else {
              echo "Ankunft Gleis: " . $s->dp['pp'] . "<br>";
            }
            preg_match_all('!\d+!', (string) $s->dp['pp'], $gleisOhneBuchstabeZwei);
            if (in_array($gleisOhneBuchstabeZwei[0][0], $gleiseWithFahrstuhlArray[0])) {
              ?>
              <i title="Fahrstuhl verfügbar!" class="fa-regular fa-elevator" style="margin-top: 0.33rem;"></i>
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

</script>