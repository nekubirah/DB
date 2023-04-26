<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>DB Navigator</title>
</head>
<body>


<?php

//Curl
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

$response = json_decode($response, true);

$m=0;
 foreach ($response['result'] as $h){
        $bahnhof_name[$m] = $response['result'][$m]['name'];
        $bahnhof_number[$m] = $response['result'][$m]['number'];
        $bahnhof_eva[$m] = $response['result'][$m]['evaNumbers'];
       // $bahnhof_step[$m] = $response['result'][$m]['hasSteplessAccess'];
        $m++;
 }

 
    
// Bahnhof Daten Raussuchen
$i=0;
foreach($bahnhof_name as $name){
    if(json_encode($name) == json_encode($_POST["vonListe"])){
      $nr = $i;
        //echo $bahnhof_name[$i] ,"</br>";
        //echo $bahnhof_number[$i] ,"</br>";
        
        $evanr = explode(",",json_encode($bahnhof_eva[$i]));
        $evanr_true = explode(":",$evanr[0]);
        //echo $evanr_true[1], "</br>";
    }
    $i++;
}


//Facilities aka Fahrstühle (elevator)
$curl3 = curl_init();

curl_setopt_array($curl3, [
  CURLOPT_URL => "https://apis.deutschebahn.com/db-api-marketplace/apis/fasta/v2/stations/".$bahnhof_number[$nr],
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

/*
if ($err) {
  echo "cURL Error #:" . $err;
} else {
  echo $response3."<br><br>";
}
*/


// Welches Gleis hat welche "Facility"
$response3 =json_decode($response3);
foreach($response3->facilities as $facilities) {
  if($facilities->type == "ELEVATOR"){
  echo  $facilities->description.": ".$facilities->type."<br>";
}}


echo "<br><br>";


//datum Übergabe
$date=$_POST["date"];
$date = str_replace("-","",$date);
$date = substr($date, 2);

//time Übergabe
$time = $_POST["time"];
$time = explode(":",$time);


//Abfrage der Time Tables
$curl2 = curl_init();
$link ="https://apis.deutschebahn.com/db-api-marketplace/apis/timetables/v1/plan/".$evanr_true[1]."/".$date."/".$time[0];
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


/*link zum Prüfen
echo $link,"<br><br>";
*/

/*test ob Daten übergeben wurden

  $str = htmlentities($response2 , ENT_XML1);
  if ($str == ""){ echo "404 - No Data Found";}
  echo $str;
  
echo "<br><br>";
*/

/*Daten formatieren
Das Ganze ist in 2 geteilt:
- Ankuft des Zuges und 
- Abfahrt des zuges
*/

$xml = simplexml_load_string($response2);

echo "Station: " . $xml['station'] . "<br><br>";

foreach ($xml->s as $s) {
    echo "ID: " . $s['id'] . "<br>";
    echo "Zug: " . $s->tl['c'] . " ";
    echo $s->dp['l'] . $s->ar['l']."<br>";

    if (isset($s->dp)) {
    echo "Abfahrt Zeit: " . substr($s->dp['pt'],6,2).":".substr($s->dp['pt'],8,2) ."<br>";
    echo "Abfahrts Gleis: " . $s->dp['pp'] . "<br>";
    echo "Abfahrt Weg (Nach): " . $s->dp['ppth'] . "<br> <br>";
    }

    if (isset($s->ar)) {
        echo "Ankunft Zeit: " . substr($s->ar['pt'],6,2).":".substr($s->ar['pt'],8,2) ."<br>";
        echo "Ankunfts Gleis: " . $s->ar['pp'] . "<br>";
        echo "Ankunft Weg (Aus/Von): " . $s->ar['ppth'] . "<br> <br>";
    }
}


?>

</body>
</html>



