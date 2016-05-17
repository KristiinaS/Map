<?php 
include_once("functions.php");
begin_session();
connect_db();

$mode = "";
if (isset($_GET['mode'])) {
	$mode = $_GET['mode'];
}

include_once("view/head.html");
switch($mode){
	case 'map':
		show_map();
		break;
	case 'login':
		login();
		break;
	case 'about':
		show_about();
		break;
	case 'register':
		register();
		break;
	case 'contact':
		show_contact();
		break;
	case 'locations':
		show_locations();
		break;
	case 'logout':
		logout();
	default:
		include("view/home.html");
}
include_once("view/foot.html");

?>