{script_yandex}
<script>
	var lon = {lon},
		lat = {lat},
		zoom = {zoom},
		controls = $.parseJSON('{controls}'),
		placemarkStyle = $.parseJSON('{placemarkStyle}'),
		balloonContentBodyText = '{title} <br> <p>{short_story limit="100"}</p>',
		iconContentText = '{title limit="15"}',
		hintContentText = '{title}'

	// Инициализация карты.
	ymaps.ready(showMap);

	function showMap() {

		simpeDetailMap = new ymaps.Map('simpleMap-{id}', {
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
	};

</script>

<div id="simpleMap-{id}" style="height: {mapHeight}px;"></div>
