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

$response = json_decode($response, true);

$m=0;
 foreach ($response['result'] as $h){
        $bahnhof_name[$m] = $response['result'][$m]['name'];

        $bahnhof_name[$m] = str_replace(" ( ","(",$bahnhof_name[$m]);
        $bahnhof_name[$m] = str_replace(" (b ","-(bei ",$bahnhof_name[$m]);
        $bahnhof_name[$m] = str_replace(" ) ",")",$bahnhof_name[$m]);
        $bahnhof_name[$m] = str_replace(" )",")",$bahnhof_name[$m]);
        $bahnhof_name[$m] = str_replace(" ","-",$bahnhof_name[$m]);

        $bahnhof_number[$m] = $response['result'][$m]['number'];
        $bahnhof_eva[$m] = $response['result'][$m]['evaNumbers'];
       // $bahnhof_step[$m] = $response['result'][$m]['hasSteplessAccess'];
        $m++;
 }

 echo $bahnhof_name[0] ,"</br>";
 echo $bahnhof_number[0] ,"</br>";


$evanr = explode(",",json_encode($bahnhof_eva[0]));
$evanr_true = explode(":",$evanr[0]);
//echo $evanr_true[1], "</br>";
    
$i=0;
foreach($bahnhof_name as $name){
    if(json_encode($name) == json_encode($_POST["vonliste"])){
        echo $bahnhof_name[$i] ,"</br>";
        echo $bahnhof_number[$i] ,"</br>";

        $evanr = explode(",",json_encode($bahnhof_eva[$i]));
        $evanr_true = explode(":",$evanr[0]);
        echo $evanr_true[1], "</br>";
    }
    $i++;
}

$curl2 = curl_init();

$link ="https://apis.deutschebahn.com/db-api-marketplace/apis/timetables/v1/plan/".$evanr_true[1]."/230310/11";
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
 echo $link,"<br>";

    echo "1";
  echo $response2;
  echo "2";
  $str = htmlentities($response2 , ENT_XML1);
  echo $str;
  

?>

