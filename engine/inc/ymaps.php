<?php
/**
 * DLE-YandexMaps — Бесплатный модуль Яндекс Карт для DLE
 *
 * @author     ПафНутиЙ <pafnuty10@gmail.com>
 * @link       https://git.io/v9irg
 */

if (!defined('DATALIFEENGINE') OR !defined('LOGGED_IN')) {
	die("Hacking attempt!");
}
if ($member_id['user_group'] != '1') {
	msg("error", $lang['index_denied'], $lang['index_denied']);
}

/**
 * Конфиг модуля
 */

$cfg = json_decode(file_get_contents(ENGINE_DIR . '/data/ymaps_config.json'));

define('MODULE_DIR', ENGINE_DIR . '/modules/' . $cfg->moduleName . '/');

if (@file_exists(MODULE_DIR . '/language/' . $cfg->main->moduleLang . '.lng')) {
	include(MODULE_DIR . '/language/' . $cfg->main->moduleLang . '.lng');
} else {
	die("Language file not found");
}

include(MODULE_DIR . 'admin/classes/xfields.php');

?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="<?= $config['charset'] ?>">
	<title><?= $cfg->moduleTitle ?> - <?= $module_lang['moduleTextManagment'] ?></title>
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">

	<link rel="stylesheet" href="<?= $config['http_home_url'] ?>engine/modules/<?= $cfg->moduleName ?>/css/style.css">
	<script src="//api-maps.yandex.ru/2.1/?lang=ru_RU"></script>
	<script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
	<script src="//cdnjs.cloudflare.com/ajax/libs/autosize.js/1.18.1/jquery.autosize.min.js"></script>
	<script src="<?= $config['http_home_url'] ?>engine/modules/<?= $cfg->moduleName ?>/js/jquery.form.min.js"></script>
	<script src="<?= $config['http_home_url'] ?>engine/modules/<?= $cfg->moduleName ?>/js/jquery.ladda.min.js"></script>
	<script src="<?= $config['http_home_url'] ?>engine/modules/<?= $cfg->moduleName ?>/js/jquery.magnificpopup.min.js"></script>
	<script src="<?= $config['http_home_url'] ?>engine/modules/<?= $cfg->moduleName ?>/js/jquery.formstyler.min.js"></script>
	<script src="<?= $config['http_home_url'] ?>engine/modules/<?= $cfg->moduleName ?>/js/jquery.easyResponsiveTabs.min.js"></script>
	<script src="<?= $config['http_home_url'] ?>engine/modules/<?= $cfg->moduleName ?>/js/main.js"></script>
</head>
<body>
	<div class="container container-blue">
		<header class="content">
			<div class="col col-mb-12 col-6">
				<a href="<?= $PHP_SELF ?>?mod=main" class="btn btn-small btn-white"><?= $lang['skin_main'] ?></a>
				<a class="btn btn-small btn-white" href="<?= $PHP_SELF ?>?mod=options&amp;action=options"
				   title="<?= $module_lang['moduleTextAllSections'] ?>"><?= $module_lang['moduleTextAllSections'] ?></a>
				<a href="<?= $config['http_home_url'] ?>" target="_blank"
				   class="btn btn-small btn-white"><?= $lang['skin_view'] ?></a>
			</div>
			<div class="col col-mb-12 col-6 ta-right">
				<?= $member_id['name'] . ' <small class="hide-phone">(' . $user_group[$member_id['user_group']]['group_name'] . ')</small> ' ?>
				<a href="<?= $PHP_SELF ?>?action=logout" class="btn btn-small btn-red"><?= $lang['skin_logout'] ?></a>
			</div>
		</header>
	</div>
	<div class="container">
		<div class="content">
			<div class="col col-mb-12 col-12">
				<h1 class="ta-center">
					<?= $cfg->moduleTitle ?>
				</h1>
				<hr>
			</div> <!-- .col col-mb-12 col-12 -->
		</div> <!-- .content -->
		<div id="settings">
			<div class="content">
				<div class="col col-mb-12">
					<ul class="resp-tabs-list">
						<li><?= $module_lang['mapSettings'] ?></li>
						<li><?= $module_lang['mapsPointSettings'] ?></li>
						<li><?= $module_lang['mapsSupport'] ?></li>
					</ul>

					<div class="resp-tabs-container">
						<div>
							<? include(MODULE_DIR . 'admin/mapsettings.php'); ?>
						</div>
						<div>
							<? include(MODULE_DIR . 'admin/pointersettings.php'); ?>
						</div>
						<div>
							<? include(MODULE_DIR . 'admin/support.php'); ?>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div> <!-- .container -->

	<div class="container">
		<div class="content">
			<div class="col col-mb-12">
				<hr class="mt0">
				<?= $module_lang['contactsForSupport'] ?><br>
				<a href="https://github.com/dle-modules/DLE-YandexMaps" target="_blank">DLE-YandexMaps</a> <br>
				<a href="https://github.com/dle-modules/DLE-YandexMaps/issues/new" target="_blank"><?= $module_lang['moduleTextSupportСond'] ?></a><br>
			</div>
		</div>
	</div>
</body>
</html>
