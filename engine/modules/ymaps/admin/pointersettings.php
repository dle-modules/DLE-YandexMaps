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

$icons = json_decode(file_get_contents(ENGINE_DIR . '/data/ymaps_icons.json'), true);
function showCatPoints($parentid = 0, $sublevelmarker = false) {
	global $lang, $cat_info, $config;
	$cat_item = '';

	if (count($cat_info)) {

		foreach ($cat_info as $cats) {
			if ($cats['parentid'] == $parentid) {
				$root_category[] = $cats['id'];
			}
		}

		if (count($root_category)) {
			foreach ($root_category as $id) {
				$cat_item .= '<li class="clearfix" point-item="' . $cat_info[$id]['id'] . '"><h4>' . stripslashes($cat_info[$id]['name']) . '</h4>';
				$cat_item .= getCatPointSettings($cat_info[$id]['id']);
				$cat_item .= '</li>';

				$cat_item .= showCatPoints($id, true);
			}

			if ($sublevelmarker) {
				return '<li class="pt0 pb0"><ul class="unstyled cat-points-list cat-points-list-child">' . $cat_item . '</ul></li>';
			} else {
				return $cat_item;
			}

		}
	}

}

function getCatPointSettings($id) {
	global $cfg, $config, $module_lang;
	$strPoint = [];
	$arPoint = (array)$cfg->pointSettings->catPoints->$id;
	$imgName = str_replace(['islands#', 'StretchyIcon'], ['', 'Str'], $arPoint['preset']);
	if (array_key_exists('iconColor', $arPoint)) {
		$strPoint[] = '<p class="img-point"><img style="background: ' . $arPoint['iconColor'] . '" src="/engine/modules/' . $cfg->moduleName . '/images/' . $imgName . '.png" alt=""></p>';
	} elseif (array_key_exists('iconLayout', $arPoint)) {
		$strPoint[] = '<p class="img-point"><img src="' . $arPoint['iconImageHref'] . '" alt=""></p>';
	} else {
		$strPoint[] = '<p class="img-point"><img src="engine/modules/' . $cfg->moduleName . '/images/' . $imgName . '.png" alt=""></p>';
	}

	if (count($arPoint) > 0) {
		$res = implode('', $strPoint) . '<p><span class="btn btn-small" data-point-change="' . $id . '">' . $module_lang['moduleActionChange'] . '</span> <span class="btn btn-small btn-red ladda-button" data-style="zoom-out" data-point-delete="' . $id . '"><span class="ladda-label">' . $module_lang['moduleActionDelete'] . '</span></span></p>';
	} else {
		$res = '<p><span class="btn btn-small" data-point-change="' . $id . '">' . $module_lang['moduleActionAdd'] . '</span></p>';
	}

	return '<div class="point-result">' . $res . '</div>';
}

function getHiddenInputs($id) {
	global $cfg;
	$inputs = [];
	$arPoint = (array)$cfg->pointSettings->catPoints->$id;

	foreach ($arPoint as $k => $info) {
		if (is_array($info)) {
			foreach ($info as $i => $v) {
				$inputs[] = '<input type="hidden" name="catPoints[' . $id . '][' . $k . '][' . $i . ']" value="' . $v . '" />';
			}
		} else {
			$inputs[] = '<input type="hidden" name="catPoints[' . $id . '][' . $k . ']" value="' . $info . '" />';
		}
	}

	return implode("\n", $inputs);
}

?>


<div class="content">
	<div class="col col-mb-12 col-5 col-dt-4 form-label">
		&nbsp;
	</div>
	<div class="col col-mb-12 col-7 col-dt-8 form-control">
		<h2 class="m0"><?= $module_lang['mapsPointSettings'] ?></h2>
	</div>
</div>
<div class="content">
	<div class="col col-mb-12 col-5 col-dt-4 form-label">
		&nbsp;
	</div>
</div>

<div class="content">
	<div class="col col-mb-12 col-5 col-dt-4 form-label">
		<?= $module_lang['pointersCatDefault'] ?>
	</div>
	<div class="col col-mb-12 col-7 col-dt-8 form-control">
		<ul class="unstyled cat-points-list bb0">
			<li class="clearfix" point-item="2">
				<?= getCatPointSettings('default'); ?>
			</li>
		</ul>
	</div>
	<div class="col col-mb-12">
		<hr>
	</div>
	<div class="col col-mb-12 col-5 col-dt-4 form-label">
		<?= $module_lang['pointersCat'] ?>
	</div>
	<div class="col col-mb-12 col-7 col-dt-8 form-control">
		<ul class="unstyled cat-points-list"><?= showCatPoints() ?></ul>
	</div>
</div>

