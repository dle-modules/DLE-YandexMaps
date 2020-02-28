<?php
/**
 * DLE-YandexMaps — Бесплатный модуль Яндекс Карт для DLE
 *
 * @author     ПафНутиЙ <pafnuty10@gmail.com>
 * @link       https://git.io/v9irg
 */

if (!defined('DATALIFEENGINE')) {
	header("HTTP/1.1 403 Forbidden");
	header('Location: ../../../');
	die("Hacking attempt!");
}

if (!$is_logged) {
	die('error');
}

define('ENGINE_DIR', ROOT_DIR . '/engine');

$cfg   = json_decode(file_get_contents(ENGINE_DIR . '/data/ymaps_config.json'));

define('MODULE_DIR', ENGINE_DIR . '/modules/' . $cfg->moduleName . '/');

if (@file_exists(MODULE_DIR . '/language/' . $cfg->main->moduleLang . '.lng')) {
	include(DLEPlugins::Check(MODULE_DIR . '/language/' . $cfg->main->moduleLang . '.lng'));
} else {
	die("Language file not found");
}

$key = ($cfg->main->apiKey) ? '&apikey=' . $cfg->main->apiKey : '';

$mapHeight         = ($cfg->main->mapHeight) ? $cfg->main->mapHeight : '400';
$controls          = (array)$cfg->main->controls;
$controls          = array_keys($controls);
$mapSelector = $cfg->moduleName . '-edit-map-container';

$modalHtml = <<<HTML
<div class="mfp-hide">
	<div id="{$mapSelector}-modal" class="modal-wrapper">
		<span class="modal-close popup-modal-dismiss">&times;</span>
		<div class="modal-content">
			<div id="{$mapSelector}" class="map-wrapper" style="height: {$mapHeight}px;"></div>
		</div>
		<div class="btn btn-gray popup-modal-dismiss btn-editmap-save bg-blue-700">{$module_lang['moduleActionApply']}</div>
	</div>
</div>
HTML;

$btnHtml = <<<HTML
<button class="btn btn-gray bg-blue-700 btn-editmap-modal"
		type="button"
		disabled
		>editmap</button>
<button class="btn btn-gray bg-blue-700 btn-clear-placemark"
		type="button"
		title="{$module_lang['moduleTextDelPlacemark']}"
>&times;</button>
HTML;



$ymapsJson = [
	'mapSelector' => $mapSelector,
	'mapUrl' => '//api-maps.yandex.ru/2.1/?lang=ru_RU' . $key,
	'scripts' => [
		'/engine/modules/' . $cfg->moduleName . '/js/jquery.magnificpopup.min.js'
	],
	'controls' => $controls,
	'height' => $cfg->main->mapHeight,
	'defaultPos' => [
		'lat' => $cfg->main->mapCenter->latitude,
		'lon' => $cfg->main->mapCenter->longitude,
		'zoom' => $cfg->main->mapCenter->zoom,
	],
	'arPlacemarkStyles' => $cfg->pointSettings->catPoints,
	'inputSelector' => '[name="xfield[' . $cfg->main->coordsField .']"]',
	'xfHolder' => '#xfield_holder_' . $cfg->main->coordsField,
	'modalHtml' => $modalHtml,
	'btnHtml' => $btnHtml,
	'text' => [
		'edit' => $module_lang['moduleTextEditPlacemark'],
		'add' => $module_lang['moduleTextAddPlacemark']
	]
];


header('Content-Type: application/json');
echo json_encode($ymapsJson);
