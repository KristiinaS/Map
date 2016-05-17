<?php
function begin_session(){
	session_start();
}

function end_session(){
	$_SESSION = array();
	if (isset($_COOKIE[session_name()])) {
		setcookie(session_name(), '', time()-42000, '/');
	}
	session_destroy();
}

function show_map(){
	include("view/map.html");
}

function show_locations(){
	if (isset($_SESSION['logged_in'])){
		include("view/locations.html");
	} else {
		$_SESSION['teade'] = "Please log in to see your locations!";
		header('Location:?mode=login');
	}	
}

function login(){
	include("view/login.html");
}

function show_about(){
	include("view/about.html");
}

function show_register(){
	include("view/register.html");
}

function show_contact(){
	include("view/contact.html");
}

function logout(){
	end_session();
	header('Location: ?');
}

?>