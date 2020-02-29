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

    $cfg = json_decode(file_get_contents(ENGINE_DIR . '/data/ymaps_config.json'), true);

    if ($_POST['pointID'] > 0 || $_POST['pointID'] == 'default') {
        $post = array_filter($_POST);

        $catId = $post['pointID'];
        unset($post['pointID']);
        if (isset($post['preset']) && isset($post['iconColor'])) {
            $cfg['pointSettings']['catPoints'][$catId] = ['preset'    => $post['preset'],
                                                          'iconColor' => $post['iconColor'],
            ];
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
    file_put_contents(ENGINE_DIR.'/data/ymaps_config.json', $jsn);
    die ($jsn);
} else {
    die ('Access denied');
}