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

$fullscreenControl_checked = ($cfg->main->controls->fullscreenControl) ? 'checked' : '';
$geolocationControl_checked = ($cfg->main->controls->geolocationControl) ? 'checked' : '';
$routeEditor_checked = ($cfg->main->controls->routeEditor) ? 'checked' : '';
$rulerControl_checked = ($cfg->main->controls->rulerControl) ? 'checked' : '';
$searchControl_checked = ($cfg->main->controls->searchControl) ? 'checked' : '';
$trafficControl_checked = ($cfg->main->controls->trafficControl) ? 'checked' : '';
$typeSelector_checked = ($cfg->main->controls->typeSelector) ? 'checked' : '';
$zoomControl_checked = ($cfg->main->controls->zoomControl) ? 'checked' : '';
/**
 * [getLangsList description]
 *
 * @param  string $dir [description]
 *
 * @return [type]      [description]
 */
function getLangsList($dir = '../language') {
	$f = scandir($dir);
	foreach ($f as $file) {
		if (preg_match('/\.(lng)/', $file)) {
			$arr[] = str_replace('.lng', '', $file);
		}
	}

	return $arr;
}

?>

<div class="content">
	<div class="col col-mb-12 col-5 col-dt-4 form-label">
		&nbsp;
	</div>
	<div class="col col-mb-12 col-7 col-dt-8 form-control">
		<h2 class="m0"><?= $module_lang['mapSettings'] ?></h2>
	</div>
