<?php 
include_once("functions.php");
connect_db();

if (isset($_POST['lat']) AND isset($_POST['lng'])) {
	add_location();
} 

if (isset($_POST['show_locations'])) {
	$answer = json_encode(get_locations());
	print_r($answer);
	
} 

if (isset($_POST['delete'])){
	$lat = $_POST['delete_lat'];
	$lng = $_POST['delete_lng'];
	delete_location($lat,$lng);
	setcookie('notification', 'Location has been removed! <br>');
}
?>