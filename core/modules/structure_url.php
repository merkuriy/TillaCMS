<?php

/*
 * Модуль Structure - структура
 */
class modules_structure_url {

    /*
     *  Разбор URL адреса и построение массива $system['structure'].
     *  Если $request_uri это ID раздела, то он заменяется на URL этого раздела
     *  Если $request_uri это пустая строка или содержит только символы "/",
     *  откроется домашняя страница, или, если таковой не задано в настройках,
     *  будет взят URL первого подраздела из раздела с id=1 (Главное меню)
     */
    function recognizeUrl ($request_uri = '') {

        global $system, $CONF;

        if ($request_uri >= 1) {
            //если $request_uri это id раздела
            $paths = view::attr( '_url', 'id='.$request_uri );

        } else {
            //если $request_uri это uri путь

            //ищем REQUEST_URI, если неуказан $request_uri
            if ($request_uri == '') {
                $request_uri = $_SERVER['REQUEST_URI'];
            }

            // Отделяем от адреса параметры страницы и запоминаем их
            $paths = explode('?', $request_uri);
            $paths = explode(':', $paths[0] );

            view::debug_point($paths, 'parts===');

            foreach ($paths as $key => $value) {
                if ($key > 0) {
                    $param_page = explode('-',$value);
                    if (count($param_page) == 2) {
                        $system['urlParam'][$param_page[0]]=$param_page[1];
                    }
                }
            }

            // Проверяем адрес, если ничего не введено ищем домашнюю страницу
            if ($paths[0] == '/') {
                //грузим домашнюю страницу

                if ($system['homePage'] > 0) {
                    modules_structure_view::newLevel($system['homePage']);
                    // TODO: нужно проверять существование такой домашней страницы и выводить ошибку (возможно при инициализации)

                } else {
                    header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found');
                    header('Status: 404 Not Found');
                    exit;
                }
                return true;

            } else {
                //страница по указоному пути
                $paths = $paths[0];
            }
        }

        view::debug_point($paths, 'URL= '.$request_uri, 0);

        if (empty($paths)) {
            header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found');
            header('Status: 404 Not Found');
            exit;
        }

        $pathsArr = explode('/', substr($paths, 1));
        foreach ($pathsArr as $key => $value) if ($value == '') {
            header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found');
            header('Status: 404 Not Found');
            exit;

            unset($pathsArr[$key]);
        }

        if (modules_structure_url::buildingStructure($pathsArr) === false) {
            header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found');
            header('Status: 404 Not Found');
            exit;
        }

        /*
        modules_structure_url::absctractParent();
        if( $system['section'][ $system['structure']['section'][0]['level'] ] == 'error' ){
            
            $iderror = modules_settings_sys::get('errorPage');
            
            if( $iderror ){
                
                modules_structure_url::recognizeUrl($iderror);
            }
        }
        */
    }


