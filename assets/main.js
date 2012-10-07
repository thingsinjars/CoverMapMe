$(window).on('load', function() {
	$('#mapContainer')
	.on('maploaded', function() {
      goToUserPosition();
    })
    .jOVI({
    	autoload: false,
		center: [38.895111, -77.036667],		// Washington D.C.
		zoom: 12,		// zoom level
		behavior: true,		// map interaction
		zoomBar: true,		// zoom bar
		scaleBar: false,		// scale bar at the bottom
		overview: false,		// minimap (bottom-right)
		typeSelector: false,		// normal, satellite, terrain
		positioning: true // geolocation
	}, "_peU-uCkp-j8ovkzFGNU", "gBoUkAMoxoqIWfxWA5DuMQ");
});

function goToUserPosition() {
	if (nokia.maps.positioning.Manager) {
		var positioning = new nokia.maps.positioning.Manager();

		// Gets the current position, if available the first given callback function is executed else the second
		positioning.getCurrentPosition(
			function(position) {
				var coords = position.coords;
				$('#mapContainer').jOVI('map', function(map) {
					map.setZoomLevel(13);
					map.setCenter(coords);
				});
			},
			function(error) {
			}
		);
	}
}

var ttype = 0;
$(function() {
	$('#upload_link').on('click', function(e) {
		e.preventDefault();
		$('#mapContainer').jOVI('map', function(map) {
			window.location.href = 'upload-photo.php?c=' + map.center.latitude + ',' + map.center.longitude + '&z=' + map.zoomLevel + '&t=' + ttype;
		});
	});
	$('#nextstep').on('click', function(e) {
		e.preventDefault();
		$('#stepone').hide();
		$('#steptwo').show();
		$('#mapContainer').jOVI('map', function(map) {
			$('#staticmap').attr('src', 'http://m.nok.it/?app_id=_peU-uCkp-j8ovkzFGNU&token=gBoUkAMoxoqIWfxWA5DuMQ&c=' + map.center.latitude + ',' + map.center.longitude + '&z=' + map.zoomLevel + '&nord&w=851&h=315&nodot&t=0');
		});
	});
	$('#previousstep').on('click', function(e) {
		e.preventDefault();
		$('#stepone').show();
		$('#steptwo').hide();
	});
	$('.tile-select li').on('click', function() {
		var tiletype = $(this).data('tile');
		var imgurl = $('#staticmap').attr('src').replace(/&t=\d+/, '&t=' + tiletype);
		ttype = tiletype;
		$('#staticmap').attr('src', "data:image/gif;base64,R0lGODlhAQABAIAAAP///////yH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==");
		$('#staticmap').attr('src', imgurl);
	});
	var basicSearchBox = new nokia.places.widgets.SearchBox({
			targetNode: "basicSearchBox",
			searchCenter: function () {
				return {
					latitude: 52.516274,
					longitude: 13.377678
				}
			},
			onResults: function (data) {
				$('#mapContainer').jOVI('setCenter', {'latitude': data.results.items[0].position.latitude, 'longitude': data.results.items[0].position.longitude});
			}
		});

});