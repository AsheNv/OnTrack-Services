<?php
/**
 * Created by PhpStorm.
 * User: AsheN NovA
 * Date: 3/21/2016
 * Time: 11:21 AM
 */


if($_SERVER["REQUEST_METHOD"]=="GET")
{
    require 'Connection.php';

    mainFunction();


}
$pos_array = array();
$last_pos_time = 0;


function mainFunction(){


    global  $last_pos_time;



    $trainId = $_GET["trainId"];
    $userSt = $_GET['userSt'];
    //   $firstPos = 1;

    $lastPos =  getLastPos($trainId);
    $firstPosName = getStartStation($trainId);
    $firstPos = getStartIndex($firstPosName);
    $sumDistance = getDistSum ($firstPos,$lastPos);
    $idleTime = getIdleTime($trainId);
    $trainSpeed = getSpeed($sumDistance,$trainId,$idleTime);
    $userStation = getUserSt($userSt);
    $remainingDistance = remainDistance($userStation,$lastPos);
    $startSchID = getSchId($lastPos,$trainId);
    $endSchID = getSchId($userStation,$trainId);
    $numberOfStations = getNoOfStations($startSchID,$endSchID,$trainId);
    $timeEstimate = estimateTime ($trainSpeed,$remainingDistance,$last_pos_time,$numberOfStations);
    $schArrival = getSchArrivalTime($trainId,$lastPos);

    $schAr = strtotime($schArrival);
    $actAr = strtotime($last_pos_time);
    $etatime = strtotime($timeEstimate);
    $delay = getCurrentDelay($schAr,$actAr);



    $searchResult = searchValidation($userStation,$trainId);



    $Eta = $actAr + $etatime ;

    $result_array = array("Delay"=>gmdate("H:i:s", $delay),  "Eta" => $timeEstimate, "Time Arrival" => gmdate("H:i:s", $Eta), "Train Status" => $searchResult);
    header ('Content-Type: application/json');
    echo json_encode(array("Result" => $result_array));


}



function getLastPos($trainId){

    global $connect;
    global $last_pos_time;

    $tid =  $trainId;


    $query = " SELECT distance_info.DisId, train_history.StationName, train_history.Date FROM distance_info
               INNER JOIN train_history ON train_history.StationName = distance_info.StartSt
               WHERE `trainId` = '".$tid."'
               ORDER BY  `Date` DESC
               LIMIT 1";


    mysqli_query($connect, $query) or die (mysqli_error ($connect));


    $result = mysqli_query($connect, $query);
    $number_of_rows = mysqli_num_rows($result);


    if($number_of_rows > 0)
    {
        while($row = mysqli_fetch_assoc($result))
        {
            $pos_array = $row;

        }
    }

    $curStId=$pos_array["DisId"];
    $time = $pos_array["Date"];
    $last_pos_time = date("H:i:s",strtotime($time));


    // getDistSum(1,$pos_array["DisId"]);

    return $curStId;


}

function getStartStation($trainId){


    global $connect;

    $tid =  $trainId;


    $query = "SELECT `StartSt` FROM `train_trip` WHERE `TrainID`= '$tid'";


    mysqli_query($connect, $query) or die (mysqli_error ($connect));


    $result = mysqli_query($connect, $query);
    $number_of_rows = mysqli_num_rows($result);


    if($number_of_rows > 0)
    {
        while($row = mysqli_fetch_assoc($result))
        {
            $startSt_array = $row;

        }
    }

    $startSt=$startSt_array["StartSt"];



    return $startSt;



}

function getDistSum($sid, $eid){

    global $connect;

    $stStrId = $sid;
    $stEndId = $eid;


    if ($stStrId>$stEndId) {

        $query = "SELECT SUM( `Distance` ) FROM  `distance_info` WHERE  `DisId` BETWEEN  $stEndId AND $stStrId";

    }else if($stStrId<$stEndId){

        $query = "SELECT SUM( `Distance` ) FROM  `distance_info` WHERE  `DisId` BETWEEN  $stStrId AND $stEndId";

    }else{

        throw new Exception("Values can't be equal");

    }


    mysqli_query($connect, $query) or die (mysqli_error ($connect));


    $result = mysqli_query($connect, $query);
    // $number_of_rows = mysqli_num_rows($result);

    // $temp_array = array();

    $row = mysqli_fetch_assoc($result);

    $distSum = $row['SUM( `Distance` )'];

    return $distSum;


    //header ('Content-Type: application/json');
    //echo json_encode(array("Position"=>$distSum));

    // mysqli_close($connect);

}

