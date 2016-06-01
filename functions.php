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
	mysqli_query($connection, "SET CHARACTER SET UTF8") or die(/*"Couldn't get database in UTF8 - ".mysqli_error($connection*/ "Error 100: Oops! Something went wrong!")/*)*/;
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
			$query = "select * from users where username='$username'";
			$result = mysqli_query($connection, $query) or die("Error 101: Oops! Something went wrong!");
			$username_exists = false;
			if ($line = mysqli_fetch_array($result)){ //Check if the user exists in database
				$db_password = $line['passwrd'];
				if (password_verify($password, $db_password)) {
					$username_exists = true;
				}
			}
			
			if ($username_exists) {
				$_SESSION['logged_in'] = 1;
				$_SESSION['notification'] = "You are now logged in!";
				$_SESSION['username'] = $username;
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
	$result = mysqli_query($connection, $query) or die("Error 102a: Oops! Something went wrong!");
	$rows = mysqli_num_rows($result);
	//$_SESSION['notification'] = "Rows: ".$rows;
	if (!$rows) {
		$query = "create table $tablename (country varchar(60) not null, city varchar(60) not null, lat float (10,6) not null, lng float (10,6) not null, comment varchar(300), id int auto_increment primary key)";
		mysqli_query($connection, $query) or die("Error 102b: Oops! Something went wrong!");
	}
}

function add_location() {
	global $connection; 
	$username = mysqli_real_escape_string($connection, htmlspecialchars($_SESSION['username']));
	if (isset($username)) {
		$lat = mysqli_real_escape_string($connection,htmlspecialchars($_POST['lat']));
		$lng = mysqli_real_escape_string($connection,htmlspecialchars($_POST['lng']));
		$country = mysqli_real_escape_string($connection,htmlspecialchars($_POST['country']));
		$city = mysqli_real_escape_string($connection,htmlspecialchars($_POST['city']));
		$table = $username."_locations";
		$query = "insert into $table (country,city,lat,lng) values ('$country','$city','$lat', $lng)";
		mysqli_query($connection, $query) or die("Error 103: Oops! Something went wrong!");
		echo "Information added to database.";
	}
	
}

function save_comment($id, $comment){
	global $connection;
	$id = mysqli_real_escape_string($connection, htmlspecialchars($id));
	$comment = mysqli_real_escape_string($connection, htmlspecialchars($comment));
	$username = mysqli_real_escape_string($connection, htmlspecialchars($_SESSION['username']));
	if (isset($username)){
		$table = $username."_locations";
		$query = "update $table set comment='$comment' where id=$id";
		mysqli_query($connection, $query) or die("Error 104: Oops! Something went wrong!");
	}
}

function delete_location($id){
	global $connection;
	$id = mysqli_real_escape_string($connection, htmlspecialchars($id));
	$username = mysqli_real_escape_string($connection, htmlspecialchars($_SESSION['username']));
	if (isset($username)) {
		$table = $username."_locations";
		$query = "delete from $table where id=$id";
		mysqli_query($connection, $query) or die("Error 105: Oops! Something went wrong!");
	}
}

function delete_all_locations(){
	global $connection;
	$username = mysqli_real_escape_string($connection, htmlspecialchars($_SESSION['username']));
	if (isset($username)) {
		$table = $username."_locations";
		$query = "truncate table $table";
		mysqli_query($connection, $query) or die("Error 106: Oops! Something went wrong!");
	}
}

function get_locations() {
	global $connection;
	$username = mysqli_real_escape_string($connection, htmlspecialchars($_SESSION['username']));
	$table = $username."_locations";
	$query = "select * from $table";
	$result = mysqli_query($connection, $query) or die("Error 107: Oops! Something went wrong!");
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
		} else if (strlen($username) > 40) {
			$error = "Username cannot be more than 40 characters!";
			include('view/register.html');
		} else if (!ctype_alnum($username)) {
			$error = "Username can only contain letters and numbers!";
			include('view/register.html');
		} else if (strlen($password) < 6) { //check if password is long enough
			$error = "Password must be at least 6 characters long!";
			include('view/register.html');
		} else if (strcmp($password,$password2) !== 0) { //check if the passwords match
			$error = "Inserted passwords are different!";
			include('view/register.html');
		} else {
			$query = "select * from users where username='$username'";
			$result = mysqli_query($connection, $query) or die("Error 108a: Oops! Something went wrong!");
			$rows = mysqli_num_rows($result); //check if username already exists
			if ($rows) {
				$error = "Username already exists!";
				include('view/register.html');
			} else {
				$password = password_hash($password, PASSWORD_BCRYPT);
				$query = "insert into users (username,passwrd) values ('$username','$password')";
				mysqli_query($connection, $query) or die("Error 108b: Oops! Something went wrong!");
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