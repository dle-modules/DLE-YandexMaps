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
require_once ENGINE_DIR . '/modules/sitelogin.php';
require_once MODULE_DIR . 'admin/classes/xfields.php';


/**
 * Основной код файла
 */

if ($member_id['user_group'] == '1') {
	$fun = new xfClass;
	$name = (isset($_REQUEST['name'])) ? $db->safesql($_REQUEST['name']) : false;
	$description = (isset($_REQUEST['description'])) ? $db->safesql($_REQUEST['description']) : false;
	$value = (isset($_REQUEST['value'])) ? $db->safesql($_REQUEST['value']) : false;
	$fieldType = (isset($_REQUEST['fieldType'])) ? $db->safesql($_REQUEST['fieldType']) : false;

	$xf = $fun->xf('add', $name, $description, $value, $fieldType)->result;

	if ($xf) {
		$content = '<div class="ta-center mb10 alert alert-info">' . $module_lang['field'] . ' <b>' . $name . '</b> ' . $module_lang['successfullyСreated'] . '</div>';
	} else {
		$content = '<div class="ta-center mb10">' . $module_lang['errTryAgainLater'] . '</div>';
	}

	$showResult = '<div class="content">
		<div class="col col-mb-12 col-8 col-dt-4 col-id-3 col-center modal-white">
		<span class="modal-close popup-modal-dismiss">×</span>
			<div class="modal-content">
				' . $content . '
				<div class="ta-center mb10">
					<a href="' . $config['admin_path'] . '?mod=xfields&xfieldsaction=configure" class="btn btn-small modal-close" target="_blank">' . $module_lang['xfieldsSetup'] . '</a>
					<span class="btn btn-small btn-red modal-close">' . $module_lang['moduleActionClose'] . '</span>
				</div>
			</div>
		</div>
	</div>';

	die ($showResult);
} else {
	die ('Access denied');
}