function getIdleTime($trainId){

    global $connect;

    $tid = $trainId;



    $query = "SELECT SUM( `IdleTime` ) FROM  `train_history` WHERE  `TrainID` = '$tid' AND `Date` >= CURDATE( )";



    mysqli_query($connect, $query) or die (mysqli_error ($connect));


    $result = mysqli_query($connect, $query);
    $number_of_rows = mysqli_num_rows($result);


    if($number_of_rows > 0)
    {
        while($row = mysqli_fetch_assoc($result))
        {
            $idle_array = $row;


        }
    }


    $idleTimeSum=$idle_array['SUM( `IdleTime` )'];
//
//
//
//    header ('Content-Type: application/json');
//    echo json_encode(array("Position"=>($idleTimeSum)));

    return $idleTimeSum;

    //getSpeed($distSum);

    // mysqli_close($connect);

}

function getSpeed($distance,$trainID,$sumIdleTime){

    global $connect;


    $idleTimeSum = $sumIdleTime;
    $dis = $distance;
    $trainId = $trainID;

    $query = " SELECT MAX( DATE_FORMAT( DATE,  '%H:%i:%s' ) ) AS
                       END , MIN( DATE_FORMAT( DATE,  '%H:%i:%s' ) ) AS
                       START
                       FROM train_history
                       WHERE  `TrainID` = $trainId
                       AND  `Date` >= CURDATE( )";


    mysqli_query($connect, $query) or die (mysqli_error ($connect));


    $result = mysqli_query($connect, $query);
    // $number_of_rows = mysqli_num_rows($result);

    // $temp_array = array();

    $row = mysqli_fetch_assoc($result);

    $end = $row['END'];
    $start = $row['START'];

    $t1 = strtotime($end);
    $t2 = strtotime($start);

    $diff = round(abs($t1-$t2)/60,2);

    $t3 = ($t1-$t2) - $idleTimeSum;
    $timedif = gmdate("H:i:s", $t3 );
    $speed = round(($dis*1000) / $t3)*3.6;

    return $speed;





//
//    header ('Content-Type: application/json');
//    echo json_encode(array("Position"=>$speed ));

    //   mysqli_close($connect);
    //   getUserSt();


}

function getUserSt ($userStation){

    global $connect;

    $userSt = $userStation;


    $query = " SELECT DisId FROM distance_info
               WHERE StartSt = '".$userSt."'";


    //$query = "SELECT * FROM  `train_history` WHERE `trainId`= $trainId ORDER BY `Date` DESC LIMIT 1";


    mysqli_query($connect, $query) or die (mysqli_error ($connect));


    $result = mysqli_query($connect, $query);
    $number_of_rows = mysqli_num_rows($result);


    if($number_of_rows > 0)
    {
        while($row = mysqli_fetch_assoc($result))
        {
            $userSt_array = $row;

        }
    }


    $userStId=$userSt_array["DisId"];


    // mysqli_close($connect);


    return $userStId;

    // remainDistance ($userStId,$curStId);

//    header ('Content-Type: application/json');
//    echo json_encode(array("Position"=>$curStId));



}

function remainDistance ($userStatId, $currentStatId){


    global $connect;


    $userSId = $userStatId;
    $curSID = $currentStatId;



    if ($userSId>$curSID) {

        $query = "SELECT SUM( `Distance` ) FROM  `distance_info` WHERE  `DisId` BETWEEN  '".$curSID."' AND '".$userSId."' ";

    }else if($userSId<$curSID){

        $query = "SELECT SUM( `Distance` ) FROM  `distance_info` WHERE  `DisId` BETWEEN  '".$userSId."' AND '".$curSID."'";

    }else{

        throw new Exception("Values can't be equal");

    }


    mysqli_query($connect, $query) or die (mysqli_error ($connect));


    $result = mysqli_query($connect, $query);

    $row = mysqli_fetch_assoc($result);

    $noOfStations = mysqli_num_fields($result);

    $estDistSum = $row['SUM( `Distance` )'];

    return $estDistSum;



//    header ('Content-Type: application/json');
//    echo json_encode(array($row['SUM( `Distance` )']));


}

