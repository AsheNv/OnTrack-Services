<?php

if ($_SERVER["REQUEST_METHOD"] == "GET") {
    require 'Connection.php';
    require 'TrainTrack.php';



    session_start();


//    $sName = $_GET["sname"];
//    $tId = $_GET["trainid"];

    $sName = $_SESSION['StationName']
    $tId = $_SESSION['StationName']

    getAlias($tId,$sName);

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


