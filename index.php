<style>
    /* Add some basic styling to the form */
    form {
        width: 400px;
        margin: 0 auto;
        padding: 20px;
        background-color: #1f4037;
    }

    /* Style the labels */
    label {
        font-size: 18px;
        font-weight: bold;
        margin-bottom: 5px;
        display: block;
    }

    /* Style the inputs */
    input {
        width: 100%;
        padding: 12px 20px;
        margin-bottom: 20px;
        box-sizing: border-box;
        border: 1px solid #ccc;
        border-radius: 4px;
    }

    /* Style the submit button */
    input[type="submit"] {
        background-color: #99f2c8;
        color: #1f4037;
        padding: 12px 20px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
    }

    /* Style the result text */
    .result {
        margin-top: 20px;
        font-size: 24px;
        font-weight: bold;
        text-align: center;
    }
</style>

<title>DB Navigator</title>

<meta charset="UTF-8" name="viewport" content="width=device-width, initial-scale=1"/>


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

?>


<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
  <label for="von">Von:<br>
    <input type="string" list="bahnhöfe">
    <datalist id="bahnhöfe">
      <?php
        foreach($filteredResponse->result as $bahnhof) {
          echo '<option value="' . $bahnhof->name . '">';
        }
      ?>
    </datalist>
  </label>
  <br>
  <label for="nach">Nach:<br>
    <input type="string" list="bahnhöfe">
  </label>
  <br>
  Datum:<br>
  <input type="date" id="date" name="date">
  <br>
  Zeit:<br>
  <input type="time" id="time" name="time">
  <br>
  <input type="submit" value="Ermitteln">
</form>