</div>
<form id="settingsForm" method="POST" action="/engine/ajax/<?= $cfg->moduleName ?>/saveconfig.php">
	<input type="hidden" name="mapsettings" value="y">
	<div class="content">
		<div class="col col-mb-12 col-5 col-dt-4 form-label">
			&nbsp;
		</div>
	</div>


	<div class="content">
		<div class="col col-mb-12 col-5 col-dt-4 form-label">
			<?= $module_lang['moduleLangName'] ?>
		</div>
		<div class="col col-mb-12 col-7 col-dt-8 form-control">
			<select name="moduleLang" id="moduleLang" class="styler">
				<? foreach (getLangsList(ENGINE_DIR . '/modules/' . $cfg->moduleName . '/language') as $lang): ?>
					<? $selectedLang = ($lang == $cfg->main->moduleLang) ? 'selected' : ''; ?>
					<option value="<?= $lang; ?>" <?= $selectedLang ?>><?= $lang; ?></option>
				<? endforeach; ?>
			</select>
		</div>
	</div>

	<? /*<div class="content">
		<div class="col col-mb-12 col-5 col-dt-4 form-label">
			<?= $module_lang['moduleTextLicenceKey'] ?>
		</div>
		<div class="col col-mb-12 col-7 col-dt-8 form-control">
			<input id="licenceKey" class="input" name="licenceKey" type="text" value="<?= $cfg->main->licenceKey ?>"> <a
					href="http://store.pafnuty.name/purchase/"
					target="_blank"><?= $module_lang['moduleTextGetlicenceKey'] ?></a>
		</div>
	</div>

	<div class="content">
		<div class="col col-mb-12 col-5 col-dt-4 form-label">
			<?=$module_lang['moduleTextApiKey']?>
		</div>
		<div class="col col-mb-12 col-7 col-dt-8 form-control">
			<input id="apiKey" class="input" name="apiKey" type="text" value="<?=$cfg->main->apiKey?>"> <a href="https://tech.yandex.ru/maps/commercial/" target="_blank"><?=$module_lang['moduleTextApiKeyWhat']?></a>
		</div>
	</div>*/ ?>

	<div class="content">
		<div class="col col-mb-12 col-5 col-dt-4 form-label">
			<?= $module_lang['moduleTextMapHeight'] ?>
		</div>
		<div class="col col-mb-12 col-7 col-dt-8 form-control">
			<input type="text" name="mapHeight" id="mapHeight" class="input" value="<?= $cfg->main->mapHeight ?>">
		</div>
	</div>

	<div class="content">
		<div class="col col-mb-12 col-5 col-dt-4 form-label">
			<?= $module_lang['moduleTextMapControls'] ?>
		</div>
		<div class="col col-mb-12 col-7 col-dt-8 form-control">
			<input class="checkbox" type="checkbox" value="1" name="controls[fullscreenControl]"
			       id="fullscreenControl" <?= $fullscreenControl_checked ?>> <label
					for="fullscreenControl"><span></span> <?= $module_lang['moduleTextMapFullScreen'] ?></label>
			<br>
			<input class="checkbox" type="checkbox" value="1" name="controls[geolocationControl]"
			       id="geolocationControl" <?= $geolocationControl_checked ?>> <label
					for="geolocationControl"><span></span> <?= $module_lang['moduleTextMapLocation'] ?></label>
			<br>
			<input class="checkbox" type="checkbox" value="1" name="controls[routeEditor]"
			       id="routeEditor" <?= $routeEditor_checked ?>> <label
					for="routeEditor"><span></span> <?= $module_lang['moduleTextMapRoute'] ?></label>
			<br>
			<input class="checkbox" type="checkbox" value="1" name="controls[rulerControl]"
			       id="rulerControl" <?= $rulerControl_checked ?>> <label
					for="rulerControl"><span></span> <?= $module_lang['moduleTextMapRuler'] ?></label>
			<br>
			<input class="checkbox" type="checkbox" value="1" name="controls[searchControl]"
			       id="searchControl" <?= $searchControl_checked ?>> <label
					for="searchControl"><span></span> <?= $module_lang['moduleTextMapSearchPanel'] ?></label>
			<br>
			<input class="checkbox" type="checkbox" value="1" name="controls[trafficControl]"
			       id="trafficControl" <?= $trafficControl_checked ?>> <label
					for="trafficControl"><span></span> <?= $module_lang['moduleTextMapProbki'] ?></label>
			<br>
			<input class="checkbox" type="checkbox" value="1" name="controls[typeSelector]"
			       id="typeSelector" <?= $typeSelector_checked ?>> <label
					for="typeSelector"><span></span> <?= $module_lang['moduleTextMapTypeTrigger'] ?></label>
			<br>
			<input class="checkbox" type="checkbox" value="1" name="controls[zoomControl]"
			       id="zoomControl" <?= $zoomControl_checked ?>> <label
					for="zoomControl"><span></span> <?= $module_lang['moduleTextMapZoom'] ?></label>
			<br>
		</div>
	</div>

	<div class="content">
		<div class="col col-mb-12 col-5 col-dt-4 form-label">
			<?= $module_lang['moduleTextMapZoomDefault'] ?>
		</div>
		<div class="col col-mb-12 col-7 col-dt-8 form-control">
			<input type="text" name="mapCenter[latitude]" id="mapCenterLat" class="input" style="width: 90px;"
			       value="<?= $cfg->main->mapCenter->latitude ?>" placeholder="<?= $module_lang['moduleTextMapLat'] ?>">
			<input type="text" name="mapCenter[longitude]" id="mapCenterLon" class="input" style="width: 90px;"
			       value="<?= $cfg->main->mapCenter->longitude ?>"
			       placeholder="<?= $module_lang['moduleTextMapLon'] ?>">
			<input type="text" name="mapCenter[zoom]" id="mapCenterZoom" class="input" style="width: 52px;"
			       value="<?= $cfg->main->mapCenter->zoom ?>"
			       placeholder="<?= $module_lang['moduleTextMapZoomPlaceholder'] ?>">
			<span class="btn btn-small mfp-open-modal-map"
			      data-mfp-src="#showMap"><?= $module_lang['moduleTextMapSelectOnMap'] ?></span>
			<div class="hide">
				<div id="showMap" class="content">
					<div class="col col-mb-12 col-10 col-dt-8 col-center modal-white">
						<span class="modal-close popup-modal-dismiss">&times;</span>
						<div class="modal-content">
							<div id="map" class="map-wrapper"
							     style="height: <?= ($cfg->main->mapHeight) ? $cfg->main->mapHeight : '400' ?>px;"></div>
						</div>
						<div class="btn modal-close map-save"
						     data-id="settingsForm"><?= $module_lang['moduleActionSave'] ?></div>
					</div>
				</div>
			</div>
		</div>
	</div>


	<div class="content">
		<div class="col col-mb-12 col-5 col-dt-4 form-label">
			<?= $module_lang['moduleTextMapZoomXf'] ?>
		</div>
		<div class="col col-mb-12 col-7 col-dt-8 form-control">
			<input type="text" name="coordsField" id="coordsField" class="input xf-input"
			       value="<?= $cfg->main->coordsField ?>"
			       placeholder="<?= $module_lang['moduleTextMapZoomXfPlaceholderText'] ?>"> <span
					class="btn btn-small mfp-open-ajax-xf disabled"
					data-mfp-src="/engine/ajax/<?= $cfg->moduleName ?>/xfields.php" data-id="coordsField"
					data-description="<?= $module_lang['moduleTextMapZoomXfPlaceholder'] ?>"
					data-field-type="text"><?= $module_lang['moduleActionCreateXf'] ?></span>
		</div>
	</div>

	<? /*
		<div class="content">
			<div class="col col-mb-12 col-5 col-dt-4 form-label">
				<?=$module_lang['moduleTextMapBaloonXf']?>
			</div>
			<div class="col col-mb-12 col-7 col-dt-8 form-control">
				<input type="text" name="baloonField" id="baloonField" class="input xf-input" value="<?=$cfg->main->baloonField?>" placeholder="<?=$module_lang['moduleTextMapBaloonXfPlaceholderText']?>"> <span class="btn btn-small mfp-open-ajax-xf disabled" data-mfp-src="/engine/ajax/<?=$cfg->moduleName?>/xfields.php" data-id="baloonField" data-description="<?=$module_lang['moduleTextMapBaloonXfPlaceholder']?>" data-field-type="textarea"><?=$module_lang['moduleActionCreateXf']?></span>
			</div>
		</div>
	*/ ?>


	<div class="content">
		<div class="col col-mb-12 col-5 col-dt-4 form-label">
			&nbsp;
		</div>
		<div class="col col-mb-12 col-7 col-dt-8 form-control">
			<p>
				<button class="btn ladda-button ladda-button-old" type="submit" data-style="expand-left"><span
							class="ladda-label"><?= $module_lang['moduleActionSave'] ?></span></button>
			</p>
		</div>
	</div>

</form> <!-- #settingsForm -->
