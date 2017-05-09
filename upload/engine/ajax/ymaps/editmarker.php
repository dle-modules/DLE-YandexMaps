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

function showCustomIconList($id = 0) {
	global $config, $cfg;

	$arImages = [];
	$tplPiconPath = 'templates/' . $config['skin'] . '/' . $cfg['moduleName'] . '/icons/';

	$dir = ROOT_DIR . '/' . $tplPiconPath;
	$images = scandir($dir);
	$arPoint = $cfg['pointSettings']['catPoints'][$id];

	if ($images !== false) {
		$images = preg_grep('/\\.(?:png|gif|jpe?g)$/', $images);
		foreach ($images as $image) {
			$imgSize = getimagesize($dir . '/' . $image);
			$selected = ($arPoint['iconImageHref'] == $config['http_home_url'] . $tplPiconPath . $image) ? 'selected' : false;
			$offsetWidth = ($selected) ? $arPoint['iconImageOffset'][0] : false;
			$offsetHeight = ($selected) ? $arPoint['iconImageOffset'][1] : false;
			$arImages[] = ['name' => $image, 'src' => $config['http_home_url'] . $tplPiconPath . $image, 'width' => $imgSize[0], 'height' => $imgSize[1], 'offsetWidth' => $offsetWidth, 'offsetHeight' => $offsetHeight, 'selected' => $selected,];
		}
	}

	//
	return $arImages;
}

function getIconsArray($array = [], $id = 0, $type = '') {
	global $cfg, $module_lang;
	$arPoint = $cfg['pointSettings']['catPoints'][$id];
	$option = [];

	foreach ($array as $name => $value) {
		$pointColor = ($arPoint['iconColor']) ? $arPoint['iconColor'] : '#4a9fc5';
		$customColor = ($name == $type) ? 'data-icon-color = "' . $pointColor . '"' : '';
		$name = str_replace(['islands#', 'StretchyIcon'], ['', 'Str'], $name);

		$customName = ($name == $type) ? $name . $module_lang['markerCustomColor'] : $name;

		$selected = (in_array($value, $arPoint)) ? 'selected' : '';
		$option[] = '<option value="' . $value . '" data-select-img="engine/modules/' . $cfg['moduleName'] . '/images/' . $name . '.png" ' . $customColor . ' ' . $selected . '>' . $customName . '</option>';

	}

	return implode("\n", $option);
}

require_once ENGINE_DIR . '/modules/sitelogin.php';
require_once MODULE_DIR . 'admin/classes/xfields.php';


/**
 * Основной код файла
 */

