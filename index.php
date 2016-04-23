<?php 

$mode = "";
if (isset($_GET['mode'])) {
	$mode = $_GET['mode'];
}

include_once("view/head.html");
switch($mode){
	case 'map':
		include("view/map.html");
		break;
	case 'login':
		include("view/login.html");
		break;
	case 'about':
		include("view/about.html");
		break;
	case 'register':
		include("view/register.html");
		break;
	case 'contact':
		include("view/contact.html");
		break;
	case 'logout':
		header('Location: ?');
	default:
		include("view/home.html");
}
include_once("view/foot.html");

?>