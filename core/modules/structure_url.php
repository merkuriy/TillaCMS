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
    static function recognizeUrl ($request_uri = '') {

        global $system, $CONF;

        if ($request_uri >= 1) {
            //если $request_uri это id раздела
            $paths = view::attr( '_url', 'id='.$request_uri );

        } else {
            // если $request_uri это uri путь

            // если $request_uri не указан, то возьмём найдём его из REQUEST_URI,
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
                        $system['urlParam'][$param_page[0]] = $param_page[1];
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
                    show_404('ehr1');
                }
                return true;

            } else {
                //страница по указоному пути
                $paths = $paths[0];
            }
        }

        view::debug_point($paths, 'URL= '.$request_uri, 0);

        if (empty($paths)) {
            show_404('ehr2');
        }

//        $CONF['app']['uri']['postfix']
        if (empty($CONF['app']['uri']['postfix'])) {
            $CONF['app']['uri']['postfix'] = '';
            /*
             * postfix=""
             * Если обязательные постфиксы URI не используются, то
             * будет проделана проверка на завершающий слеш (ниже при проверке лишних слешей)
             */
            $needUrlFix = false; // $needUrlFix = substr($paths, -1) == '/';
            $paths = substr($paths, 1);

        } else {
            /*
             * postfix="..."
             * Если обязательные постфиксы URI используются, то
             * производим проверку на присутсвие постфикса.
             * Если постфикс присутвует, то удаляем его из $paths.
             * Если постфикс отсутвтсвует, то назначаем исправление текущего URI (needUrlFix)
             */
            $postfixPos = 0 - strlen($CONF['app']['uri']['postfix']);
            if ($needUrlFix = substr($paths, $postfixPos) == $CONF['app']['uri']['postfix']) {
                $paths = substr($paths, 1);
            } else {
                $paths = substr($paths, 1, $postfixPos);
            }
        }

        $pathsArr = explode('/', $paths);
        foreach ($pathsArr as $key => $value) if ($value == '') {
            $needUrlFix = true;
            unset($pathsArr[$key]);
        }

        if (modules_structure_url::buildingStructure($pathsArr) === false) {
            show_404('ehr3');
        }

        if ($needUrlFix) {
            redirect('/' . implode('/', $pathsArr) . $CONF['app']['uri']['postfix']); // urlfix=/
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
    static function buildingStructure ($paths) {

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
            $system['section'][$result[0]['id']]['name'] =& $sectionName;

            modules_structure_view::newBaseClass( $result[0]['base_class'] );
            modules_structure_view::newLevel(     $result[0]['id'] );

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
                     * т. е. отбрасываем из адреса название корневого раздела
                     */
                    redirect('/' . implode('/', $paths) . '/'); // urlfix=/
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
            foreach ($result as $key => &$section) {
                if ($section['parent_id'] == $system['level'][modules_structure_view::getLevelLast()]['section']['id']) {
                    if ($section['name'] == $sectionName) {
                        modules_structure_view::newBaseClass( $section['base_class'] );
                        modules_structure_view::newLevel(     $section['id'] );
                        unset($result[$key]);
                        continue 2;
                    }
                    unset($result[$key]);
                }
            }

            //не было найдено очередного раздела
            //<<error
            return false;
        }

        return true;
    }


    /*
     *  Производит изменения в массиве $system['structure']
     *  в с учетом атрибутов __abstractParent у разделов в масиве
     */
    static function absctractParent() {

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
