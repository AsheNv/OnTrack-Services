<?php
/**
 * Created by PhpStorm.
 * User: AsheN NovA
 * Date: 3/21/2016
 * Time: 11:21 AM
 */

$sname = '';
$sid = 0;

if($_SERVER["REQUEST_METHOD"]=="GET")
{
    require 'Connection.php';
    
    $lon = $_GET["longitude"];
    $lat = $_GET["latitude"];
    $itime = $_GET["idletime"];
    $trainId = $_GET["trainId"];
    getCoordination($lon,$lat,$itime,$trainId);
    savePos($lon,$lat,$trainId);

}

function savePos($sLong,$sLat,$trainID){

    $lon = $sLong;
    $lat = $sLat;
    
    $trainId = $trainID;

    global $connect;


    $stmt = $connect->prepare("INSERT INTO `current_pos`(`trainId`, `lat`, `long`)
             VALUES (?,?,?)");

            $stmt->bind_param("idd",$trainID,$sLat,$sLong);

            $stmt->execute();
            $stmt->close();




}


function getCoordination($sLong,$sLat,$iTime,$trainID){


    $lon = $sLong;
    $lat = $sLat;
    $itime = $iTime;
    $trainId = $trainID;




    global $connect;





    $query = "SELECT * FROM station";

    mysqli_query($connect, $query) or die (mysqli_error ($connect));


    $result = mysqli_query($connect, $query);


    $arr = array();
    $arr5 = array($lon,$lat);


    $arr5["SLong"] = $arr5[0];
    unset($arr5[0]);

    $arr5["SLat"] = $arr5[1];
    unset($arr5[1]);

    while ( $row = mysqli_fetch_assoc($result) ) {

        $arr [] = $row;

    }

    $found = false;
    $sname = "";
    $sid = 0;
    foreach ($arr as $key => $data) {

        $lon2 = $data['SLong'];
        $lat2 = $data['SLat'];


        if (distance($lat,$lon,$lat2,$lon2) < 0.1) { // if distance < 0.1 km we take locations as equal

            $sname = $data['SName'];
            $sid = $data['SID'];

            getAlias($trainId,$sname);

            $stmt = $connect->prepare("INSERT INTO `train_history`(`SID`,`StationName`, `TrainID`, `IdleTime` )
             VALUES (?,?,?,?)");

            $stmt->bind_param("isii",$sid,$sname,$trainId,$itime );

            $stmt->execute();
            $stmt->close();


            break;
        }




        

    }



//    header ('Content-Type: application/json');
//    echo json_encode(array("Position"=>$_SESSION['TrainID']));
//




}


function distance($lat1, $lon1, $lat2, $lon2, $unit) {

    $theta = $lon1 - $lon2;
    $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
    $dist = acos($dist);
    $dist = rad2deg($dist);
    $miles = $dist * 60 * 1.1515;
    $unit = strtoupper($unit);


    return ($miles * 1.609344);

}

function getAlias($TrainId, $StationName)
{


    global $connect;

    $trainId = $TrainId;
    $stationName = $StationName;


    $query = "SELECT * FROM pushbot WHERE `trainId`= '$trainId'";
    mysqli_query($connect, $query) or die (mysqli_error($connect));


    $result = mysqli_query($connect, $query);


    $arr = array();


    while ($row = mysqli_fetch_assoc($result)) {

        $arr [] = $row;

    }


    foreach ($arr as $key => $data) {

        $tokenId = $data['Alias'];

        sendPush($tokenId, $stationName);


    }
}

function sendPush($alias, $station)
{
// Push The notification with parameters
    require_once('PushBots.class.php');
    $trainAlias = $alias;
    $trainStation = $station;

    $pb = new PushBots();
// Application ID
    $appID = '57456e864a9efad0af8b4568';
// Application Secret
    $appSecret = 'c7e001e0fb43608dba263b1170fe1e2f';
    $pb->App($appID, $appSecret);
    $pb->Alias("$trainAlias");


// Notification Settings
    $pb->Alert("The Train has reached $trainStation");
    $pb->Platform(array("0","1"));




// Update Alias
    /**
     * set Alias Data
     * @param	integer	$platform 0=> iOS or 1=> Android.
     * @param	String	$token Device Registration ID.
     * @param	String	$alias New Alias.
     */



// Push it !
    $pb->Push();


}

?>
