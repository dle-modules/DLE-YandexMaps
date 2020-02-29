<?php
/**
 * DLE-YandexMaps — Бесплатный модуль Яндекс Карт для DLE
 *
 * @author     ПафНутиЙ <pafnuty10@gmail.com>
 * @link       https://git.io/v9irg
 */

if (!defined('DATALIFEENGINE')) {
    header("HTTP/1.1 403 Forbidden");
    header('Location: ../../');
    die("Hacking attempt!");
}

/**
 * Основной код файла
 */
if ($member_id['user_group'] == '1') {
    include_once(DLEPlugins::Check(ENGINE_DIR.'/modules/ymaps/language/Russian.lng'));
    require_once(DLEPlugins::Check(ENGINE_DIR.'/modules/ymaps/admin/classes/xfields.php'));

    $fun         = new xfClass;
    $name        = (isset($_REQUEST['name'])) ? $db->safesql($_REQUEST['name']) : false;
    $description = (isset($_REQUEST['description'])) ? $db->safesql($_REQUEST['description']) : false;
    $value       = (isset($_REQUEST['value'])) ? $db->safesql($_REQUEST['value']) : false;
    $fieldType   = (isset($_REQUEST['fieldType'])) ? $db->safesql($_REQUEST['fieldType']) : false;

    $xf = $fun->xf('add', $name, $description, $value, $fieldType)->result;

    if ($xf) {
        $content = '<div class="ta-center mb10 alert alert-info">'.$module_lang['field'].' <b>'.$name.'</b> '
            .$module_lang['successfullyСreated'].'</div>';
    } else {
        $content = '<div class="ta-center mb10">'.$module_lang['errTryAgainLater'].'</div>';
    }

    $showResult = '<div class="content">
		<div class="col col-mb-12 col-8 col-dt-4 col-id-3 col-center modal-white">
		<span class="modal-close popup-modal-dismiss">×</span>
			<div class="modal-content">
				'.$content.'
				<div class="ta-center mb10">
					<a href="'.$config['admin_path']
        .'?mod=xfields&xfieldsaction=configure" class="btn btn-small modal-close" target="_blank">'
        .$module_lang['xfieldsSetup'].'</a>
					<span class="btn btn-small btn-red modal-close">'.$module_lang['moduleActionClose'].'</span>
				</div>
			</div>
		</div>
	</div>';

    die ($showResult);
} else {
    die ('Access denied');
}