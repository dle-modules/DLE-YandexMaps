<?php
/**
 * DLE-YandexMaps — Бесплатный модуль Яндекс Карт для DLE
 *
 * @author     ПафНутиЙ <pafnuty10@gmail.com>
 * @link       https://git.io/v9irg
 */

if (!defined('DATALIFEENGINE')) {
	die("Go fuck yourself!");
}

$cfg = json_decode(file_get_contents(ENGINE_DIR . '/data/ymaps_config.json'));

define('MODULE_DIR', ENGINE_DIR . '/modules/' . $cfg->moduleName . '/');

define('MODULE_DIR', ENGINE_DIR . '/modules/' . $cfg->moduleName . '/');

if (@file_exists(MODULE_DIR . '/language/' . $cfg->main->moduleLang . '.lng')) {
	include(MODULE_DIR . '/language/' . $cfg->main->moduleLang . '.lng');
} else {
	die("Language file not found");
}

$key = ($cfg->apiKey) ? '&key=' . $cfg->apiKey : '';

$ymaps = '<script src="//api-maps.yandex.ru/2.1/?lang=ru_RU' . $key . '"></script>';
$ymaps .= '<script src="/templates/' . $config['skin'] . '/' . $cfg->moduleName . '/' . $cfg->moduleName . '.js"></script>';

$mapHeight = ($cfg->main->mapHeight) ? $cfg->main->mapHeight : '400';
$controls = (array)$cfg->main->controls;
$controls = array_keys($controls);
$controls = json_encode($controls);

$ymaps .= <<<HTML
<script>
	var arPlacemarkStyles = '{$arPlacemarkStyles}',
		inputSelector = '[name="xfield[{$cfg->main->coordsField}]"]',
		controls = $.parseJSON('{$controls}'),
		height = {$cfg->main->mapHeight},
		coordsFieldText;

	function hideCoordsField(but) {
		if ($('#xfield_holder_{$cfg->main->coordsField}').css('display') != 'none') {
			$('#xfield_holder_{$cfg->main->coordsField}').hide();
			$(but).show();
		} else {
			$(but).hide();
		}
	}

	function getCorrsFromInput() {
		var pointInput = $(inputSelector);
		if (pointInput.val()) {
			return $.parseJSON(pointInput.val());
		} else {
			return {"lat":"{$cfg->main->mapCenter->latitude}","lon":"{$cfg->main->mapCenter->longitude}","zoom":"{$cfg->main->mapCenter->zoom}"};
		}
	}

	function setCenter(coords, zoom) {
		var inpText = '{"lat":"'+coords[0].toPrecision(6)+'", "lon" : "'+coords[1].toPrecision(6)+'", "zoom": "'+zoom+'"}';
		$('[name="xfield[{$cfg->main->coordsField}]"]').val(inpText);
	}
</script>

<div id="map-inline" class="map-wrapper" style="height: {$mapHeight}px;"></div>
HTML;


echo $ymaps;