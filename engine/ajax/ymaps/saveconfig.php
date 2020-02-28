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

include (DLEPlugins::Check(ENGINE_DIR . '/data/config.php'));

require_once (DLEPlugins::Check(ENGINE_DIR . '/classes/mysql.php'));
require_once (DLEPlugins::Check(ENGINE_DIR . '/data/dbconfig.php'));
require_once (DLEPlugins::Check(ENGINE_DIR . '/modules/functions.php'));
dle_session();


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

/**
 * Основной код файла
 */
if ($member_id['user_group'] == '1') {

	if ($_POST['pointID'] > 0 || $_POST['pointID'] == 'default') {
		$post = array_filter($_POST);

		$catId = $post['pointID'];
		unset($post['pointID']);
		if (isset($post['preset']) && isset($post['iconColor'])) {
			$cfg['pointSettings']['catPoints'][$catId] = ['preset' => $post['preset'], 'iconColor' => $post['iconColor'],];
		} elseif (isset($post['iconLayout'])) {
			unset($post['preset']);
			$cfg['pointSettings']['catPoints'][$catId] = $post;
		} elseif ($post['preset']) {
			$cfg['pointSettings']['catPoints'][$catId] = ['preset' => $post['preset'],];
		}
		if (isset($_POST['deletePoint']) && $_POST['deletePoint'] == 'y') {
			unset($cfg['pointSettings']['catPoints'][$catId]);
		}

	}
	if ($_POST['mapsettings']) {
		unset($_POST['mapsettings']);
		$cfg['main'] = $_POST;
	}

	$jsn = json_encode($cfg);
	file_put_contents(ENGINE_DIR . '/data/ymaps_config.json', $jsn);
	die ($jsn);
} else {
	die ('Access denied');
}