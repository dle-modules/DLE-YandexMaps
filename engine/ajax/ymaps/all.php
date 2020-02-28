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

include_once ENGINE_DIR . '/plugins/loader/loader.php';

if (@file_exists(MODULE_DIR . '/language/' . $cfg['main']['moduleLang'] . '.lng')) {
	include(DLEPlugins::Check(MODULE_DIR . '/language/' . $cfg['main']['moduleLang'] . '.lng'));
} else {
	die("Language file not found");
}


include (DLEPlugins::Check(ENGINE_DIR . '/data/config.php'));

require_once (DLEPlugins::Check(ENGINE_DIR . '/classes/mysql.php'));
require_once (DLEPlugins::Check(ENGINE_DIR . '/data/dbconfig.php'));
require_once (DLEPlugins::Check(ENGINE_DIR . '/modules/functions.php'));
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
$template_dir = ROOT_DIR . '/templates/' . $config['skin'];

// Пытаемся получить даные из шаблона с настройками
if ($_REQUEST['preset'] && file_exists($template_dir . '/' . $cfg['moduleName'] . '/all/' . $_REQUEST['preset'] . '.tpl')) {
	// Если файл существует - берём из него контент с настройками
	$preset = file_get_contents($template_dir . '/' . $cfg['moduleName'] . '/all/' . $_REQUEST['preset'] . '.tpl');
	$arConf = [];
} else {
	die('error');
}
// Разбиваем полученные из файла нестройки по строкам
$preset = explode("\n", $preset);

// Пробегаем по массиву и формируем список настроек
foreach ($preset as $v) {
	$_v = explode('=', $v);
	if (isset($_v[1])) {
		$arConf[trim($_v[0])] = trim($_v[1]);
	}
}

// Конфиг модуля
$yMapCfg = [// 'template' => !empty($template) ? $template : $cfg['moduleName'] . '/all/default',
	'cachePrefix' => !empty($arConf['cachePrefix']) ? $arConf['cachePrefix'] : 'news_ym_all', 'startFrom' => !empty($arConf['startFrom']) ? (int)$arConf['startFrom'] : '0', // C какой новости начать вывод
	'limit'       => !empty($arConf['limit']) ? (int)$arConf['limit'] : '1000', // Максимальное количество выводимых точек
	'postId'      => !empty($arConf['postId']) ? $arConf['postId'] : '', // ID новостей для вывода в массиве (через запятую, или черточку)
	'notPostId'   => !empty($arConf['notPostId']) ? $arConf['notPostId'] : '', // ID игнорируемых новостей (через запятую, или черточку)
	'catId'       => !empty($arConf['catId']) ? $arConf['catId'] : '', // Категории для показа	(через запятую, или черточку)
	'subcats'     => !empty($arConf['subcats']) ? $arConf['subcats'] : false, // Выводить подкатегории указанных категорий (&subcats=y), работает и с диапазонами.
	'notCatId'    => !empty($arConf['notCatId']) ? $arConf['notCatId'] : '', // Игнорируемые категории (через запятую, или черточку)
	'notSubcats'  => !empty($arConf['notSubcats']) ? $arConf['notSubcats'] : false, // Игнорировать подкатегории игнорируемых категорий (&notSubcats=y), работает и с диапазонами.
	'type'        => !empty($arConf['type']) ? $arConf['type'] : 'json', // Тип возвращаемых данных. Пока только json т.к. это наиболее правильный вариант
];

if ($yMapCfg['catId'] == 'this' || $yMapCfg['notCatId'] == 'this') {
	$url = parse_url($_SERVER['HTTP_REFERER']);
	$path = $url['path'];

	$arPath = explode('/', $path);

	$category_id = false;

	foreach ($cat_info as $key => $cat) {
		if ($cat['alt_name'] == $arPath[1]) {
			$category_id = $cat['id'];
			break;
		}
	}
}

