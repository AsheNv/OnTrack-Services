<?php

if($_SERVER["REQUEST_METHOD"]=="GET")
{
require 'Connection.php';
getPosition();
}
     


function getPosition()
{

	global $connect;

	$trainId = $_GET["trainId"];
         

	$query = "SELECT * FROM current_pos WHERE `trainId`= $trainId ORDER BY posId DESC LIMIT 1";
        
        mysqli_query($connect, $query) or die (mysqli_error ($connect));
        
        
	$result = mysqli_query($connect, $query);
	$number_of_rows = mysqli_num_rows($result);
	
	$temp_array = array();
        
        
	$delay = 10;
        $eta = 30;
        
	if($number_of_rows > 0)
	{
	while($row = mysqli_fetch_assoc($result))
		{
		$temp_array[] = $row;
		
		}	
	}
	$result_array = array("Delay Time"=>$delay,"ETA"=>$eta);
	header ('Content-Type: application/json');
	echo json_encode(array("Position"=>$temp_array, "Delay"=>$result_array));
       
	mysqli_close($connect);
	
	

}


?>
