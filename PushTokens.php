<?php
/**
 * Created by PhpStorm.
 * User: NovA
 * Date: 3/21/2016
 * Time: 10:29 AM
 */




// TO DO : CHANGE THE QUERY. LONG AND LAT WILL SAVE ON DIF TABLE

if($_SERVER["REQUEST_METHOD"]=="GET")
{
    require 'Connection.php';
    getTokens();
}



function getTokens()
{

    global $connect;

    $alias = $_GET["Alias"];
    $trainId = $_GET["TrainId"];


    $query = "SELECT * FROM `pushbot` WHERE `Alias` = '$alias'";

    mysqli_query($connect, $query) or die (mysqli_error ($connect));


    $result = mysqli_query($connect, $query);
    $number_of_rows = mysqli_num_rows($result);



    if($number_of_rows > 0)
    {
        updateToken($alias,$trainId);
    }
    else
    {

        header ('Content-Type: application/json');
        echo json_encode(array($trainId));

        insertToken($alias,$trainId);
    }



    mysqli_close($connect);



}

function updateToken($Alias, $TrainID){


    global $connect;


     $alias = $Alias;
     $trainId = $TrainID;



    $query = "UPDATE `pushbot` SET `TrainId`='$trainId' WHERE `Alias` ='$alias'";


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
function insertToken($Alias, $TrainID){


    global $connect;


    $alias = $Alias;
    $trainId = $TrainID;



    $stmt = $connect->prepare("INSERT INTO `pushbot`(`Alias`, `TrainID` )
             VALUES (?,?)");

    $stmt->bind_param("si",$alias,$trainId);

    $stmt->execute();
    $stmt->close();



}