// Если имеются переменные со значениями this, изменяем значение переменной cacheNameAddon
if ($yMapCfg['catId'] == 'this') {
	$yMapCfg['cacheNameAddon'] .= $category_id . 'cId_';
}
if ($yMapCfg['notCatId'] == 'this') {
	$yMapCfg['cacheNameAddon'] .= $category_id . 'nCId_';
}
if ($yMapCfg['postId'] == 'this') {
	$yMapCfg['cacheNameAddon'] .= $_REQUEST["newsid"] . 'pId_';
}
if ($yMapCfg['notPostId'] == 'this') {
	$yMapCfg['cacheNameAddon'] .= $_REQUEST["newsid"] . 'nPId_';
}

$cacheName = md5(implode('_', $yMapCfg)) . $config['skin'];
$yMap = false;
$yMap = dle_cache($yMapCfg['cachePrefix'], $cacheName . $config['skin'], $yMapCfg['cacheSuffix']);
if (!$yMap) {

	$mapHeight = ($cfg['main']['mapHeight']) ? $cfg['main']['mapHeight'] : '400';
	$controls = $cfg['main']['controls'];
	$controls = array_keys($controls);
	$controls = json_encode($controls);

	$catPoints = $cfg['pointSettings']['catPoints'];

	// Массив с условиями запроса
	$wheres = [];
	$wheres[] = 'approve';

	// Фильтрация КАТЕГОРИЙ по их ID
	if ($yMapCfg['catId'] == 'this') {
		$yMapCfg['catId'] = ($yMapCfg['subcats']) ? get_sub_cats($category_id) : $category_id;
	}
	if ($yMapCfg['notCatId'] == 'this') {
		$yMapCfg['notCatId'] = ($yMapCfg['notSubcats']) ? get_sub_cats($category_id) : $category_id;
	}

	if ($yMapCfg['catId'] || $yMapCfg['notCatId']) {
		$ignore = ($yMapCfg['notCatId']) ? 'NOT ' : '';
		$catArr = ($yMapCfg['notCatId']) ? getDiapazone($yMapCfg['notCatId'], $yMapCfg['notSubcats']) : getDiapazone($yMapCfg['catId'], $yMapCfg['subcats']);
		$wheres[] = $ignore . 'category regexp "[[:<:]](' . str_replace(',', '|', $catArr) . ')[[:>:]]"';
	}

	// Фильтрация НОВОСТЕЙ по их ID
	if ($yMapCfg['postId'] == 'this') {
		$yMapCfg['postId'] = $_REQUEST["newsid"];
	}
	if ($yMapCfg['notPostId'] == 'this') {
		$yMapCfg['notPostId'] = $_REQUEST["newsid"];
	}

	if (($yMapCfg['postId'] || $yMapCfg['notPostId']) && $yMapCfg['related'] == '') {
		$ignorePosts = ($yMapCfg['notPostId']) ? 'NOT ' : '';
		$postsArr = ($yMapCfg['notPostId']) ? getDiapazone($yMapCfg['notPostId']) : getDiapazone($yMapCfg['postId']);
		$wheres[] = $ignorePosts . 'id regexp "[[:<:]](' . str_replace(',', '|', $postsArr) . ')[[:>:]]"';
	}

	// Условие для отбора новостей, у которых есть координаты точек
	$wheres[] = 'xfields regexp "[[:<:]](' . $cfg['main']['coordsField'] . ')[[:>:]]"';

	// Складываем условия
	$where = (count($wheres)) ? ' WHERE ' . implode(' AND ', $wheres) : '';

	// Выполняем запрос
	$row = $db->super_query("SELECT id, title, category, xfields FROM " . PREFIX . "_post " . $where . " LIMIT " . $yMapCfg['startFrom'] . ", " . $yMapCfg['limit'], true);

	$geoObjects = [];
	if (count($row) > 0) {
		foreach ($row as $key => $placemark) {
			$geoObject = [];
			// Определяем ID геообъекта
			$geoObject['id'] = (int)$placemark['id'];

			$geoObject['type'] = 'Feature';

			$_catId = intval($placemark['category']);
			$geoObject['category'] = $_catId;

			$catPoint = ($catPoints[$_catId]) ? $_catId : 'default';
			$placemarkStyle = $catPoints[$catPoint];

			// небольшой костылёк для приведения типов, иначе я.карта не воспринимает.
			if ($placemarkStyle['iconImageSize']) {
				$placemarkStyle['iconImageSize'][0] = (int)$placemarkStyle['iconImageSize'][0];
				$placemarkStyle['iconImageSize'][1] = (int)$placemarkStyle['iconImageSize'][1];
			}
			if ($placemarkStyle['iconImageOffset']) {
				$placemarkStyle['iconImageOffset'][0] = (int)$placemarkStyle['iconImageOffset'][0];
				$placemarkStyle['iconImageOffset'][1] = (int)$placemarkStyle['iconImageOffset'][1];
			}

			$geoObject['options'] = $placemarkStyle;
			$title = htmlspecialchars(strip_tags(stripslashes($placemark['title'])), ENT_QUOTES, $config['charset']);
			$geoObject['properties']['clusterCaption'] = $title;
			$geoObject['properties']['hintContent'] = $title;

			$xf = xfieldsdataload($placemark['xfields']);

			$xfCo_ = html_entity_decode($xf[$cfg['main']['coordsField']], ENT_COMPAT);

			$xfCoords = json_decode($xfCo_, true);


			$geoObject['geometry']['type'] = 'Point';
			$geoObject['geometry']['coordinates'][0] = $xfCoords['lat'];
			$geoObject['geometry']['coordinates'][1] = $xfCoords['lon'];

			$geoObjects[] = $geoObject;
		}
	}
	$yMap['type'] = 'FeatureCollection';
	$yMap['features'] = $geoObjects;

	$yMap = json_encode($yMap);

	create_cache($yMapCfg['cachePrefix'], $yMap, $cacheName . $config['skin'], true);
}
die($yMap);

