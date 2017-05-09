<?php
/**
 * DLE-YandexMaps — Бесплатный модуль Яндекс Карт для DLE
 *
 * @author     ПафНутиЙ <pafnuty10@gmail.com>
 * @link       https://git.io/v9irg
 */

// Всякие обязательные штуки для ajax DLE
@error_reporting(E_ALL ^ E_WARNING ^ E_NOTICE);
@ini_set('display_errors', true);
@ini_set('html_errors', false);
@ini_set('error_reporting', E_ALL ^ E_WARNING ^ E_NOTICE);

define('DATALIFEENGINE', true);
define('ROOT_DIR', substr(dirname(__FILE__), 0, -18));

define('ENGINE_DIR', ROOT_DIR . '/engine');

$cfg = json_decode(file_get_contents(ENGINE_DIR . '/data/ymaps_config.json'), true);
$icons = json_decode(file_get_contents(ENGINE_DIR . '/data/ymaps_icons.json'), true);

define('MODULE_DIR', ENGINE_DIR . '/modules/' . $cfg['moduleName'] . '/');

if (@file_exists(MODULE_DIR . '/language/' . $cfg['main']['moduleLang'] . '.lng')) {
	include(MODULE_DIR . '/language/' . $cfg['main']['moduleLang'] . '.lng');
} else {
	die("Language file not found");
}


include ENGINE_DIR . '/data/config.php';

require_once ENGINE_DIR . '/classes/mysql.php';
require_once ENGINE_DIR . '/data/dbconfig.php';
require_once ENGINE_DIR . '/modules/functions.php';
if ($config['version_id'] > 9.6) {
	dle_session();
} else {
	@session_start();
}


$user_group = get_vars("usergroup");
if (!$user_group) {
	$user_group = [];
	$db->query("SELECT * FROM " . USERPREFIX . "_usergroups ORDER BY id ASC");
	while ($row = $db->get_row()) {
		$user_group[$row['id']] = [];
		foreach ($row as $key => $value) $user_group[$row['id']][$key] = stripslashes($value);
	}
	set_vars("usergroup", $user_group);
	$db->free();
}

$cat_info = get_vars("category");

if (!is_array($cat_info)) {
	$cat_info = [];

	$db->query("SELECT * FROM " . PREFIX . "_category ORDER BY posi ASC");
	while ($row = $db->get_row()) {

		$cat_info[$row['id']] = [];

		foreach ($row as $key => $value) {
			$cat_info[$row['id']][$key] = stripslashes($value);
		}

	}
	set_vars("category", $cat_info);
	$db->free();
}


$key = ($cfg->apiKey) ? '&key=' . $cfg->apiKey : '';

$ymaps = '<script src="//api-maps.yandex.ru/2.1/?lang=ru_RU&onload=editMapInit' . $key . '"></script>';
$ymaps .= '<script src="/engine/modules/' . $cfg['moduleName'] . '/js/jquery.magnificpopup.min.js"></script>';
// $ymaps .= '<script src="/templates/' . $config['skin'] . '/' . $cfg['moduleName'] . '/' . $cfg['moduleName'] . '.js"></script>';

$mapHeight = ($cfg['main']['mapHeight']) ? $cfg['main']['mapHeight'] : '400';
$controls = $cfg['main']['controls'];
$controls = array_keys($controls);
$controls = json_encode($controls);
$arPlacemarkStyles = ($cfg['pointSettings']['catPoints']) ? json_encode($cfg['pointSettings']['catPoints']) : '{}';


