{script_yandex}
<script>
	var lon = {lon};
	var	lat = {lat};
	var	zoom = {zoom};
	var	controls = JSON.parse('{controls}');
	var	placemarkStyle = JSON.parse('{placemarkStyle}');
	var	balloonContentBodyText = '{title} <br> <p>{short_story limit="100"}</p>';
	var	iconContentText = '{title limit="15"}';
	var	hintContentText = '{title}';

	// Инициализация карты.
	ymaps.ready(showMap);

	function showMap() {

		var simpeDetailMap = new ymaps.Map('simpleMap-{id}', {
			center: [lat, lon],
			zoom: zoom,
			controls: controls
		}),
		placemark = new ymaps.Placemark([lat, lon], {
			balloonContentBody: balloonContentBodyText,
			iconContent: iconContentText, // Раскомментировать  при необходимости
			hintContent: hintContentText
		},
		placemarkStyle
		);

		simpeDetailMap.geoObjects.add(placemark);
	}

</script>

<div id="simpleMap-{id}" style="height: {mapHeight}px;"></div>
