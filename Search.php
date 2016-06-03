<?php

if($_SERVER["REQUEST_METHOD"]=="GET")
{
    require 'Connection.php';
    mainFunc();
}

$rCheck = false;


function mainFunc(){


    global $rCheck;
    $StartSt = $_GET["startSt"];
    $EndSt = $_GET["endSt"];
    $arrivalTimeStart = $_GET["StartTime"];
    $arrivalTimeEnd = $_GET["EndTime"];
    $opDay = $_GET["OpDay"];

   


    $startId = getStartIndex ($StartSt);
    $endId = getEndIndex($EndSt);
    $unqId = getUniqID($startId,$endId);
    


    if($opDay == "All"){

        $results = getResultsAll($startId,$StartSt,$EndSt,$unqId,$arrivalTimeStart,$arrivalTimeEnd);

    }
    else{

        $results = getResults($startId,$StartSt,$EndSt,$unqId,$arrivalTimeStart,$arrivalTimeEnd,$opDay);
        
    }

    if ($rCheck === true){

        header ('Content-Type: application/json');
        echo json_encode(array("Result"=>"No" ));


    }



    header ('Content-Type: application/json');
    echo json_encode(array("Result"=>$results ));



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

function getEndIndex($EndStation){

    global $connect;

    $endSt = $EndStation;

    $query = "SELECT `SID`
              FROM  `station`
              WHERE SName = '$endSt'";

    mysqli_query($connect, $query) or die (mysqli_error ($connect));


    $result = mysqli_query($connect, $query);
    $number_of_rows = mysqli_num_rows($result);

    //$index_array = array();

    if($number_of_rows > 0)
    {
        //create an array
        while($row =mysqli_fetch_assoc($result)){

            $endId = $row['SID'];
        }

    }



    return $endId;
    //getArrival($startId);




}

function getUniqID($StartStationId,$EndStationId){


    $startId = $StartStationId;
    $endId = $EndStationId;


    if($startId<$endId){

        $unqId = 1;

    }elseif($startId>$endId) {

        $unqId = 2;
    }else{

        echo ("Error");
    }

    return $unqId;

}


function getResults($startStId,$startStation,$endStation,$uniqueId,$arrivalTimeStart,$arrivalTimeEnd,$operateDay)
{

    global $connect;
    global $rCheck;

    $startId = $startStId;
    $startSt = $startStation;
    $endSt = $endStation;
    $unqId = $uniqueId;
    $arrivalSt1 = $arrivalTimeStart;
    $arrivalSt2 = $arrivalTimeEnd;
    $opDay = $operateDay;



    $query = "SELECT `TripID`
              FROM  `train_trip`
              WHERE Stations REGEXP  '$startSt'
              AND Stations REGEXP  '$endSt'
              AND `UnqID` = '$unqId'";

    mysqli_query($connect, $query) or die (mysqli_error ($connect));


    $result = mysqli_query($connect, $query);
    $number_of_rows = mysqli_num_rows($result);

    $tripId = array();

    if($number_of_rows > 0)
    {
        //create an array
        while($id =mysqli_fetch_array($result)){

            $tripId[]= $id["TripID"];

        }


    }
    else{

         $rCheck = true;


    }

    $av = array();


    foreach($tripId as $id){



        $query = "SELECT train_trip.TripID, train_trip.TrainID, train_trip.StartSt, train_trip.StartStTime, train_trip.EndSt, train_trip.EndStTime, train_trip.TrainType, train_sch.SID, train_sch.ArrivalTime, train_sch.DepTime, train_sch_info.OpDay
            FROM  `train_trip`
            INNER JOIN train_sch ON train_sch.TripID = train_trip.TripID
            INNER JOIN train_sch_info ON train_sch_info.TripID = train_trip.TripID
            WHERE  `ArrivalTime`
            BETWEEN '$arrivalSt1'
            AND  '$arrivalSt2'
            AND train_trip.TripID ='$id'
             AND train_sch_info.OpDay ='$opDay'
            AND train_sch.SID = '$startId'";


        mysqli_query($connect, $query) or die (mysqli_error ($connect));

        $result = mysqli_query($connect, $query);

        while ( $row = mysqli_fetch_assoc( $result ) ) {
            $array [] = $row;

        }


        array_push( $av, $array);

        //  $unique = array_map("unserialize", array_unique(array_map("serialize", $av)));


    }

    $a=array();

    foreach($av as $k=>$v){
        foreach($v as $key=>$value){
            if(!in_array($value, $a)){
                $a[]=$value;
            }
        }
    }
    return $a;

//    header ('Content-Type: application/json');
//    echo json_encode(array($av));

    mysqli_close($connect);

}

function getResultsAll($startStId,$startStation,$endStation,$uniqueId,$arrivalTimeStart,$arrivalTimeEnd)
{

    global $connect;
    global $rCheck;

    $startId = $startStId;
    $startSt = $startStation;
    $endSt = $endStation;
    $unqId = $uniqueId;
    $arrivalSt1 = $arrivalTimeStart;
    $arrivalSt2 = $arrivalTimeEnd;




    $query = "SELECT `TripID`
              FROM  `train_trip`
              WHERE Stations REGEXP  '$startSt'
              AND Stations REGEXP  '$endSt'
              AND `UnqID` = '$unqId'";

    mysqli_query($connect, $query) or die (mysqli_error ($connect));


    $result = mysqli_query($connect, $query);
    $number_of_rows = mysqli_num_rows($result);

    $tripId = array();

    if($number_of_rows > 0)
    {
        //create an array
        while($id =mysqli_fetch_array($result)){

            $tripId[]= $id["TripID"];

        }


    }
    else{

        $rCheck = true;


    }

    $av = array();


    foreach($tripId as $id){



        $query = "SELECT train_trip.TripID, train_trip.TrainID, train_trip.StartSt, train_trip.StartStTime, train_trip.EndSt, train_trip.EndStTime, train_trip.TrainType, train_sch.SID, train_sch.ArrivalTime, train_sch.DepTime, train_sch_info.OpDay
            FROM  `train_trip`
            INNER JOIN train_sch ON train_sch.TripID = train_trip.TripID
            INNER JOIN train_sch_info ON train_sch_info.TripID = train_trip.TripID
            WHERE  `ArrivalTime`
            BETWEEN '$arrivalSt1'
            AND  '$arrivalSt2'
            AND train_trip.TripID ='$id'
            AND train_sch.SID = '$startId'";


        mysqli_query($connect, $query) or die (mysqli_error ($connect));

        $result = mysqli_query($connect, $query);

        while ( $row = mysqli_fetch_assoc( $result ) ) {
            $array [] = $row;

        }


        array_push( $av, $array);

        //  $unique = array_map("unserialize", array_unique(array_map("serialize", $av)));


    }

    $a=array();

    foreach($av as $k=>$v){
        foreach($v as $key=>$value){
            if(!in_array($value, $a)){
                $a[]=$value;
            }
        }
    }
    return $a;

//    header ('Content-Type: application/json');
//    echo json_encode(array($av));

    mysqli_close($connect);

}



function isEmpty($a){
    foreach($a as $elm)
        if(!empty($elm)){
            return false;
        }else{

            return true;
        }

}

?>
