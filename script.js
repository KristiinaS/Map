var map;
var markers = [];
function initMap() {
	map = new google.maps.Map(document.getElementById('map'), {
		center: {lat: 58.486, lng: 25.461},
		zoom: 7
	});
	
	/*
	var marker;
	marker = new google.maps.Marker({
		map: map,
		position: {lat: 58.486, lng: 25.461}
	});
	*/
	
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
	//alert(location);
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