if ($member_id['user_group'] == '1') {

	if ($_REQUEST['id'] == 'default') {
		$arCat['id'] = 'default';
		$arCat['name'] = $module_lang['pointersCatDefault'];
		$headerTextPrefix = '';
	} else {
		$catId = (int)$_REQUEST['id'];
		$arCat = $db->super_query("SELECT id, name FROM " . PREFIX . "_category WHERE id=" . $catId);
		$headerTextPrefix = $module_lang['moduleTextMarkerSettingsHeader'];
	}

	if ($arCat['id'] > 0 || $arCat['id'] == 'default') {
		$optionsCustomIcons = [];

		foreach (showCustomIconList($arCat['id']) as $option) {
			$optionsCustomIcons[] = '<option value="' . $option['src'] . '" ' . $option['selected'] . ' data-size-width="' . $option['width'] . '" data-size-height="' . $option['height'] . '" data-offset-width="' . $option['offsetWidth'] . '" data-offset-height="' . $option['offsetHeight'] . '" data-select-img="' . $option['src'] . '">' . $option['name'] . '</option>';
		}
		$optionsCustomIcons = implode("\n", $optionsCustomIcons);

		$customSelect = ($cfg['pointSettings']['catPoints'][$arCat['id']]['iconLayout']) ? 'selected' : '';

		$showResult = '<div class="col-mb-12 col-8 col-dt-6 col-ld-5 center-block">
			<div class="content">
				<div class="modal-white">
				<span class="modal-close popup-modal-dismiss">&times;</span>
				<div class="modal-header">
					<p>' . $headerTextPrefix . $arCat['name'] . '</p>
				</div>
					<div class="modal-content p10">
						<form method="POST" action="engine/ajax/' . $cfg['moduleName'] . '/saveconfig.php" data-ajax-submit="reload">
							<input type="hidden" name="pointID" value="' . $arCat['id'] . '">
							<div class="content">
								<div class="col col-mb-12 col-9 marker-select-wrapper">
									<select class="styler marker-select input-block mb10" name="preset" data-search="true">
										<option value="">---</option>
										<optgroup label="' . $module_lang['moduleTextMarkerWText'] . '">
											' . getIconsArray($icons['icon'], $arCat['id'], 'icon') . '
										</optgroup>
										<optgroup label="' . $module_lang['moduleTextMarkerStrech'] . '">
											' . getIconsArray($icons['stretchyIcon'], $arCat['id']) . '
										</optgroup>
										<optgroup label="' . $module_lang['moduleTextMarkerWDot'] . '">
											' . getIconsArray($icons['dotIcon'], $arCat['id'], 'dotIcon') . '
										</optgroup>
										<optgroup label="' . $module_lang['moduleTextMarkerRound'] . '">
											' . getIconsArray($icons['circleIcon'], $arCat['id'], 'circleIcon') . '
										</optgroup>
										<optgroup label="' . $module_lang['moduleTextMarkerRoundWDot'] . '">
											' . getIconsArray($icons['circleDotIcon'], $arCat['id'], 'circleDotIcon') . '
										</optgroup>
										<option value="custom" ' . $customSelect . '>' . $module_lang['moduleTextMarkerCustom'] . '</option>
									</select>
									<div class="marker-custom-icon-select-wrapper hide">
										<input type="hidden" name="iconLayout" value="" class="input iconLayout">
										<select class="styler marker-select marker-custom-icon-select input-block mb10" name="iconImageHref">
											' . $optionsCustomIcons . '
										</select>
										<div class="mb10">
											<span class="form-label form-label-small">' . $module_lang['moduleTextIconWidth'] . '</span> <input type="number" name="iconImageSize[0]" value="" class="input iconImageSize0"> px.
										</div>
										<div class="mb10">
											<span class="form-label form-label-small">' . $module_lang['moduleTextIconHeight'] . '</span> <input type="number" name="iconImageSize[1]" value="" class="input iconImageSize1"> px.
										</div>
										<div class="mb10">
											<span class="form-label form-label-small">' . $module_lang['moduleTextIconOffWidth'] . '</span> <input type="number" name="iconImageOffset[0]" value="" class="input iconImageOffset0"> px.
										</div>
										<div class="mb10">
											<span class="form-label form-label-small">' . $module_lang['moduleTextIconOffHeight'] . '</span> <input type="number" name="iconImageOffset[1]" value="" class="input iconImageOffset1"> px.
										</div>	
										<p class="ta-center"><a href="http://codepen.io/pafnuty/pen/dhwam?editors=101" target="_blank">' . $module_lang['moduleTextExampleLink'] . '</a></p>						
									</div>
								</div>
								<div class="col col-mb-12 col-3">
									<div class="selected-marker-wrapper">
										<img class="selected-marker" src="data:image/gif;base64,R0lGODlhHgAeALMAAO/v7+np6fv7++Tk5Pb29t/f3/n5+efn5/Ly8u3t7eLi4v39/evr6/T09N3d3f///yH5BAAAAAAALAAAAAAeAB4AAAR38MlJq7046827/xwQOKQDECBFKGXrBOlDuDT8LUV5CBLQIh8fSVEROg6fEQlIEZSIHgapsKgYSoWPYOBoWJSvmEVaMogphxbgPEmXmOxEy8t+OEuo+sO41u/Lfg8IU4E9J4WIE1sOAzx+YAmBkIEGXI2JmJmaHhEAOw==" alt="">
							
									</div>
								</div>
							</div> <!-- .content -->
							<div class="ta-center mb10">
							<button class="btn ladda-button" type="submit" data-style="expand-left"><span class="ladda-label">' . $module_lang['moduleActionSave'] . '</span></button>
								<span class="btn btn-small btn-red modal-close">' . $module_lang['moduleActionClose'] . '</span>
							</div>
						</form>
					</div>
				</div>
			</div>
		</div>';
	} else {
		$showResult = '<div class="content">
			<div class="col col-mb-12 col-8 col-dt-4 col-id-3 col-center modal-white">
			<span class="modal-close popup-modal-dismiss">×</span>
				<div class="modal-content"><p class="ta-center">' . $module_lang['catIdWrong'] . '</p></div>
			</div>
		</div>';
	}

	die ($showResult);
} else {
	die ('Access denied');
}