    /*
     *  Разбирает масив имён дерева страниц $paths
     *  и строит массив $system['structure']
     */
    function buildingStructure ($paths) {

        global $system;
        $system__rootSections =& $system['rootSections'];

        //ищем первый уровень
        if (($sectionName = array_shift($paths)) === NULL) {
            //не было найдено раздела первого уровня
            //<<error
            echo 'eh1';
            return false;
        }

        $rootSectionsStr = implode(',', array_keys($system__rootSections));
        $result = sys::sql('
            SELECT `id`, `parent_id`, `title`, `base_class`
            FROM `prefix_Sections`
            WHERE
              `name` = "'.$sectionName.'"
              AND `id` NOT IN ('.$rootSectionsStr.') AND `parent_id` IN ('.$rootSectionsStr.')
            LIMIT 1 ;
        ', 1);

        if (isset($result[0])) {
            //сохраняются данные атрибутов в системном массиве
            $system['section'][$result[0]['id']] = $result[0];
            $system['section'][$result[0]['id']]['name'] &= $sectionName;

            modules_structure_view::newLevel($result[0]['id']);

        } else {
            //не было найдено раздела первого уровня

            if (count($paths) > 0) {
                $result = sys::sql('
                    SELECT 1
                    FROM `prefix_Sections`
                    WHERE
                      `name` = "'.$sectionName.'" AND `id` IN ('.$rootSectionsStr.')
                    LIMIT 1 ;
                ', 1);
                if (isset($result[0])) {
                    /* если существует корневой раздел совпадающий с разделом первого уровня
                     * такое можно исправить автоматически редиректом HTTP 301
                     */
                    // TODO: убрать прямую работу с $_SERVER['HTTP_HOST']
                    header ($_SERVER['SERVER_PROTOCOL'] . ' 301 Moved Permanently');
                    header ('Location: http://' . $_SERVER['HTTP_HOST'] . '/' . implode('/', $paths));
                    die;
                }
            }

            //<<error
            echo 'eh2';
            return false;
        }

        if (count($paths) === 0) {
            // все разделы найдены
            return true;
        }

        //ищем все остальные разделы
        $result = sys::sql('
			SELECT `id`, `parent_id`, `name`
			FROM `prefix_Sections`
			WHERE
			  `name` IN ("' . implode('","', $paths) . '")
			  AND `id` NOT IN ('.$rootSectionsStr.') AND `parent_id` NOT IN ('.$rootSectionsStr.')
			;
		',1);

        if (count($result) === 0) {
            //не было найдено остальных разделов
            //<<error
            return false;
        }

        foreach ($paths as $sectionName) {

            $temp_a = count($result) - 1;

            do {
                if ($result[$temp_a]['parent_id'] == $system['level'][modules_structure_view::getLevelLast()]['section']['id']) {
                    if ($result[$temp_a]['name'] == $sectionName) {
                        modules_structure_view::newLevel( $result[$temp_a]['id'] );
                        unset($result[$temp_a]);
                        break;
                    }
                    unset($result[$temp_a]);
                }
            } while (--$temp_a >= 0);

            if ($temp_a == -1) {
                //не было найдено очередного раздела
                //<<error
                return false;
            }
        }

        return true;
    }


    /*
     *  Производит изменения в массиве $system['structure']
     *  в с учетом атрибутов __abstractParent у разделов в масиве
     */
    function absctractParent() {

        global $system;

        //загружаем атрибут __abstractParent
        $abstractParent = view::attr('__abstractParent');

        //разбираем строку __abstractParent и вычисляем новый parent_id
        if (strlen($abstractParent) > 0) {
            $abstractParent = explode('/',$abstractParent);

            while (!($newAbstractParent>0) and list(,$value) = each($abstractParent)) {

                $conditions = explode(':', $value);

                if (isset($conditions[1])) {

                    $cond_array = explode(';',$conditions[1]);

                    $uslovie = true;
                    while ($uslovie and list(,$cond) = each($cond_array)) {

                        preg_match('/^[a-zA-Z]+/', $cond, $attr_name);
                        $cond_param = substr( $cond, strlen($attr_name[0]) );

                        $result = sys::sql('
							SELECT `value`
							FROM `prefix_ClassSections`
							WHERE `parent_id`="'.
                            $system['structure']['section'][ count($system['structure']['section'])-1 ]['base_class'].'"
								and `type`="attr"
								and `name`="'.$attr_name[0].'"
							LIMIT 1 ;
						',1);

                        eval('$uslovie = components_'.$result[0]['value'].'::condition("'.$attr_name[0].'",'.
                            $system['structure']['section'][ count($system['structure']['section'])-1 ]['id'].',"'.
                            $cond_param.'");');

                    }

                    if ($uslovie) $newAbstractParent = $conditions[0];

                } else {
                    $newAbstractParent = $conditions[0];
                }
            }

            if ($newAbstractParent > 0) {

                $oldsection = $system['structure']['section'][ count($system['structure']['section'])-1 ];

                $id = $newAbstractParent;

                do {
                    $result = sys::sql('
						SELECT `name`, `parent_id`
						FROM `prefix_Sections`
						WHERE `id`="'.$id.'";
					',1);
                    $paths[]=$result[0]['name'];
                    $id = $result[0]['parent_id'];
                } while ($result[0]['parent_id'] != '0' and $result[0]['parent_id'] != '1');

                krsort($paths);

                if (count($paths) > 0 and $paths[0] != '') {
                    unset($system['structure']['section']);
                    modules_structure_view::setSystemPagesData();
                    modules_structure_url::buildingStructure($paths);
                    modules_structure_view::setSystemPagesData($oldsection);
                }
            }
        }
    }

}


// init
global $system;

// TODO: возможно лучше будет убрать id=1 из корневого раздела по умолчанию
$system['rootSections'] = array(0 => true, 1 => true);
$rootSections = modules_settings_sys::get('rootSections');
if ($rootSections) {
    $rootSections = explode(',', $rootSections);
    foreach ($rootSections as $rootSectionId) {
      if ($rootSectionId > 1) $system['rootSections'][$rootSectionId] = true;
    }
}

if (!(($system['homePage'] = modules_settings_sys::get('homePage') - 0) >= 1)) {

    /* если указано неверное значение домашней страницы
     * ищем первую подходящую страницу (с приоритетом для корневых разделов)
     */
    $result = sys::sql('
        SELECT `id`
        FROM `prefix_Sections`
        ORDER BY `id` NOT IN ('.$rootSectionsStr.'), `pos`
        LIMIT 1
    ;', 1);

    $system['homePage'] = isset($result[0]) ? $result[0]['id'] : 0;
}
$system['rootSections'][$system['homePage']] = true;