function getSchId($stationID,$TrainID){

    global $connect;

    $sId = $stationID;
    $tId = $TrainID;



    $query = "SELECT `SchID` FROM  `train_sch` WHERE `SID` = '$sId' AND `TrainID` = '$tId' ";


    mysqli_query($connect, $query) or die (mysqli_error($connect));


    $result = mysqli_query($connect, $query);
    $number_of_rows = mysqli_num_rows($result);


    if ($number_of_rows > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $array = $row;


        }
    }

    $schId = $array['SchID'];
//
//
//
//    header ('Content-Type: application/json');
//    echo json_encode(array("Position"=>($idleTimeSum)));


    return $schId;
    //getSpeed($distSum);

}

function getNoOfStations($userStationId,$currentStationId,$trainId){

    global $connect;

    $userSId = $userStationId;
    $curSID = $currentStationId;
    $tid = $trainId;


    $query = "SELECT * FROM  `train_sch` WHERE `TrainID` = ' $tid' AND `SchID` BETWEEN  '$userSId' AND '$curSID'";


    mysqli_query($connect, $query) or die (mysqli_error($connect));


    $result = mysqli_query($connect, $query);
    $number_of_rows = mysqli_num_rows($result);


    if ($number_of_rows > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $array = $row;


        }
    }


    return $number_of_rows;


}

function estimateTime($speed,$estDistSum,$last_pos_time,$noOfStations)
{


    $tSpeed = $speed;
    $disSum = $estDistSum;
    $lastPosTIme = $last_pos_time;
    $nStations = $noOfStations;


    $estSec = ($disSum * 1000) / ($tSpeed / 3.6);
    $estTime = gmdate("H:i:s", $estSec);
    $arrival_time = $estSec + $lastPosTIme;
    $arTime = gmdate("H:i:s", $arrival_time);


    if ($nStations > 2) {

        $arTime2 = ($nStations * 90)+ $arrival_time;
        $arTime = gmdate("H:i:s", $arTime2);
    }


    return $arTime;


}

function getSchArrivalTime($trainId,$stationId){


    global $connect;

    $sid = $stationId;
    $tid = $trainId;


    $query = "SELECT * FROM  `train_sch` WHERE `TrainID` = ' $tid' AND `SID` = '$sid'";


    mysqli_query($connect, $query) or die (mysqli_error($connect));


    $result = mysqli_query($connect, $query);
    $number_of_rows = mysqli_num_rows($result);


    if ($number_of_rows > 0) {
        while ($row = mysqli_fetch_assoc($result)) {

            $array = $row["ArrivalTime"];


        }
    }


    return $array;

}

function getCurrentDelay($schArrivalTime,$ActualArrivalTime){

    $schTime = $schArrivalTime;
    $actTime = $ActualArrivalTime;

    $delay = 0;

    if($schTime<$actTime){

        $delay = $actTime - $schTime;


    }
    $delayTime = gmdate("H:i:s", $delay);

    return $delay;

}


function getStartIndex($StartStation){

    global $connect;

    $StartSt = $StartStation;

    $query = "SELECT `SID`
              FROM  `station`
              WHERE SName = '$StartSt'";

    mysqli_query($connect, $query) or die (mysqli_error ($connect));


    $result = mysqli_query($connect, $query);
    $number_of_rows = mysqli_num_rows($result);


    if($number_of_rows > 0)
    {
        //create an array
        while($row =mysqli_fetch_assoc($result)){

            $startId = $row['SID'];
        }

    }

    return $startId;
}

function searchValidation($stationId, $trainId){

    global $connect;

    $stId = $stationId;
    $tId = $trainId;
    $searchResult = false;

    $query = "SELECT *
              FROM  `train_history`
              WHERE SID = $stId AND $tId = $trainId";

    mysqli_query($connect, $query) or die (mysqli_error ($connect));


    $result = mysqli_query($connect, $query);
    $number_of_rows = mysqli_num_rows($result);


    if($number_of_rows > 0)
    {
        $searchResult =  true;

    }

    return $searchResult;
}


?>
