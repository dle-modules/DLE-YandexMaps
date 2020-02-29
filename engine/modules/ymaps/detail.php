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

$cfg = json_decode(file_get_contents(ENGINE_DIR.'/data/ymaps_config.json'));

include_once(DLEPlugins::Check(ENGINE_DIR.'/modules/ymaps/language/Russian.lng'));

// Список разрешенных полей, отбираемых из БД.
$existFields = ['title', 'short_story', 'full_story'];

// Конфиг модуля
$yMapCfg   = [
    'cachePrefix' => !empty($arConf['cachePrefix']) ? $arConf['cachePrefix'] : 'full_'.(int)$_REQUEST['newsid'].'_ym',
    'id'          => (!empty($id)) ? (int)$id : false,
    'template'    => !empty($template) ? $template : 'ymaps/default',
    'fields'      => !empty($fields) ? $fields : '',
];
$cacheName = md5(implode('_', $yMapCfg)).$config['skin'];
$yMap      = false;
$yMap      = dle_cache($yMapCfg['cachePrefix'], $cacheName.$config['skin'], true);
if (!$yMap) {

    // Поля, которые отбираются из БД в любом случае
    $_queryFields = ['id', 'category', 'xfields', 'approve'];

    // убираем пробелы, на всякий случай
    $fields = str_replace(' ', '', $fields);

    // Разбиваем поля на массив
    $_fields = explode(',', $fields);

    // Сравниваем со списком разрешенных полей
    foreach ($_fields as $key => $field) {
        if (!in_array($field, $existFields)) {
            // Удаляем лишние поля из массива
            unset($_fields[$key]);
        }
    }

    // Объединяем массивы
    $arQueryFields = array_merge($_queryFields, $_fields);

    // И опять разбиваем, для вставки в запрос.
    $queryFields = implode(', ', $arQueryFields);

    // API-key
    $key = ($cfg->main->apiKey) ? '&apikey='.$cfg->main->apiKey : '';


    $mapHeight = ($cfg->main->mapHeight) ? $cfg->main->mapHeight : '400';
    $controls  = (array)$cfg->main->controls;
    $controls  = array_keys($controls);
    $controls  = json_encode($controls);

    if (file_exists(TEMPLATE_DIR.'/'.$yMapCfg['template'].'.tpl')) {

        $tpl->load_template($yMapCfg['template'].'.tpl');

        if ($yMapCfg['id'] > 0) {
            $row = $db->super_query("SELECT ".$queryFields." FROM ".PREFIX."_post WHERE id = ".$yMapCfg['id']);

            $catPoints = $cfg->pointSettings->catPoints;
            $_catId    = intval($row['category']);

            $catPoint       = ($catPoints->$_catId) ? $_catId : 'default';
            $placemarkStyle = (array)$catPoints->$catPoint;
            // небольшой костылёк для приведения типов, иначе я.карта не воспринимает.
            if ($placemarkStyle['iconImageSize']) {
                $placemarkStyle['iconImageSize'][0] = (int)$placemarkStyle['iconImageSize'][0];
                $placemarkStyle['iconImageSize'][1] = (int)$placemarkStyle['iconImageSize'][1];
            }
            if ($placemarkStyle['iconImageOffset']) {
                $placemarkStyle['iconImageOffset'][0] = (int)$placemarkStyle['iconImageOffset'][0];
                $placemarkStyle['iconImageOffset'][1] = (int)$placemarkStyle['iconImageOffset'][1];
            }

            $placemarkStyle = json_encode($placemarkStyle);

            $tpl->set('{placemarkStyle}', $placemarkStyle);

            if ($row['date']) {
                $row['date'] = strtotime($row['date']);
            }
            if ($row['short_story']) {
                $row['short_story'] = stripslashes($row['short_story']);
            }
            if ($row['full_story']) {
                $row['full_story'] = stripslashes($row['full_story']);
            }

            if ($row['category']) {
                $my_cat      = [];
                $my_cat_link = [];
                $cat_list    = explode(',', $row['category']);

                $config['category_separator'] = ($config['category_separator'] != ',') ? ' '
                    .$config['category_separator'] : ', ';

                if (count($cat_list) == 1) {
                    $my_cat[]    = $cat_info[$cat_list[0]]['name'];
                    $my_cat_link = get_categories($cat_list[0], $config['category_separator']);
                } else {
                    foreach ($cat_list as $element) {
                        if ($element) {
                            $my_cat[] = $cat_info[$element]['name'];
                            if ($config['allow_alt_url']) {
                                $my_cat_link[] = "<a href=\"".$config['http_home_url'].get_url($element)
                                    ."/\">{$cat_info[$element]['name']}</a>";
                            } else {
                                $my_cat_link[]
                                    = "<a href=\"$PHP_SELF?do=cat&category={$cat_info[$element]['alt_name']}\">{$cat_info[$element]['name']}</a>";
                            }
                        }
                    }
                    $my_cat_link = implode("{$config['category_separator']} ", $my_cat_link);
                }
                $my_cat = implode("{$config['category_separator']} ", $my_cat);
            }

            if ($row['title']) {
                $row['title'] = strip_tags($row['title']);
            }

            $xf = xfieldsdataload($row['xfields']);

            $xfCo_ = html_entity_decode($xf[$cfg->main->coordsField], ENT_COMPAT);

            $xfCoords = json_decode($xfCo_);

            $lon  = ($xfCoords->lon) ? $xfCoords->lon : $cfg->main->mapCenter->longitude;
            $lat  = ($xfCoords->lat) ? $xfCoords->lat : $cfg->main->mapCenter->latitude;
            $zoom = ($xfCoords->zoom) ? $xfCoords->zoom : $cfg->main->mapCenter->zoom;

        } else {
            die('empty news id');
        }

        $script_yandex = '<script src="https://api-maps.yandex.ru/2.1/?lang=ru_RU'.$key.'"></script>';
        $script_popup  = '<script src="/engine/modules/ymaps/js/jquery.magnificpopup.min.js"></script>';
        $script_module = '<script src="/templates/'.$config['skin'].'/ymaps/ymaps_public.js"></script>';

        $tpl->set('{key}', $key);

        $tpl->set('{scripts}', $script_yandex.$script_popup.$script_module);
        $tpl->set('{script_yandex}', $script_yandex);
        $tpl->set('{script_popup}', $script_popup);
        $tpl->set('{script_module}', $script_module);

        $tpl->set('{lon}', $lon);
        $tpl->set('{lat}', $lat);
        $tpl->set('{zoom}', $zoom);
        $tpl->set('{mapHeight}', $mapHeight);
        $tpl->set('{controls}', $controls);
        $tpl->set('{placemarkStyle}', $placemarkStyle);
        $tpl->set('{baloon}', '');
        $tpl->set('{id}', $row['id']);


        // Определяем список тегов, используемых в шаблоне на основании массива с данными о разрешенных полях
        foreach ($existFields as $field) {

            if ($row[$field]) {
                $tpl->set('{'.$field.'}', $row[$field]);
                $tpl->copy_template = preg_replace("'\\[not_".$field."\\](.*?)\\[/not_".$field."\\]'is", "",
                    $tpl->copy_template);
                $tpl->copy_template = str_replace("[".$field."]", "", $tpl->copy_template);
                $tpl->copy_template = str_replace("[/".$field."]", "", $tpl->copy_template);
            } else {
                $tpl->set('{'.$field.'}', "");
                $tpl->copy_template = preg_replace("'\\[".$field."\\](.*?)\\[/".$field."\\]'is", "",
                    $tpl->copy_template);
                $tpl->copy_template = str_replace("[not_".$field."]", "", $tpl->copy_template);
                $tpl->copy_template = str_replace("[/not_".$field."]", "", $tpl->copy_template);
            }
        }

        // Работаем с допполями
        if (strpos($tpl->copy_template, "[xfvalue_") !== false OR strpos($tpl->copy_template, "[xfgiven_") !== false) {

            $xfieldsdata = $xf;
            $xfields     = xfieldsload();
            if (count($xfields)) {

                $xfieldsdata = xfieldsdataload($row['xfields']);

                foreach ($xfields as $value) {
                    $preg_safe_name = preg_quote($value[0], "'");

                    if ($value[20]) {

                        $value[20] = explode(',', $value[20]);

                        if ($value[20][0] AND !in_array($member_id['user_group'], $value[20])) {
                            $xfieldsdata[$value[0]] = "";
                        }

                    }

                    if ($value[3] == "yesorno") {

                        if (intval($xfieldsdata[$value[0]])) {
                            $xfgiven                = true;
                            $xfieldsdata[$value[0]] = $lang['xfield_xyes'];
                        } else {
                            $xfgiven                = false;
                            $xfieldsdata[$value[0]] = $lang['xfield_xno'];
                        }

                    } else {

                        if ($xfieldsdata[$value[0]] == "") {
                            $xfgiven = false;
                        } else {
                            $xfgiven = true;
                        }

                    }

                    if (!$xfgiven) {
                        $tpl->copy_template
                                            = preg_replace("'\\[xfgiven_{$preg_safe_name}\\](.*?)\\[/xfgiven_{$preg_safe_name}\\]'is",
                            "", $tpl->copy_template);
                        $tpl->copy_template = str_ireplace("[xfnotgiven_{$value[0]}]", "", $tpl->copy_template);
                        $tpl->copy_template = str_ireplace("[/xfnotgiven_{$value[0]}]", "", $tpl->copy_template);
                    } else {
                        $tpl->copy_template
                                            = preg_replace("'\\[xfnotgiven_{$preg_safe_name}\\](.*?)\\[/xfnotgiven_{$preg_safe_name}\\]'is",
                            "", $tpl->copy_template);
                        $tpl->copy_template = str_ireplace("[xfgiven_{$value[0]}]", "", $tpl->copy_template);
                        $tpl->copy_template = str_ireplace("[/xfgiven_{$value[0]}]", "", $tpl->copy_template);
                    }

                    if (strpos($tpl->copy_template, "[ifxfvalue {$value[0]}") !== false) {
                        $tpl->copy_template = preg_replace_callback("#\\[ifxfvalue(.+?)\\](.+?)\\[/ifxfvalue\\]#is",
                            "check_xfvalue", $tpl->copy_template);
                    }

                    if ($value[6] AND !empty($xfieldsdata[$value[0]])) {
                        $temp_array = explode(",", $xfieldsdata[$value[0]]);
                        $value3     = [];

                        foreach ($temp_array as $value2) {

                            $value2 = trim($value2);

                            if ($value2) {

                                $value4 = str_replace(["&#039;", "&quot;", "&amp;", "&#123;", "&#91;", "&#58;"],
                                    ["'", '"', "&", "{", "[", ":"], $value2);

                                if ($value[3] == "datetime") {

                                    $value2 = strtotime($value4);

                                    if (!trim($value[24])) {
                                        $value[24] = $config['timestamp_active'];
                                    }

                                    if ($value[25]) {

                                        if ($value[26]) {
                                            $value2 = langdate($value[24], $value2);
                                        } else {
                                            $value2 = langdate($value[24], $value2, false, $customlangdate);
                                        }

                                    } else {
                                        $value2 = date($value[24], $value2);
                                    }

                                }

                                if ($config['allow_alt_url']) {
                                    $value3[] = "<a href=\"".$config['http_home_url']."xfsearch/".$value[0]."/"
                                        .rawurlencode($value4)."/\">".$value2."</a>";
                                } else {
                                    $value3[] = "<a href=\"$PHP_SELF?do=xfsearch&amp;xfname=".$value[0]."&amp;xf="
                                        .rawurlencode($value4)."\">".$value2."</a>";
                                }
                            }

                        }

                        if (empty($value[21])) {
                            $value[21] = ", ";
                        }

                        $xfieldsdata[$value[0]] = implode($value[21], $value3);

                        unset($temp_array);
                        unset($value2);
                        unset($value3);
                        unset($value3);

                    } elseif ($value[3] == "datetime" AND !empty($xfieldsdata[$value[0]])) {

                        $xfieldsdata[$value[0]] = strtotime(str_replace("&#58;", ":", $xfieldsdata[$value[0]]));

                        if (!trim($value[24])) {
                            $value[24] = $config['timestamp_active'];
                        }

                        if ($value[25]) {

                            if ($value[26]) {
                                $xfieldsdata[$value[0]] = langdate($value[24], $xfieldsdata[$value[0]]);
                            } else {
                                $xfieldsdata[$value[0]] = langdate($value[24], $xfieldsdata[$value[0]], false,
                                    $customlangdate);
                            }

                        } else {
                            $xfieldsdata[$value[0]] = date($value[24], $xfieldsdata[$value[0]]);
                        }


                    }

                    if ($config['allow_links'] AND $value[3] == "textarea" AND function_exists('replace_links')) {
                        $xfieldsdata[$value[0]] = replace_links($xfieldsdata[$value[0]], $replace_links['news']);
                    }

                    if ($value[3] == "image" AND $xfieldsdata[$value[0]]) {

                        $temp_array = explode('|', $xfieldsdata[$value[0]]);

                        if (count($temp_array) > 1) {

                            $temp_alt   = $temp_array[0];
                            $temp_value = $temp_array[1];

                        } else {

                            $temp_alt   = '';
                            $temp_value = $temp_array[0];

                        }

                        $path_parts = @pathinfo($temp_value);

                        if ($value[12] AND file_exists(ROOT_DIR."/uploads/posts/".$path_parts['dirname']."/thumbs/"
                                .$path_parts['basename'])
                        ) {
                            $thumb_url = $config['http_home_url']."uploads/posts/".$path_parts['dirname']."/thumbs/"
                                .$path_parts['basename'];
                            $img_url   = $config['http_home_url']."uploads/posts/".$path_parts['dirname']."/"
                                .$path_parts['basename'];
                        } else {
                            $img_url   = $config['http_home_url']."uploads/posts/".$path_parts['dirname']."/"
                                .$path_parts['basename'];
                            $thumb_url = "";
                        }

                        if ($thumb_url) {
                            $tpl->set("[xfvalue_thumb_url_{$value[0]}]", $thumb_url);
                            $xfieldsdata[$value[0]]
                                = "<a href=\"$img_url\" class=\"highslide\" target=\"_blank\"><img class=\"xfieldimage {$value[0]}\" src=\"$thumb_url\" alt=\"{$temp_alt}\"></a>";
                        } else {
                            $tpl->set("[xfvalue_thumb_url_{$value[0]}]", $img_url);
                            $xfieldsdata[$value[0]]
                                = "<img class=\"xfieldimage {$value[0]}\" src=\"{$img_url}\" alt=\"{$temp_alt}\">";
                        }

                        $tpl->set("[xfvalue_image_url_{$value[0]}]", $img_url);

                    }

                    if ($value[3] == "image" AND !$xfieldsdata[$value[0]]) {

                        $tpl->set("[xfvalue_thumb_url_{$value[0]}]", "");
                        $tpl->set("[xfvalue_image_url_{$value[0]}]", "");

                    }

                    if ($value[3] == "imagegalery" AND $xfieldsdata[$value[0]] AND stripos($tpl->copy_template,
                            "[xfvalue_{$value[0]}") !== false
                    ) {

                        $fieldvalue_arr       = explode(',', $xfieldsdata[$value[0]]);
                        $gallery_image        = [];
                        $gallery_single_image = [];
                        $xf_image_count       = 0;
                        $single_need          = false;

                        if (stripos($tpl->copy_template, "[xfvalue_{$value[0]} image=") !== false) {
                            $single_need = true;
                        }

                        foreach ($fieldvalue_arr as $temp_value) {
                            $xf_image_count++;

                            $temp_value = trim($temp_value);

                            if ($temp_value == "") {
                                continue;
                            }

                            $temp_array = explode('|', $temp_value);

                            if (count($temp_array) > 1) {

                                $temp_alt   = $temp_array[0];
                                $temp_value = $temp_array[1];

                            } else {

                                $temp_alt   = '';
                                $temp_value = $temp_array[0];

                            }

                            $path_parts = @pathinfo($temp_value);

                            if ($value[12] AND file_exists(ROOT_DIR."/uploads/posts/".$path_parts['dirname']."/thumbs/"
                                    .$path_parts['basename'])
                            ) {
                                $thumb_url = $config['http_home_url']."uploads/posts/".$path_parts['dirname']."/thumbs/"
                                    .$path_parts['basename'];
                                $img_url   = $config['http_home_url']."uploads/posts/".$path_parts['dirname']."/"
                                    .$path_parts['basename'];
                            } else {
                                $img_url   = $config['http_home_url']."uploads/posts/".$path_parts['dirname']."/"
                                    .$path_parts['basename'];
                                $thumb_url = "";
                            }

                            if ($thumb_url) {

                                $gallery_image[]
                                    = "<li><a href=\"$img_url\" onclick=\"return hs.expand(this, { slideshowGroup: 'xf_{$row['id']}_{$value[0]}' })\" target=\"_blank\"><img src=\"{$thumb_url}\" alt=\"{$temp_alt}\"></a></li>";
                                $gallery_single_image['[xfvalue_'.$value[0].' image="'.$xf_image_count.'"]']
                                    = "<a href=\"{$img_url}\" class=\"highslide\" target=\"_blank\"><img class=\"xfieldimage {$value[0]}\" src=\"{$thumb_url}\" alt=\"{$temp_alt}\"></a>";

                            } else {
                                $gallery_image[] = "<li><img src=\"{$img_url}\" alt=\"{$temp_alt}\"></li>";
                                $gallery_single_image['[xfvalue_'.$value[0].' image="'.$xf_image_count.'"]']
                                                 = "<img class=\"xfieldimage {$value[0]}\" src=\"{$img_url}\" alt=\"{$temp_alt}\">";
                            }

                        }

                        if ($single_need AND count($gallery_single_image)) {
                            foreach ($gallery_single_image as $temp_key => $temp_value) {
                                $tpl->set($temp_key, $temp_value);
                            }
                        }

                        $xfieldsdata[$value[0]] = "<ul class=\"xfieldimagegallery {$value[0]}\">"
                            .implode($gallery_image)."</ul>";

                    }

                    if ($config['image_lazy'] AND $view_template != "print") {
                        $xfieldsdata[$value[0]] = preg_replace_callback("#<img(.+?)>#i", "enable_lazyload",
                            $xfieldsdata[$value[0]]);
                    }

                    $tpl->set("[xfvalue_{$value[0]}]", $xfieldsdata[$value[0]]);

                    $all_xf_content[] = $xfieldsdata[$value[0]];

                    if (preg_match("#\\[xfvalue_{$preg_safe_name} limit=['\"](.+?)['\"]\\]#i", $tpl->copy_template,
                        $matches)
                    ) {
                        $count = intval($matches[1]);

                        $xfieldsdata[$value[0]] = str_replace("><", "> <", $xfieldsdata[$value[0]]);
                        $xfieldsdata[$value[0]] = strip_tags($xfieldsdata[$value[0]], "<br>");
                        $xfieldsdata[$value[0]] = trim(str_replace("<br>", " ", str_replace("<br />", " ",
                            str_replace("\n", " ", str_replace("\r", "", $xfieldsdata[$value[0]])))));
                        $xfieldsdata[$value[0]] = preg_replace('/\s+/u', ' ', $xfieldsdata[$value[0]]);

                        if ($count AND dle_strlen($xfieldsdata[$value[0]], $config['charset']) > $count) {

                            $xfieldsdata[$value[0]] = dle_substr($xfieldsdata[$value[0]], 0, $count,
                                $config['charset']);

                            if (($temp_dmax = dle_strrpos($xfieldsdata[$value[0]], ' ', $config['charset']))) {
                                $xfieldsdata[$value[0]] = dle_substr($xfieldsdata[$value[0]], 0, $temp_dmax,
                                    $config['charset']);
                            }

                        }

                        $tpl->set($matches[0], $xfieldsdata[$value[0]]);

                    }
                }
            }
        }

        if (stripos($tpl->copy_template, "{image-") !== false && in_array('short_story', $arQueryFields)) {

            $images = [];
            preg_match_all('/(img|src)=("|\')[^"\'>]+/i', $row['short_story'].$row['xfields'], $media);
            $data = preg_replace('/(img|src)("|\'|="|=\')(.*)/i', "$3", $media[0]);

            foreach ($data as $url) {
                $info = pathinfo($url);
                if (isset($info['extension'])) {
                    if ($info['filename'] == "spoiler-plus" OR $info['filename'] == "spoiler-minus"
                        OR strpos($info['dirname'], 'engine/data/emoticons') !== false
                    ) {
                        continue;
                    }
                    $info['extension'] = strtolower($info['extension']);
                    if (($info['extension'] == 'jpg') || ($info['extension'] == 'jpeg') || ($info['extension'] == 'gif')
                        || ($info['extension'] == 'png')
                        || ($info['extension'] == 'webp')
                    ) {
                        array_push($images, $url);
                    }
                }
            }

            if (count($images)) {
                $i = 0;
                foreach ($images as $url) {
                    $i++;
                    $tpl->copy_template = str_replace('{image-'.$i.'}', $url, $tpl->copy_template);
                    $tpl->copy_template = str_replace('[image-'.$i.']', "", $tpl->copy_template);
                    $tpl->copy_template = str_replace('[/image-'.$i.']', "", $tpl->copy_template);
                    $tpl->copy_template = preg_replace("#\[not-image-{$i_count}\](.+?)\[/not-image-{$i_count}\]#is", "",
                        $tpl->copy_template);
                }

            }

            $tpl->copy_template = preg_replace("#\[image-(.+?)\](.+?)\[/image-(.+?)\]#is", "", $tpl->copy_template);
            $tpl->copy_template = preg_replace("#\\{image-(.+?)\\}#i", "{THEME}/dleimages/no_image.jpg",
                $tpl->copy_template);
            $tpl->copy_template = preg_replace("#\[not-image-(.+?)\]#i", "", $tpl->copy_template);
            $tpl->copy_template = preg_replace("#\[/not-image-(.+?)\]#i", "", $tpl->copy_template);

        }

        if (stripos($tpl->copy_template, "{fullimage-") !== false && in_array('full_story', $arQueryFields)) {

            $images = [];
            preg_match_all('/(img|src)=("|\')[^"\'>]+/i', $row['full_story'], $media);
            $data = preg_replace('/(img|src)("|\'|="|=\')(.*)/i', "$3", $media[0]);

            foreach ($data as $url) {
                $info = pathinfo($url);
                if (isset($info['extension'])) {
                    if ($info['filename'] == "spoiler-plus" OR $info['filename'] == "spoiler-minus"
                        OR strpos($info['dirname'], 'engine/data/emoticons') !== false
                    ) {
                        continue;
                    }
                    $info['extension'] = strtolower($info['extension']);
                    if (($info['extension'] == 'jpg') || ($info['extension'] == 'jpeg') || ($info['extension'] == 'gif')
                        || ($info['extension'] == 'png')
                        || ($info['extension'] == 'webp')
                    ) {
                        array_push($images, $url);
                    }
                }
            }

            if (count($images)) {
                $i = 0;
                foreach ($images as $url) {
                    $i++;
                    $tpl->copy_template = str_replace('{fullimage-'.$i.'}', $url, $tpl->copy_template);
                    $tpl->copy_template = str_replace('[fullimage-'.$i.']', "", $tpl->copy_template);
                    $tpl->copy_template = str_replace('[/fullimage-'.$i.']', "", $tpl->copy_template);
                }

            }

            $tpl->copy_template = preg_replace("#\[fullimage-(.+?)\](.+?)\[/fullimage-(.+?)\]#is", "",
                $tpl->copy_template);
            $tpl->copy_template = preg_replace("#\\{fullimage-(.+?)\\}#i", "{THEME}/dleimages/no_image.jpg",
                $tpl->copy_template);

        }

        $images     = [];
        $allcontent = $row['full_story'].$row['short_story'].$row['xfields'];
        preg_match_all('/(img|src)=("|\')[^"\'>]+/i', $allcontent, $media);
        $data = preg_replace('/(img|src)("|\'|="|=\')(.*)/i', "$3", $media[0]);

        foreach ($data as $url) {
            $info = pathinfo($url);
            if (isset($info['extension'])) {
                if ($info['filename'] == "spoiler-plus" OR $info['filename'] == "spoiler-minus"
                    OR strpos($info['dirname'], 'engine/data/emoticons') !== false
                ) {
                    continue;
                }
                $info['extension'] = strtolower($info['extension']);
                if (($info['extension'] == 'jpg' || $info['extension'] == 'jpeg' || $info['extension'] == 'gif'
                        || $info['extension'] == 'png'
                        || $info['extension'] == 'webp') AND !in_array($url, $images)
                ) {
                    array_push($images, $url);
                }
            }
        }

        if (in_array('title', $arQueryFields)) {
            if (preg_match("#\\{title limit=['\"](.+?)['\"]\\}#i", $tpl->copy_template, $matches)) {
                $count = intval($matches[1]);
                if ($count AND dle_strlen($row['title'], $config['charset']) > $count) {
                    $row['title'] = dle_substr($row['title'], 0, $count, $config['charset']);
                    if (($temp_dmax = dle_strrpos($row['title'], ' ', $config['charset']))) {
                        $row['title'] = dle_substr($row['title'], 0, $temp_dmax, $config['charset']);
                    }
                }
                $tpl->set($matches[0], $row['title']);
            }
        }

        if (in_array('short_story', $arQueryFields)) {
            if (preg_match("#\\{short_story limit=['\"](.+?)['\"]\\}#i", $tpl->copy_template, $matches)) {
                $count              = intval($matches[1]);
                $row['short_story'] = str_replace("</p><p>", " ", $row['short_story']);
                $row['short_story'] = strip_tags($row['short_story'], "<br>");
                $row['short_story'] = trim(str_replace("<br>", " ",
                    str_replace("<br />", " ", str_replace("\n", " ", str_replace("\r", "", $row['short_story'])))));
                if ($count AND dle_strlen($row['short_story'], $config['charset']) > $count) {
                    $row['short_story'] = dle_substr($row['short_story'], 0, $count, $config['charset']);
                    if (($temp_dmax = dle_strrpos($row['short_story'], ' ', $config['charset']))) {
                        $row['short_story'] = dle_substr($row['short_story'], 0, $temp_dmax, $config['charset']);
                    }
                }
                $tpl->set($matches[0], $row['short_story']);
            }
        }

        if (in_array('full_story', $arQueryFields)) {
            if (preg_match("#\\{full_story limit=['\"](.+?)['\"]\\}#i", $tpl->copy_template, $matches)) {
                $count             = intval($matches[1]);
                $row['full_story'] = str_replace("</p><p>", " ", $row['full_story']);
                $row['full_story'] = strip_tags($row['full_story'], "<br>");
                $row['full_story'] = trim(str_replace("<br>", " ",
                    str_replace("<br />", " ", str_replace("\n", " ", str_replace("\r", "", $row['full_story'])))));
                if ($count AND dle_strlen($row['full_story'], $config['charset']) > $count) {
                    $row['full_story'] = dle_substr($row['full_story'], 0, $count, $config['charset']);
                    if (($temp_dmax = dle_strrpos($row['full_story'], ' ', $config['charset']))) {
                        $row['full_story'] = dle_substr($row['full_story'], 0, $temp_dmax, $config['charset']);
                    }
                }
                $tpl->set($matches[0], $row['full_story']);

            }
        }

        $tpl->compile('yMap');
        $yMap = $tpl->result['yMap'];

        create_cache($yMapCfg['cachePrefix'], $yMap, $cacheName.$config['skin'], true);

        $tpl->clear();
    } else {
        $yMap = '<b style="color:red">'.$module_lang['moduleTextMissedTemplateFile'].': '.$config['skin'].'/'
            .$yMapCfg['template'].'.tpl</b>';
    }


}
echo $yMap;