/**!
 * DLE-YandexMaps — Бесплатный модуль Яндекс Карт для DLE
 *
 * @author     ПафНутиЙ <pafnuty10@gmail.com>
 * @link       https://git.io/v9irg
 */

var doc = $(document);

doc
	.on('change', '#category', function() {
		hideCoordsField('.btn-addmap');
	})
	.on('click', '.add-map-save', function() {
		applyCoords();
	})
	.on('click', '.add-map-clear', function() {
		resetCoords();
	});

	// Инициализация карты.

	ymaps.ready(addMapInit);

	function addMapInit() {
		var addnewsMap,
			myPlacemark;
		doc
			.on('mapInit', function() {
				var $map = $('#map'),
					coors = getCorrsFromInput(),
					lat = coors.lat,
					lon = coors.lon,
					zoom = coors.zoom;
				
				$map.height(height);

				if (addnewsMap) {
					myPlacemark = false;
					addnewsMap.events.remove(['click','boundschange','dragend']);
					addnewsMap.destroy();
				};

				addnewsMap = new ymaps.Map('map', {
					center: [lat, lon], // Саратов
					zoom: zoom,
					controls: controls
				});
				console.log('getInput', getInput());
				
				if (getInput()) {
					var _coords = $.parseJSON(getInput());
					myPlacemark = createPlacemark([_coords.lat*1, _coords.lon*1]);
					addnewsMap.geoObjects.add(myPlacemark);
				};

				// Слушаем клик на карте
				addnewsMap.events
					.add('click', function (e) {
						var coords = e.get('coords');
						// Если метка уже создана – просто передвигаем ее
						if (myPlacemark) {
							myPlacemark.geometry.setCoordinates(coords);
							console.log('двигаем');
						}
						// Если нет – создаем.
						else {
							console.log('создаём');
							myPlacemark = createPlacemark(coords);
							addnewsMap.geoObjects.add(myPlacemark);

							// Слушаем событие окончания перетаскивания на метке.
							myPlacemark.events.add('dragend', function () {
								setCenter(myPlacemark.geometry.getCoordinates(), addnewsMap.getZoom());
							});
						}
						setCenter(coords, addnewsMap.getZoom());
					})
					.add('boundschange', function (e) {
						setCenter([lat*1, lon*1], addnewsMap.getZoom());
					});

				// Создание метки
				function createPlacemark(coords) {
					// переменная arPlacemarkStyles определена в модуле
					var categoryId = ($('#category').length) ? $('#category option:selected').val() : 0,
						key = (categoryId > 0) ? categoryId : 'default',
						placemarkStyle = $.parseJSON(arPlacemarkStyles);
					
					console.log('categoryId: ',categoryId);
					return new ymaps.Placemark(coords, {},
						placemarkStyle[key]
					);
				}
			});
	};

jQuery(document).ready(function($) {
	// Прячем поле с координатами.
	// Сама функция живёт в /engine/modules/ymaps/addnews.php
	// и генерируется автоматически в зависимости от поля.
	hideCoordsField('.btn-addmap');
	if ($.magnificPopup) {
		// Дефолтные настройки magnificpopup
		$.extend(true, $.magnificPopup.defaults, {
			tClose: 'Закрыть (Esc)',
			tLoading: 'Загрузка...'
		});


		$('[data-mfp-src]').magnificPopup({
			type: 'inline',
			preloader: false,
			// focus: '#username',
			modal: true,
			callbacks: {
				open : function () {
					doc.trigger('mapInit');
				}
			}
		});
	};

	if ($('#map-inline').length) {
		ymaps.ready(ymapInline);
		function ymapInline() {
			var addnewsMap,
				myPlacemark,
				$map = $('#map'),
				coors = getCorrsFromInput(),
				lat = coors.lat,
				lon = coors.lon,
				zoom = coors.zoom;
				
			$map.height(height);

			if (!addnewsMap) {

				addnewsMap = new ymaps.Map('map-inline', {
					center: [lat, lon], // Саратов
					zoom: zoom,
					controls: controls
				});
			};
			// Слушаем клик на карте
			addnewsMap.events.add('click', function (e) {
					var coords = e.get('coords');

					// Если метка уже создана – просто передвигаем ее
					if (myPlacemark) {
						myPlacemark.geometry.setCoordinates(coords);
					}
					// Если нет – создаем.
					else {
						myPlacemark = createPlacemark(coords);

						addnewsMap.geoObjects.add(myPlacemark);
						// Слушаем событие окончания перетаскивания на метке.
						myPlacemark.events.add('dragend', function () {
							setCenter(myPlacemark.geometry.getCoordinates(), addnewsMap.getZoom());
						});
					}
					setCenter(coords, addnewsMap.getZoom());
				})
				.add('boundschange', function (e) {
					setCenter([lat * 1, lon * 1], addnewsMap.getZoom());
				});

			// Создание метки
			function createPlacemark(coords) {
				return new ymaps.Placemark(coords, {
					draggable: true
				});
			}

			
		}
	}

}); //ready