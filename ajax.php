<?php 
include_once("functions.php");

if (isset($_POST['lat']) AND isset($_POST['lng'])) {
	add_location();
} 

if (isset($_POST['show_locations'])) {
	$answer = get_locations();
	print_r($answer);
	/*?>
	<script type="text/javascript">
		var locations = <?php echo json_encode($answer); ?>;
	</script>
	<?php*/
	
} 

if (isset($_POST['delete'])){
	$lat = $_POST['delete_lat'];
	$lng = $_POST['delete_lng'];
	delete_location($lat,$lng);
}
?>