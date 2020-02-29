{scripts}
<script>
  var lon                    = {lon};
  var lat                    = {lat};
  var zoom                   = {zoom};
  var controls               = $.parseJSON('{controls}');
  var placemarkStyle         = $.parseJSON('{placemarkStyle}');
  var balloonContentBodyText = '{title} <br> <p>{short_story limit="100"}</p>';
  var iconContentText        = '{title limit="15"}';
  var hintContentText        = '{title}';

  // Инициализация карты.
  ymaps.ready(showMap);

  function showMap() {
    var detailMap;
    var detailPlacemark;

    $(document).on('mapInit', function () {
      if (!detailMap) {
        detailMap = new ymaps.Map(
          'map-{id}',
          {
            center: [lat, lon],
            zoom: zoom,
            controls: controls
          });

        detailPlacemark = new ymaps.Placemark([lat, lon], {
            balloonContentBody: balloonContentBodyText,
            // iconContent: iconContentText, // Раскомментировать  при необходимости
            hintContent: hintContentText
          },
          placemarkStyle
        );

        detailMap.geoObjects.add(detailPlacemark);
      }
    });
  }

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