/**
 * Получение диапазона между двумя цифрами, и не только
 *
 * @param bool $diapazone
 * @param bool $subcats
 *
 * @internal param string $diapasone
 * @return string
 * @author   Elkhan I. Isaev <elhan.isaev@gmail.com>
 */
function getDiapazone($diapazone = false, $subcats = false) {
	if ($diapazone !== false) {
		$diapazone = str_replace(" ", "", $diapazone);
		if (strpos($diapazone, ',') !== false) {
			$diapazoneArray = explode(',', $diapazone);
			$diapazoneArray = array_diff($diapazoneArray, [null]);
			foreach ($diapazoneArray as $v) {
				if (strpos($v, '-') !== false) {
					preg_match("#(\d+)-(\d+)#i", $v, $test);
					$diapazone = !empty($diapazone) && is_array($diapazone) ? array_merge($diapazone, (!empty ($test) ? range($test[1], $test[2]) : [])) : (!empty ($test) ? range($test[1], $test[2]) : []);
				} else {
					$diapazone = !empty($diapazone) && is_array($diapazone) ? array_merge($diapazone, (!empty ($v) ? [(int)$v] : [])) : (!empty ($v) ? [(int)$v] : []);
				}
			}
		} elseif (strpos($diapazone, '-') !== false) {
			preg_match("#(\d+)-(\d+)#i", $diapazone, $test);
			$diapazone = !empty ($test) ? range($test[1], $test[2]) : [];
		} else {
			$diapazone = [(int)$diapazone];
		}
		if (!empty($diapazone)) {
			if ($subcats && function_exists('get_sub_cats')) {
				foreach ($diapazone as $d) {
					$_sc = explode('|', get_sub_cats($d));
					foreach ($_sc as $v) {
						array_push($diapazone, $v);
					}
				}
			}
			$diapazone = array_unique($diapazone);
		} else {
			$diapazone = [];
		}
		$diapazone = implode(',', $diapazone);
	}

	return $diapazone;
}
