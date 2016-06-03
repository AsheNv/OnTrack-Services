<?php

if($_SERVER["REQUEST_METHOD"]=="GET")
{
    require 'Connection.php';
    mainFunction();
}


function mainFunction(){

    $trainId = $_GET["trainId"];
    $stName = $_GET["startStationName"];
    $Arrival = $_GET["ArrivalTime"];


    $firstWeek = getFirstWeekTime($stName,$trainId);
    $secondWeek = getSecondWeekTime($stName,$trainId);
    $thirdWeek = getThirdWeekTime($stName,$trainId);

    $firstMs = strtotime ($firstWeek);
    $secondMs = strtotime ($secondWeek);
    $thirdMs = strtotime ($thirdWeek);

    $arrivalMs = strtotime ($Arrival);

    $predictPoints = getPrediction($stName,$trainId,$arrivalMs,$firstMs,$secondMs,$thirdMs);

    $percentage = makePrediction($predictPoints);



    $result_array = array("Prediction"=>round($percentage,2), "Average"=>$secondMs, "Points" => $predictPoints);
    header ('Content-Type: application/json');
    echo json_encode(array("Result" => $result_array));

}


function getFirstWeekTime($stationName,$trainID)
{

    global $connect;

    $stName = $stationName;
    $tid = $trainID;


    $query = "SELECT DATE_FORMAT(  `Date` ,  '%H:%i:%s' ) TIMEONLY FROM `train_history` WHERE DATE(Date) = DATE_SUB(CURDATE(), INTERVAL 7 DAY) "
        . "AND `StationName` = '$stName' AND `TrainID`= $tid";


    mysqli_query($connect, $query) or die (mysqli_error($connect));


    $result = mysqli_query($connect, $query);
    $number_of_rows = mysqli_num_rows($result);


    if ($number_of_rows > 0) {
        //create an array
        while ($row = mysqli_fetch_assoc($result)) {

            $first_date = $row ['TIMEONLY'];
        }


    }

    return $first_date;

}

function getSecondWeekTime($stationName,$trainID)
{

    global $connect;

    $stName = $stationName;
    $tid = $trainID;


    $query = "SELECT DATE_FORMAT(  `Date` ,  '%H:%i:%s' ) TIMEONLY FROM `train_history` WHERE DATE(Date) = DATE_SUB(CURDATE(), INTERVAL 14 DAY) "
        . "AND `StationName` = '$stName' AND `TrainID`= $tid";


    mysqli_query($connect, $query) or die (mysqli_error($connect));


    $result = mysqli_query($connect, $query);
    $number_of_rows = mysqli_num_rows($result);


    if ($number_of_rows > 0) {
        //create an array
        while ($row = mysqli_fetch_assoc($result)) {

            $second_date = $row ['TIMEONLY'];
        }


    }

    return $second_date;

}

function getThirdWeekTime($stationName,$trainID)
{

    global $connect;

    $stName = $stationName;
    $tid = $trainID;


    $query = "SELECT DATE_FORMAT(  `Date` ,  '%H:%i:%s' ) TIMEONLY FROM `train_history` WHERE DATE(Date) = DATE_SUB(CURDATE(), INTERVAL 21 DAY) "
        . "AND `StationName` = '$stName' AND `TrainID`= $tid";


    mysqli_query($connect, $query) or die (mysqli_error($connect));


    $result = mysqli_query($connect, $query);
    $number_of_rows = mysqli_num_rows($result);


    if ($number_of_rows > 0) {
        //create an array
        while ($row = mysqli_fetch_assoc($result)) {

            $third_date = $row ['TIMEONLY'];
        }


    }

    return $third_date;
}



function getPrediction($stationName, $trainID, $arrivalTime, $firstWeekTime, $secondWeekTIme,$thirdWeekTime)
{

    global $connect;
    $stName = $stationName;
    $trainId = $trainID;
    $arrivalTimeMs = $arrivalTime;
    $first = $firstWeekTime;
    $second = $secondWeekTIme;
    $third = $thirdWeekTime;

    $secondArray =  array();


    $points = 0;



    $query = "SELECT DATE_FORMAT(  `Date` ,  '%H:%i:%s' ) TIMEONLY FROM `train_history` WHERE DATE > DATE_SUB( NOW( ) , INTERVAL 15 DAY ) AND `StationName` = '".$stName."' AND `TrainID`= $trainId";

    mysqli_query($connect, $query) or die (mysqli_error ($connect));


    $result = mysqli_query($connect, $query);
    $number_of_rows = mysqli_num_rows($result);

    $temp_array = array();

    if($number_of_rows > 0)
    {
        //create an array
        while($row =mysqli_fetch_assoc($result)){

            $temp_array[] = $row ['TIMEONLY'];
        }




    }




    $arrLength = count($temp_array);

    for($x = 0; $x < $arrLength; $x++) {


        $secondArray [] = strtotime ($temp_array[$x]);


    }


    $arrLengthSecond = count($secondArray);
    $delayRange = ($arrivalTimeMs + 300);



    for($x = 0; $x < $arrLengthSecond; $x++) {


        if ($secondArray[$x] > $delayRange){


            $points = $points + 1;
        }




    }

    if ($first > $delayRange && $second > $delayRange   ) {



        if ($third >$delayRange )
        {
            $points = $points + 20;

        }
        else{

            $points = $points + 10;
        }
    }



    return $points;

}

function makePrediction($points){

    $pts = $points;
    $totalPts = 34;

    $percentage = $pts/$totalPts*100;

    return $percentage;


}
function getAverage($array) {

    return date('H:i:s', array_sum(array_map('strtotime', $array)) / count($array));
}





