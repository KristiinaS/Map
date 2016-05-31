<?php 
include_once("functions.php");
connect_db();
session_start();
$username = $_SESSION['username'];

if (isset($_POST['lat']) AND isset($_POST['lng']) AND isset($_POST['country']) AND isset($_POST['city'])) {
	add_location();
} 

if (isset($_POST['show_locations'])) {
	$answer = json_encode(get_locations());
	print_r($answer);
	
} 

if (isset($_POST['save_comment'])) {
	$id = $_POST['id'];
	$comment = $_POST['comment'];
	save_comment($id, $comment);
}

if (isset($_POST['delete_all'])){
	delete_all_locations();
	setcookie('notification', 'All locations have been deleted!');
}

if (isset($_POST['delete'])){
	$id = $_POST['id'];
	delete_location($id);
	setcookie('notification', 'Location has been removed! <br>');
}
?>