$ymaps .= <<<HTML
<script>
	var pointInput = $('[name="xfield[{$cfg['main']['coordsField']}]"]'),
		coor = getCorrsFromInput(pointInput),
		coordsFieldText;
		

	var lon = coor.lon,
		lat = coor.lat,
		zoom = coor.zoom,
		controls = $.parseJSON('{$controls}'),
		height = {$cfg['main']['mapHeight']};


	function hideCoordsField() {
		if (pointInput.length) {
			var editPlacemarkText = (pointInput.val()) ? '{$module_lang['moduleTextEditPlacemark']}' : '{$module_lang['moduleTextAddPlacemark']}' ;
			if ($('[data-mfp-src="#addMap"]').length < 1) {
				pointInput.hide().after('<a href="#" class="btn btn-gray btn-editmap-modal" data-mfp-src="#addMap">'+editPlacemarkText+'</a> <a href="#" class="btn btn-gray" onclick="clearPlacemark(); hideCoordsField(); return false;" title="{$module_lang['moduleTextDelPlacemark']}">&times;</a>');
			} else {
				$('.btn-editmap-modal').text(editPlacemarkText);
			}
		};


	}

	function getCorrsFromInput() {
		if (pointInput.val()) {
			return $.parseJSON(pointInput.val());
		} else {
			return {"lat":"{$cfg['main']['mapCenter']['latitude']}","lon":"{$cfg['main']['mapCenter']['longitude']}","zoom":"{$cfg['main']['mapCenter']['zoom']}"};
		}
	}

	function setCenter(coords, zoom) {
		var inpText = '{"lat":"'+coords[0].toPrecision(6)+'", "lon" : "'+coords[1].toPrecision(6)+'", "zoom": "'+zoom+'"}';
		coordsFieldText = inpText;
	}
	function clearPlacemark() {
		pointInput.val('');	
	}

	function editMapInit() {
		var editnewsMap,
			myPlacemark;

		$(document)
			.on('editMapInit', function() {
				
				var map = $('#map'),
					coor = getCorrsFromInput(),
					lon = coor.lon,
					lat = coor.lat,
					zoom = coor.zoom;
				
				map.height(height);

				if (editnewsMap) {
					myPlacemark = false;
					editnewsMap.events.remove(['click','boundschange','dragend']);
					editnewsMap.destroy();
				};
				

				editnewsMap = new ymaps.Map('map', {
						center: [lat, lon],
						zoom: zoom,
						controls: controls
					});
				
				if (pointInput.val()) {
					var _coords = $.parseJSON(pointInput.val());
					myPlacemark = createPlacemark([_coords.lat*1, _coords.lon*1]);

					editnewsMap.geoObjects.add(myPlacemark);
				};

				// Слушаем клик на карте
				editnewsMap.events
					.add('click', function (e) {
						var coords = e.get('coords');
						// Если метка уже создана – просто передвигаем ее
						if (myPlacemark) {
							myPlacemark.geometry.setCoordinates(coords);
						}
						// Если нет – создаем.
						else {
							myPlacemark = createPlacemark(coords);
							editnewsMap.geoObjects.add(myPlacemark);

							// Слушаем событие окончания перетаскивания на метке.
							myPlacemark.events.add('dragend', function () {
								setCenter(myPlacemark.geometry.getCoordinates(), editnewsMap.getZoom());
							});
						}
						setCenter(coords, editnewsMap.getZoom());
					})
					.add('boundschange', function (e) {
						setCenter([lat*1, lon*1], editnewsMap.getZoom());
					});

				// Создание метки
				function createPlacemark(coords) {
					var arPlacemarkStyles = '{$arPlacemarkStyles}',
						categoryId = ($('#category').length) ? $('#category option:selected').val() : 0,
						key = (categoryId > 0) ? categoryId : 'default',
						placemarkStyle = $.parseJSON(arPlacemarkStyles);

					return new ymaps.Placemark(coords, {},
						placemarkStyle[key]
					);
				}
			});
	};
</script>

<div class="mfp-hide">
	<div id="addMap" class="modal-wrapper">
		<span class="modal-close popup-modal-dismiss">&times;</span>
		<div class="modal-content">
			<div id="map" class="map-wrapper" style="height: {$mapHeight}px;"></div>
		</div>
		<div class="btn btn-gray popup-modal-dismiss btn-editmap-save">{$module_lang['moduleActionApply']}</div>
	</div>
</div>
HTML;


echo $ymaps;