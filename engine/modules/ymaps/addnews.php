<?php
/**
 * DLE-YandexMaps — Бесплатный модуль Яндекс Карт для DLE
 *
 * @author     ПафНутиЙ <pafnuty10@gmail.com>
 * @link       https://git.io/v9irg
 */

if (!defined('DATALIFEENGINE')) {
	header("HTTP/1.1 403 Forbidden");
	die("Hacking attempt!");
}
global $onload_scripts, $js_array;

$cfg = json_decode(file_get_contents(ENGINE_DIR . '/data/ymaps_config.json'));

define('MODULE_DIR', ENGINE_DIR . '/modules/' . $cfg->moduleName . '/');

define('MODULE_DIR', ENGINE_DIR . '/modules/' . $cfg->moduleName . '/');

if (@file_exists(MODULE_DIR . '/language/' . $cfg->main->moduleLang . '.lng')) {
	include(DLEPlugins::Check(MODULE_DIR . '/language/' . $cfg->main->moduleLang . '.lng'));
} else {
	die("Language file not found");
}

$key = ($cfg->main->apiKey) ? '&apikey=' . $cfg->main->apiKey : '';

$js_array[] = '/engine/modules/' . $cfg->moduleName . '/js/jquery.magnificpopup.min.js';
$js_array[] = '/templates/' . $config['skin'] . '/' . $cfg->moduleName . '/' . $cfg->moduleName . '.js';

$mapHeight = ($cfg->main->mapHeight) ? $cfg->main->mapHeight : '400';
$controls = (array)$cfg->main->controls;
$controls = array_keys($controls);
$controls = json_encode($controls);
$arPlacemarkStyles = ($cfg->pointSettings->catPoints) ? json_encode($cfg->pointSettings->catPoints) : '{}';
$mapSelector = $cfg->moduleName . '-map-container';

$onload_scripts[] = <<<HTML
	var mapConfig = {
		isInline: false,
		mapSelector: '{$mapSelector}',
		mapUrl: '//api-maps.yandex.ru/2.1/?lang=ru_RU{$key}',
		controls: {$controls},
		height: {$cfg->main->mapHeight},
		defaultPos: {
			lat: '{$cfg->main->mapCenter->latitude}',
			lon: '{$cfg->main->mapCenter->longitude}',
			zoom: '{$cfg->main->mapCenter->zoom}',
		},
		arPlacemarkStyles: {$arPlacemarkStyles},
		inputSelector: '[name="xfield[{$cfg->main->coordsField}]"]',
		xfHolder: '#xfield_holder_{$cfg->main->coordsField}'
	}
	if (addNewsMapStart) {
		addNewsMapStart(mapConfig);
	};
HTML;

$ymaps = <<<HTML
<div class="mfp-hide">
	<div id="addMap" class="modal-wrapper">
		<span class="modal-close popup-modal-dismiss" title="{$module_lang['moduleActionClose']}">&times;</span>
		<div class="modal-content">
			<div id="{$mapSelector}" class="map-wrapper" style="height: {$mapHeight}px;"></div>
		</div>
		<div class="btn popup-modal-dismiss add-map-save">{$module_lang['moduleActionApply']}</div>
		<div class="btn popup-modal-dismiss add-map-clear">{$module_lang['moduleActionReset']}</div>
	</div>
</div>
HTML;


echo $ymaps;