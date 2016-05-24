<?php
function begin_session(){
	session_start();
}

function end_session(){
	$_SESSION = array();
	setcookie ("username","",time()-42000);
	if (isset($_COOKIE[session_name()])) {
		setcookie(session_name(), '', time()-42000, '/');
	}
	session_destroy();
}

function connect_db(){
	global $connection;
	$host="localhost";
	$user="xxx";
	$pass="xxx";
	$db="map";
	$connection = mysqli_connect($host, $user, $pass, $db) or die("Couldn't connect to database.");
	mysqli_query($connection, "SET CHARACTER SET UTF8") or die("Couldn't get database in UTF8 - ".mysqli_error($connection));
}

function show_map(){
	include("view/map.html");
}

function show_locations(){
	if (isset($_SESSION['logged_in'])){
		include("view/locations.html");
	} else {
		$_SESSION['notification'] = "Please log in to see your locations!";
		header('Location:?mode=login');
	}	
}

function login(){
	global $connection;
	global $error;
	if (!empty ($_POST)){
		$username = mysqli_real_escape_string($connection, htmlspecialchars($_POST['username']));
		$password = mysqli_real_escape_string($connection, htmlspecialchars($_POST['password']));
		if (empty($username) OR empty($password)){
			$error = "Username and/or password missing! <br> \n";
			include('view/login.html');
		} else {
			$password = sha1($password);
			$query = "select * from users where username='$username' and passwrd='$password'";
			$result = mysqli_query($connection, $query) or die("Error: ".mysqli_error($connection));
			$rows = mysqli_num_rows($result); //Check if the user exists in database
			if ($rows){
				$_SESSION['logged_in'] = 1;
				$_SESSION['notification'] = "You are now logged in!";
				setcookie("username",$username);
				$value = mysqli_fetch_object($result);
				$_SESSION['id'] = $value->id;
				create_locations_db($username);
				header('Location:?mode=map');
			} else {				
				$error = "Username/password is incorrect! <br> \n";
				include("view/login.html");
			}
		}
	} else {
		include('view/login.html');
	}
}

function create_locations_db($username) {
	global $connection;
	$tablename = $username.'_locations';
	$query = "show tables like '$tablename'"; //check if table already exists
	$result = mysqli_query($connection, $query) or die("Error: ".mysqli_error($connection));
	$rows = mysqli_num_rows($result);
	//$_SESSION['notification'] = "Rows: ".$rows;
	if (!$rows) {
		$query = "create table $tablename (country varchar(60) not null, city varchar(60) not null, lat float (10,6) not null, lng float (10,6) not null, comment varchar(300), id int auto_increment primary key)";
		mysqli_query($connection, $query) or die("Error: ".mysqli_error($connection));
	}
}

function add_location() {
	global $connection; 
	if (isset($_COOKIE['username'])) {
		$lat = $_POST['lat'];
		$lng = $_POST['lng'];
		$country = $_POST['country'];
		$city = $_POST['city'];
		$table = $_COOKIE['username']."_locations";
		$query = "insert into $table (country,city,lat,lng) values ('$country','$city','$lat', $lng)";
		mysqli_query($connection, $query) or die("Error: ".mysqli_error($connection));
		echo "Information added to database.";
	}
	
}

function save_comment($id, $comment){
	$comment = htmlspecialchars($comment);
	global $connection;
	if (isset($_COOKIE['username'])){
		$table = $_COOKIE['username']."_locations";
		$query = "update $table set comment='$comment' where id=$id";
		mysqli_query($connection, $query) or die("Error: ".mysqli_error($connection));
	}
}

function delete_location($id){
	global $connection;
	if (isset($_COOKIE['username'])) {
		$table = $_COOKIE['username']."_locations";
		$query = "delete from $table where id=$id";
		mysqli_query($connection, $query) or die("Error: ".mysqli_error($connection));
	}
}

function delete_all_locations(){
	global $connection;
	if (isset($_COOKIE['username'])) {
		$table = $_COOKIE['username']."_locations";
		$query = "truncate table $table";
		mysqli_query($connection, $query) or die("Error: ".mysqli_error($connection));
	}
}

function get_locations() {
	global $connection;
	$table = $_COOKIE['username']."_locations";
	$query = "select * from $table";
	$result = mysqli_query($connection, $query) or die("Error: ".mysqli_error($connection));
	$locations = [];
	while ($line = mysqli_fetch_array($result)){ 
		$country = $line['country'];
		$city = $line['city'];		
		$lat = $line['lat'];
		$lng = $line['lng'];
		$comment = $line['comment'];
		$id = $line['id'];
		$location = [$country, $city, $lat, $lng, $comment, $id];
		array_push($locations,$location);
	}
	return $locations;
}

function show_about(){
	include("view/about.html");
}

function register(){
	global $error;
	global $connection;
	if (!empty ($_POST)){
		$username = mysqli_real_escape_string($connection, htmlspecialchars($_POST['new_username']));
		$password = mysqli_real_escape_string($connection, htmlspecialchars($_POST['new_password']));
		$password2 = mysqli_real_escape_string($connection, htmlspecialchars($_POST['confirm_password']));
		if (empty($username) OR empty($password) OR empty($password2)){
			$error = "Please fill in all the fields!";
			include('view/register.html');
		} else if (strlen($password) < 6) { //check if password is long enough
			$error = "Password must be at least 6 characters long!";
			include('view/register.html');
		} else if (strcmp($password,$password2) !== 0) { //check if the passwords match
			$error = "Inserted passwords are different!";
			include('view/register.html');
		} else {
			$query = "select * from users where username='$username'";
			$result = mysqli_query($connection, $query) or die("Error: ".mysqli_error($connection));
			$rows = mysqli_num_rows($result); //check if username already exists
			if ($rows) {
				$error = "Username already exists!";
				include('view/register.html');
			} else {
				$password = sha1($password);
				$query = "insert into users (username,passwrd) values ('$username','$password')";
				mysqli_query($connection, $query) or die("Error: ".mysqli_error($connection));
				if (mysqli_insert_id($connection) > 0) {
					$_SESSION['notification'] = "You have been registered!";
					header('Location:?mode=login');
				}
			}
		}
	} else {
		include("view/register.html");
	}
}

function show_contact(){
	include("view/contact.html");
}

function logout(){
	end_session();
	$_SESSION['notification'] = "You have been logged out!";
	header('Location:?');
}

function DECtoDMS($dec){
	$vars = explode(".",$dec);
    $deg = $vars[0];
    $tempma = "0.".$vars[1];

    $tempma = $tempma * 3600;
    $min = floor($tempma / 60);
    $sec = $tempma - ($min*60);
	
	$dms = $deg.'° '.$min.'\' '.round($sec,2).'"';

    return $dms;
}

?>