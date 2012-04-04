$(document).ready(function() {
	$("#geoButton").click(function() {
		if (navigator.geolocation) {
			navigator.geolocation.getCurrentPosition( 
				function (position) {
					// address.city, address.country, address.countryCode, address.postalCode, address.street, address.streetNumber
					data = "positionLatitude="+position.coords.latitude+"&";
					data += "positionLongitude="+position.coords.longitude+"&";

					$.ajax({
						type: "POST",
						url: baseUrl+"/ajax/save/geo/"+$.cookie("ID"),
						data: data
					});
				}
			);
		}
	});
});
