var map;
var markers = [];
function initMap() {
	map = new google.maps.Map(document.getElementById('map'), {
		center: {lat: 37.486, lng: 14.461},
		zoom: 2
	});
	
	map.addListener("click", function(event) {
		addMarker(event.latLng);
	});
}

function addMarker(location){
	var marker = new google.maps.Marker({
		position:location,
		map:map
	});
	markers.push(marker);
	
	var answer = String(location).split(',');
	var lat = answer[0].substring(1);
	var lng = answer[1].substring(0,answer[1].length-1);
	var latLng = new google.maps.LatLng(lat,lng);
	var country;
	
	new google.maps.Geocoder().geocode({'latLng':latLng}, function(results, status){
		if (status == google.maps.GeocoderStatus.OK) {
			if (results[0]) {
				country = getCountry(results);
				city = getCity(results);
				if (city == "") {
					city = "-";
					console.log("city: ", city);
				}
				
				//write coordinates to database with country
				$.ajax({
					type: "POST",
					url: 'ajax.php',
					data: {lat:lat, lng:lng, country:country, city:city},
					success: function(data){console.log(data)}		
				});
			}
		} else {
			//write coordinates to database with "Unknown" location
			$.ajax({
				type: "POST",
				url: 'ajax.php',
				data: {lat:lat, lng:lng, country:"Unknown", city:"-"},
				success: function(data){console.log(data)}		
			});
		}
	});

}

function getCity(results){
	if (results[1] == undefined) {
		return "Unknown";
	} else {
		for (var i = 0; i < results[1].address_components.length; i++){
			var longname = results[1].address_components[i].long_name;
			var type = results[1].address_components[i].types;
			if (type.indexOf("locality") != -1) {
				return longname;
			} else if (type.indexOf("administrative_area_level_1") != -1){
				return longname;
			} else if (i == results[1].address_components.length-1) {
				return "Unknown";
			}
		}
	}	
}

function getCountry(results){
	for (var i = 0; i < results[0].address_components.length; i++){
		var longname = results[0].address_components[i].long_name;
		var type = results[0].address_components[i].types;
		if (type.indexOf("country") != -1){
			return longname;
		}
	}
}

// Sets the map on all markers in the array.
function setMapOnAll(map) {
	for (var i = 0; i < markers.length; i++) {
	markers[i].setMap(map);
	}
}

// Removes the markers from the map, but keeps them in the array.
function clearMarkers() {
	setMapOnAll(null);
}

// Shows any markers currently in the array.
function showMarkers() {
	setMapOnAll(map);
}

// Deletes all markers in the array by removing references to them.
function deleteMarkers(){
	clearMarkers();
	markers = [];
}

// Deletes locations from the list and database
function deleteLocation(id){
	var click = confirm("Are you sure you want to delete this location?");
	if (click == true) {
		$.post("ajax.php", {'delete':1, 'id':id}, function(){
			location.reload();	
		});
	}
}

function saveComment(id){
	var comment = document.getElementById("comment"+id).value;
	//console.log("JS comment: ", comment);
	//console.log("id: ", id);
	$.post("ajax.php", {'save_comment':1, 'id':id, 'comment':comment}, function(resp){
		console.log(resp);
	});
}

function deleteAllLocations(){
	var click = confirm("Are you sure you want to delete all your locations?");
	if (click == true) {
		$.post("ajax.php", {'delete_all':1}, function(){
			location.reload();	
		});
	}
}

//Show all locations saved in database
function showMyLocations(){	
	var marker;

	$.post("ajax.php", {show_locations:1}, function(resp){
		var locations = JSON.parse(resp);
		i = 0;
		while (i < locations.length) {
			var coordinates = {lat: parseFloat(locations[i][2]), lng: parseFloat(locations[i][3])};
			marker = new google.maps.Marker({
				position: coordinates,
				map: map
			})
			markers.push(marker);
			marker.setMap(map);
			i++;
		}
	});

}


