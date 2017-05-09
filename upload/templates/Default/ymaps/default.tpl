{scripts}
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
		var detailMap,
			detailPlacemark;

		$(document).on('mapInit', function() {
			var $map = $('#map-{id}');
			if (!detailMap) {
				detailMap = new ymaps.Map('map-{id}', {
					center: [lat, lon],
					zoom: zoom,
					controls: controls
				}),
				detailPlacemark = new ymaps.Placemark([lat, lon], {
					balloonContentBody: balloonContentBodyText,
					// iconContent: iconContentText, // Раскомментировать  при необходимости
					hintContent: hintContentText
				},
				placemarkStyle
				);

				detailMap.geoObjects.add(detailPlacemark);
			};
		});
	};

</script>
<span class="btn" data-mfp-src="#showMap-{id}">Показать карту</span>

<div class="mfp-hide">
	<div id="showMap-{id}" class="modal-wrapper">
		<span class="modal-close">&times;</span>
		<div class="modal-content">
			<div id="map-{id}" class="map-wrapper" style="height: {mapHeight}px;"></div>
		</div>
		<div class="btn btn-close">Закрыть</div>
	</div>
</div>