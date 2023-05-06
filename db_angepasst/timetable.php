<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <script src="https://kit.fontawesome.com/2af9ff65b1.js" crossorigin="anonymous"></script>
  <title>DB Navigator</title>

  <style>
    .rounded-button {
      border-radius: 50%;
      padding: 0.4rem;
      font-size: 1rem;
      border: solid 1px;
      background-color: white;
      cursor: pointer;
    }

    .rounded-button:hover {
      background-color: #444744;
      color: white;
    }

    .unsereItemsBox {
      width: 750px;
      border-radius: 20px;
    }

    .unsereItems {
      padding: 1rem;
      border-bottom: solid 1px gray;
    }

    .unsereItems:hover {
      background-color: #f8f9fa;
    }

    h1 {
      font-family: inherit;
      font-weight: 500;
      line-height: 1.2;
      margin: 2rem;
      font-size: 2rem;
      padding: 2rem;
      margin-bottom: 0;
      padding-bottom: 1.5rem;
    }

    h3 {
      font-family: inherit;
      font-weight: 500;
      line-height: 1.2;
      margin: 0;
      padding: 0;
    }

    .header {
      background-color: white;
      position: sticky;
      top: 0;
      box-shadow: 0 5px 4px -6px black;
    }

    .sticky {
      position: fixed;
      top: 0;
      width: 100%
    }

    .toggle {
      position: relative;
      display: inline-block;
      width: 80px;
      height: 28px;
      font-size: 14px;
    }

    .toggle-input {
      display: none;
    }

    .toggle-bg {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background-color: #ccc;
      border-radius: 14px;
      transition: background-color 0.3s ease;
    }

    .toggle-label {
      position: absolute;
      top: 50%;
      left: 0;
      right: 0;
      transform: translateY(-50%);
      text-align: center;
      pointer-events: none;
    }

    .toggle-label:before,
    .toggle-label:after {
      position: absolute;
      top: 50%;
      transform: translateY(-50%);
    }

    .toggle-label:before {
      content: attr(data-off);
      right: 0.4rem;
      opacity: 1;
    }

    .toggle-label:after {
      content: attr(data-on);
      left: 0.4rem;
      color: #757575;
      opacity: 0;
    }

    .toggle-input:checked~.toggle-bg {
      background-color: #444744;
    }

    .toggle-input:checked~.toggle-label:before {
      color: #757575;
      opacity: 0;
    }

    .toggle-input:checked~.toggle-label:after {
      color: #fff;
      opacity: 1;
    }

    .toggle-handle {
      position: absolute;
      top: 50%;
      left: 4px;
      width: 20px;
      height: 20px;
      background-color: #fff;
      border-radius: 50%;
      transform: translateY(-50%);
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
      transition: left 0.3s ease;
    }

    .toggle-input:checked~.toggle-handle {
      left: calc(100% - 4px - 20px);
    }
  </style>
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
          <?php echo $xml['station']; ?>
        </h1>

        <div
          style="display: flex;justify-content: space-between; padding-right: 1rem;padding-left: 1rem; padding-bottom: 0.5rem; align-items: center;">
          <form action="timetable.php" method="post">
            <?php
            $value_encoded = htmlspecialchars($xml['station']);
            echo '<input type="hidden" id="vonListe" name="vonListe" value= "' . $value_encoded . '">';
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
                <i class="fa-solid fa-arrow-right fa-rotate-180"></i>
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
            echo '<input type="hidden" id="vonListe" name="vonListe" value= "' . $value_encoded . '">';
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
                <i class="fa-solid fa-arrow-right"></i>
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
              echo "<h3>" . $s->tl['c'] . " " . $s->tl['n'] . " aus " . $stationenVon[0] . " </h3> <br>";
              echo "Ankunft Zeit: " . substr($s->ar['pt'], 6, 2) . ":" . substr($s->ar['pt'], 8, 2) . "<br>";
              echo "Ankunfts Gleis: " . $s->ar['pp'] . "<br>";
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
            echo "<h3>" . $s->tl['c'] . " " . $s->tl['n'] . " nach " . end($stationen) . "</h3> <br>";
            echo "Abfahrt Zeit: " . substr($s->dp['pt'], 6, 2) . ":" . substr($s->dp['pt'], 8, 2) . "<br>";
            echo "Abfahrts Gleis: " . $s->dp['pp'] . "<br>";
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