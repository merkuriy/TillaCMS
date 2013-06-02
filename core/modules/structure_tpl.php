<?php

/*
 * Модуль Structure - структура
 * Парсер шаблонов
 */
class modules_structure_tpl {

    //парсит шаблон, определённого типа
    function tpl ($tplType = '', $tplName = '', $idSection = '', $tplParam = '') {

        global $system;

        echo '<div class="debug_sdvig">';

        view::debug_point($system, 'tpl.'.$tplType);


        if ($tplType == 'section') {
            //--------------------------------------------------------------------------------------
            // tplType - SECTION

            modules_structure_tpl::newTplLevel($tplType, $tplParam);

            if (!($id_section >= 1) and (
                $system['tplLevel'][modules_structure_tpl::getTplLevelLast() - 1]['tplType'] == 'table' or
                    $system['tplLevel'][modules_structure_tpl::getTplLevelLast() - 1]['tplType'] == 'line'
            )) {
                //--------------------------------------------------------------------------------------
                // tplType - SECTION-> из TABLE

                //idSection и ActiveLevel
                $system['tplLevel'][modules_structure_tpl::getTplLevelLast()]['__activity'] = false;

                //tplName
                if ($tplName == '') {
                    $system['tplLevel'][ modules_structure_tpl::getTplLevelLast() ]['tplName'] =&
                        $system['tplLevel'][ modules_structure_tpl::getTplLevelLast()-1 ]['tplName'];
                } else {
                    $system['tplLevel'][ modules_structure_tpl::getTplLevelLast() ]['tplName'] = $tplName;
                }

                //подгружаем дочерние элементы
                $tplLevel =& modules_structure_sqlFilter::loadChild();

                $content = '';

                foreach ($tplLevel['child'] as $value) {

                    //level и ActiveLevel
                    $system['tplLevel'][ modules_structure_tpl::getTplLevelLast() ]['level'] =&
                        $system['level'][ modules_structure_view::newLevel( $value['id'] ) ];

                    // вычисляем tpl postfix name
                    // проверить, активен раздел или нет
                    $currentId = $system['level'][modules_structure_view::getLevelLast()]['section']['id'];
                    $tplLevelLast =& $system['tplLevel'][modules_structure_tpl::getTplLevelLast()];
                    end($system['sectionActivity']);
                    if (key($system['sectionActivity']) == $currentId) {
                        $tplLevelLast['tplPostfix'] = array('current', 'active');
                    } elseif (isset($system['sectionActivity'][$currentId])) {
                        $tplLevelLast['tplPostfix'] = array('active');
                    } else {
                        $tplLevelLast['tplPostfix'] = array();
                    }

                    $content .= modules_structure_tpl::pre_parseTemplate();

                    unset($system['tplLevel'][ modules_structure_tpl::getTplLevelLast() ]['level']);
                    modules_structure_view::removeLevel();
                }

                //removeTplLevel
                unset( $system['tplLevel'][ modules_structure_tpl::getTplLevelLast() ] );

            } else {
                //--------------------------------------------------------------------------------------
                // tplType - SECTION-> самостоятельный

                //level и ActiveLevel
                if ($idSection >= 1 &&
                    $idSection != $system['level'][ modules_structure_view::getLevelLast() ]['section']['id']
                ) {
                    $system['tplLevel'][ modules_structure_tpl::getTplLevelLast() ]['level'] =
						&$system['level'][ modules_structure_view::newLevel( $idSection ) ];

                    $system['tplLevel'][ modules_structure_tpl::getTplLevelLast() ]['__activity'] = false;
                } else {
                    $system['tplLevel'][ modules_structure_tpl::getTplLevelLast() ]['level'] =
						&$system['tplLevel'][ modules_structure_tpl::getTplLevelLast()-1 ]['level'];

                    $system['tplLevel'][ modules_structure_tpl::getTplLevelLast() ]['__activity'] =
						&$system['tplLevel'][ modules_structure_tpl::getTplLevelLast()-1 ]['__activity'];
                }

                //tplName
                if ($tplName == '') {
                    if (modules_structure_tpl::getTplLevelLast() == 1) {
                        //шаблон запущен сразу из шаблона типа PAGE

                        if (strlen($system['urlParam']['tpl_section']) > 0) {
                            //если в УРЛе страницы задан tpl_section

                            $system['tplLevel'][
                            modules_structure_tpl::getTplLevelLast()
                            ]['tplName'] = $system['urlParam']['tpl_section'];
                        } else {

                            if (view::attr('tpl_section') == '') {
                                //если в атрибутах не задан tpl_section

                                $system['tplLevel'][
                                modules_structure_tpl::getTplLevelLast()
                                ]['tplName'] = 'default';
                            } else {
                                $system['tplLevel'][
                                modules_structure_tpl::getTplLevelLast()
                                ]['tplName'] = view::attr('tpl_section');
                            }
                        }

                    } else {
                        $system['tplLevel'][ modules_structure_tpl::getTplLevelLast() ]['tplName'] =
							&$system['tplLevel'][ modules_structure_tpl::getTplLevelLast()-1 ]['tplName'];
                    }
                } else {
                    $system['tplLevel'][ modules_structure_tpl::getTplLevelLast() ]['tplName'] = $tplName;
                }

                $content = modules_structure_tpl::pre_parseTemplate();

                modules_structure_tpl::removeTplLevel();
            }


        } elseif ($tplType == 'line') {
            //--------------------------------------------------------------------------------------
            // tplType - LINE

            if ($system['tplLevel'][ modules_structure_tpl::getTplLevelLast() ]['tplType'] != 'table') {
                //шаблон типа LINE может вызыватся только из шаблона типа TABLE
                return false; //<<error
            }

            modules_structure_tpl::setNewTplLevel($tplType, $tplParam);

            //level и ActiveLevel
            $system['tplLevel'][ modules_structure_tpl::getTplLevelLast() ]['level'] =
				&$system['tplLevel'][ modules_structure_tpl::getTplLevelLast()-1 ]['level'];
            $system['tplLevel'][ modules_structure_tpl::getTplLevelLast() ]['__activity'] =
				&$system['tplLevel'][ modules_structure_tpl::getTplLevelLast()-1 ]['__activity'];

            //tplName
            if ($tplName == '') {
                $system['tplLevel'][ modules_structure_tpl::getTplLevelLast() ]['tplName'] =
					&$system['tplLevel'][ modules_structure_tpl::getTplLevelLast()-1 ]['tplName'];

            } else {
                $system['tplLevel'][ modules_structure_tpl::getTplLevelLast() ]['tplName'] = $tplName;
            }

            $content = modules_structure_tpl::pre_parseTemplate();

            modules_structure_tpl::removeTplLevel();


        } elseif ($tplType == 'table') {
            //--------------------------------------------------------------------------------------
            // tplType - TABLE

            modules_structure_tpl::newTplLevel($tplType, $tplParam);

            //level и ActiveLevel
            if ($idSection>=1 &&
                $idSection != $system['level'][ modules_structure_view::getLevelLast() ]['section']['id']
            ) {
                $system['tplLevel'][ modules_structure_tpl::getTplLevelLast() ]['level'] =
					&$system['level'][ modules_structure_view::newLevel( $idSection ) ];
                $system['tplLevel'][ modules_structure_tpl::getTplLevelLast() ]['__activity'] = false;

            } else {
                $system['tplLevel'][ modules_structure_tpl::getTplLevelLast() ]['level'] =
					&$system['tplLevel'][ modules_structure_tpl::getTplLevelLast()-1 ]['level'];
                $system['tplLevel'][ modules_structure_tpl::getTplLevelLast() ]['__activity'] =
					&$system['tplLevel'][ modules_structure_tpl::getTplLevelLast()-1 ]['__activity'];
            }


            if (modules_structure_sqlFilter::countChild() < 1) {
                // в данном разделе нет дочерних элементов
                // не выводим шаблон
                modules_structure_tpl::removeTplLevel();

                echo '</div>'; //закрываем точку дебага
                return false; //<<error
            }

            //tplName
            if ($tplName == '') {
                $system['tplLevel'][ modules_structure_tpl::getTplLevelLast() ]['tplName'] =
					&$system['tplLevel'][ modules_structure_tpl::getTplLevelLast()-1 ]['tplName'];
            } else {
                $system['tplLevel'][ modules_structure_tpl::getTplLevelLast() ]['tplName'] = $tplName;
            }

            view::debug_point($system['tplLevel'], 'tplType=TABLE, до pre_parseTemplate()', 1);

            $content = modules_structure_tpl::pre_parseTemplate();

            modules_structure_tpl::removeTplLevel();


        } elseif ($tplType == 'page') {
            //--------------------------------------------------------------------------------------
            // tplType - PAGE

            view::debug_point($system, '>>>', 1);

            if (modules_structure_tpl::getTplLevelLast() >= 0) {
                //шаблон типа Page должен быть первым
                return false; //<<error
            }

            modules_structure_tpl::newTplLevel($tplType, $tplParam);

            //расчёт активных разделов
            foreach ($system['level'] as $value) {
                $system['sectionActivity'][ $value['section']['id'] ] = true;
            }

            view::debug_point($system, '>>>->>>', 1);

            //level и ActiveLevel
            $system['tplLevel'][modules_structure_tpl::getTplLevelLast()]['level'] =&
                $system['level'][modules_structure_view::getLevelLast()];
            $system['tplLevel'][modules_structure_tpl::getTplLevelLast()]['__activity'] = true;

            //tplName
            if ($tplName == '') {
                //если при вызове шаблона явно не задан tplName

                if ($system['urlParam']['tpl_page'] == '') {
                    //если в УРЛе страницы не задан tpl_page

                    if (view::attr('tpl_page') == '') {
                        //если в атрибутах не задан tpl_page
                        $system['tplLevel'][
                        modules_structure_tpl::getTplLevelLast()
                        ]['tplName'] = 'default';

                    } else {
                        $system['tplLevel'][
                        modules_structure_tpl::getTplLevelLast()
                        ]['tplName'] = view::attr('tpl_page');
                    }

                } else {
                    $system['tplLevel'][
                    modules_structure_tpl::getTplLevelLast()
                    ]['tplName'] = $system['urlParam']['tpl_page'];
                }

            } else {
                $system['tplLevel'][ modules_structure_tpl::getTplLevelLast() ]['tplName'] = $tplName;
            }

            $content = modules_structure_tpl::pre_parseTemplate();

            modules_structure_tpl::removeTplLevel();

        } else {
            //--------------------------------------------------------------------------------------
            // tplType - FIELD

            if (strlen($tplName) >= 1) {
                $content = modules_structure_tpl::readTemplate($tplType, $tplName);
            }
        }

        echo '</div>';
        return $content;
    }


    /*
     * Новый уровень шаблонов (tplLevel)
     *
     * Устанавлеваем новый уровень в шаблонах
     * и сохраняет все доступные для уровня данные
     */
    function newTplLevel ($tplType='', $tplParam='') {

        global $system;

        $tplParams = array();
        if ($tplParam != '') {
            $params = explode(',', $tplParam);
            foreach ($params as $value) {
                $arr_params = explode('=', $value);
                $tplParams[ trim($arr_params[0]) ][] = trim($arr_params[1]);
            }
        }

        $system['tplLevel'][ count( $system['tplLevel'] ) ] = array(
            'tplType'	=> $tplType,
            'tplParam'	=> $tplParams
        );

    }


    /*
     * возвращает последний номер уровня в шаблонах,
     * т.е. тот который в данный момент используется (активный)
     */
    function getTplLevelLast() {

        global $system;
        return count($system['tplLevel']) - 1;
    }


    /*
     * удаляет используемый активный уровень
     */
    function removeTplLevel() {

        global $system;

        if ($system['level'][ modules_structure_view::getLevelLast() ]['section']['id'] !=
            $system['tplLevel'][ modules_structure_tpl::getTplLevelLast()-1 ]['level']['section']['id']
        ) {
            //если последний уровень section->Level был создан активным TplLevel, удаляем его
            modules_structure_view::removeLevel();
        }

        unset($system['tplLevel'][ modules_structure_tpl::getTplLevelLast() ]);
    }


    /*
     * Предварительная подготовка к парсингу стандартных типов шаблонов
     */
    function pre_parseTemplate() {

        global $system;

        //проверяем на рекурсивность шаблона
        if ($system['tplLevel'][ modules_structure_tpl::getTplLevelLast() ]['tplType'] ==
            $system['tplLevel'][ modules_structure_tpl::getTplLevelLast()-1 ]['tplType'] and
            $system['tplLevel'][ modules_structure_tpl::getTplLevelLast() ]['tplName'] ==
                $system['tplLevel'][ modules_structure_tpl::getTplLevelLast()-1 ]['tplName'] and
                $system['tplLevel'][ modules_structure_tpl::getTplLevelLast() ]['level']['number'] ==
                    $system['tplLevel'][ modules_structure_tpl::getTplLevelLast()-1 ]['level']['number']
        ) {
            //<<error
            //выводим предупреждение и продолжаем работу
            view::debug_error('рекурсивный вызов шаблона');
        }

        return modules_structure_tpl::parseTemplate();
    }


    function readTemplate ($tplType = '', $tplName = '', $tplPostfix = array()) {

        global $system;

        $system['tplLevel'][ count($system['tplLevel']) ] = array(
            'tplType' => $tplType,
            'tplName' => $tplName,
            'tplPostfix' => $tplPostfix,
            'level' => &$system['tplLevel'][ count( $system['tplLevel'] )-1 ]['level']
        );

        $tpl = modules_structure_tpl::parseTemplate();

        unset($system['tplLevel'][ modules_structure_tpl::getTplLevelLast() ]);

        return $tpl;
    }


    /*
     * Читает файл шаблона
     *
     * Ищем файл шаблона с постфиксом, если постфикс известен
     * если нет файла с постфиксом ищем файл шаблона без постфикса
     */
    function readTemplateFile() {

        $TimeStart=gettimeofday();
        global $system, $CONF;

        $CONF['readTplFileCount']++;

        $tplLevelLast = &$system['tplLevel'][ modules_structure_tpl::getTplLevelLast() ];
        $tplFile = $tplLevelLast['tplName'].'.'.$tplLevelLast['tplType'];

        if ($isPostfix = count($tplLevelLast['tplPostfix']) > 0) {
            $tplFile .= '.'.current($tplLevelLast['tplPostfix']);
            unset($tplLevelLast['tplPostfix'][key($tplLevelLast['tplPostfix'])]);
        }

        $tplCacheFile = '../data/tplCache/'.str_replace( '/', '~', $tplFile).'.php';
        $tplFileDir = '../templates/'.$tplFile.'.tpl';

        if (empty($system['tplFile'][$tplFile])) {
            //если файл шаблона еще не прочитывали
            if (file_exists($tplFileDir)) {

                if (file_exists($tplCacheFile) and filemtime($tplFileDir) < filemtime($tplCacheFile)) {
                    //если найден актульный кеш шаблона
                    $system['tplFile'][$tplFile] = create_function( '',
                        'return \''.file_get_contents( $tplCacheFile ).'\';'
                    );

                } else {
                    //если кеш шаблона неактуальный или его нет
                    //читаем шаблон и создаём кеш

                    //читаем файл шаблона
                    modules_structure_tpl::strs00(file_get_contents($tplFileDir), $tplTempCache);
                    file_put_contents($tplCacheFile, $tplTempCache);

                    $system['tplFile'][$tplFile] = create_function('', 'return\''.$tplTempCache.'\';');
                    unset($tplCacheFile);
                }

            } else {
                //если такого файла шаблона нет
                if ($isPostfix) {
                    //если нет файла шаблона с постфиксом
                    //пробуем прочитать без постфикса
                    $system['tplFile'][$tplFile] =&
                        $system['tplFile'][modules_structure_tpl::readTemplateFile()];

                } else {
                    //<<error
                    view::debug_error('Не найден шаблон ('.$tplFileDir.')');
                    //Не найден шаблон
                    $system['tplFile'][$tplFile] = '-';
                }
            }
        }

        $TimeEnd = gettimeofday();
        $CONF['readTplFileTime'] +=
            (float)($TimeEnd['sec'] - $TimeStart['sec']) +
                ((float) ($TimeEnd['usec'] - $TimeStart['usec']) / 1000000);

        return $tplFile;
    }


    /*
     * Парсинг шаблона
     */
    function parseTemplate () {

        global $system;
        return $system['tplFile'][ modules_structure_tpl::readTemplateFile() ]();
    }


    /*
     * %A.B(C)% - макрос - %класс.метод(параметры)%
     * {{D}} - экранированная строка - {{экранированная строка}}
     * начальный корневой этап
     *
     * ищем открывающий %
     *
     * возвращает во второй параметр $tplResult:
     * -Array( строка[, макрос[, строка[, макрос[, ...]]]] )
     *
     */
    function strs00 (&$tplText, &$tplResult) {

        $tplTextLength = strlen($tplText);
        $tplTextPos = 0;	//текущий проверяемый символ

        $tplResult = '';

        $temp_pos2=0;

        for (; $tplTextPos < $tplTextLength; $tplTextPos++) {
            if ($tplText[$tplTextPos] == '%') {
                //нашли начало нового макроса

                $temp_arr = modules_structure_tpl::strs01( $tplText, $tplTextLength, $tplTextPos );

                if (count($temp_arr[2]) >= 1) {
                    if ($temp_arr[0] === false) {
                        $tplResult .=
                            str_replace('\'', '\\\'',
                                substr( $tplText, $temp_pos2, $temp_arr[1]-$temp_pos2 ).
                                    $temp_arr[2]
                            );

                    } else {
                        $tplResult .=
                            str_replace('\'', '\\\'',
                                substr( $tplText, $temp_pos2, $temp_arr[0]-$temp_pos2 )
                            ).
                                '\'.'.$temp_arr[1].'(\''.$temp_arr[2].'\').\'';
                    }
                    $temp_pos2 = $tplTextPos+1;

                }

            } elseif ($tplText[$tplTextPos] == '{' && $tplText[$tplTextPos+1] == '{') {
                //нашли начало экранированной строки

                $temp_arr = modules_structure_tpl::stre01($tplText, $tplTextLength, $tplTextPos);

                if (count($temp_arr[1]) >= 1) {
                    $tplResult .= str_replace('\'', '\\\'',
                        substr( $tplText, $temp_pos2, $temp_arr[0]-$temp_pos2 ).$temp_arr[1]
                    );
                    $temp_pos2 = $tplTextPos+2;
                }

                $tplTextPos++;
            }
        }

        $tplResult = preg_replace("/((\()''\.|\.''(\))|(\()''(\))|\.''(\.))/", '$2$3$4$5$6',

            $tplResult = preg_replace('/(^\s|\S\s)\s+(\S|$)/', '$1$2',

                $tplResult.str_replace('\'', '\\\'',
                    substr( $tplText, $temp_pos2 )
                )
            )
        );
    }


    /*
     * после открывающихся фигурных скобок
     * {{
     *
     * ищем D
     *
     * возвращает:
     * -Array( 0=начал.позиция макроса;	1=D )
     */
    function stre01 (&$tplText, &$tplTextLength, &$tplTextPos) {

        $tplTextPos += 2;
        $temp_pos = $tplTextPos; //начальная позиция A или B

        $activePos = 0;

        while ($tplTextPos < $tplTextLength) {

            if ($tplText[$tplTextPos] == '}' && $tplText[$tplTextPos+1] == '}') {
                //нашли конец экранированной строки D

                return array(
                    $temp_pos-2,
                    substr($tplText, $temp_pos, $tplTextPos-$temp_pos)
                );
            }

            $tplTextPos++;
            $activePos = $tplTextPos-$temp_pos;
        }
    }


    /*
     * после открывающего процента
     * %
     *
     * ищем A или B
     *
     * возвращает:
     * -Array( 0=false, 1=начал.позиция C; 2=C ), если был найден не макрос
     * -Array( 0=начал.позиция макроса;	1=A::B;	2=C ), в противном случае
     */
    function strs01 (&$tplText, &$tplTextLength, &$tplTextPos) {

        $tplTextPos++;
        $temp_pos = $tplTextPos; //начальная позиция A или B

        $activePos = 0;

        for (; ($tplTextPos < $tplTextLength and $activePos <= 18 ); $tplTextPos++, $activePos = $tplTextPos - $temp_pos) {
            $str_ord = ord($tplText[$tplTextPos]);

            if (($str_ord >= 65 and $str_ord <= 90) or
                ($str_ord >= 97 and $str_ord <= 122) or
                ($str_ord >= 48 and $str_ord <= 57) or
                $str_ord == 95
            ) {
                //нашли символ из операнда A или B

            } elseif ($activePos >= 3 and $str_ord == 46) {
                //нашли точку
                $temp_arr = modules_structure_tpl::strs02($tplText, $tplTextLength, $tplTextPos);

                if ($temp_arr[0] === false) {
                    return $temp_arr;
                } else {

                    $className = substr($tplText, $temp_pos, $temp_arr[0]-$temp_pos );

                    if (count(explode('_', $className )) > 1) {
                        $className = 'modules_'.$className;
                    }

                    return array(
                        $temp_pos-1,
                        $className.'::'.$temp_arr[1],
                        $temp_arr[2]
                    );
                }

            }elseif ($activePos >= 3 and $str_ord == 40) {
                //нашли открывающую скобку
                $temp_arr = modules_structure_tpl::strs03($tplText, $tplTextLength, $tplTextPos);

                if ($temp_arr[0] === false) {
                    return $temp_arr;
                } else {
                    return array(
                        $temp_pos-1,
                        'view::'.substr($tplText, $temp_pos, $temp_arr[0] - $temp_pos),
                        $temp_arr[1]
                    );
                }

            } else {
                //это не макрос
                $tplTextPos--;
                return array(
                    false,
                    0,
                    array()
                );
            }
        }
    }


    /*
     * после точки
     * %A.
     *
     * ищем B
     *
     * возвращает:
     * -Array( 0=false, 1=начал.позиция C; 1=C ), если был найден не макрос
     * -Array( 0=конечная позиция A; 1=B; 2=C ), в противном случае
     */
    function strs02 (&$tplText, &$tplTextLength, &$tplTextPos) {

        $tplTextPos++;
        $temp_pos = $tplTextPos;  //начальная позиция B

        $activePos = $tplTextPos-$temp_pos;

        for (; ($tplTextPos < $tplTextLength and $activePos <= 18 ); $tplTextPos++, $activePos = $tplTextPos - $temp_pos) {

            $str_ord = ord($tplText[$tplTextPos]);

            if (($str_ord >= 65 and $str_ord <= 90) or
                ($str_ord >= 97 and $str_ord <= 122) or
                ($str_ord >= 48 and $str_ord <= 57)
            ) {
                //нашли символ из операнда B

            } elseif ($activePos >= 3 and $str_ord == 40) {
                //нашли открывающую скобку
                $temp_arr = modules_structure_tpl::strs03($tplText, $tplTextLength, $tplTextPos);

                if ($temp_arr[0] === false) {
                    return $temp_arr;
                } else {
                    return array(
                        $temp_pos-1,
                        substr($tplText, $temp_pos, $temp_arr[0] - $temp_pos),
                        $temp_arr[1]
                    );
                }

            } else {
                //это не макрос
                return array(
                    false,
                    0,
                    array()
                );
            }
        }
    }


    /*
     * после открывающей скобки
     * %A.B( или %A(
     *
     * ищем C
     *
     * возвращает:
     * -Array( 0=false; 1=начал.позиция C; 1=C ), если был найден не макрос
     * -Array( 0=конечная позиция A.B; 1=C ), в противном случае
     */
    function strs03 (&$tplText, &$tplTextLength, &$tplTextPos) {

        $temp_pos = $tplTextPos;	//конечная позиция A.B
        $tplTextPos++;			//начальная позиция C
        $temp_pos2 = $tplTextPos;

        $temp_a = array('');

        for (; $tplTextPos < $tplTextLength; $tplTextPos++) {

            if ($tplText[$tplTextPos] == ')' and $tplText[$tplTextPos+1] == '%') {
                //нашли окончание макроса

                end($temp_a);
                $temp_a[key($temp_a)] = rtrim(
                    $temp_a[key($temp_a)].substr($tplText, $temp_pos2, $tplTextPos-$temp_pos2)
                );

                $tplTextPos++; //конечная позиция макроса

                reset($temp_a);
                $temp_a[0] = rtrim($temp_a[0]);

                $temp_b='';

                do {
                    $temp_b .= preg_replace('/\s*;\s*/', '\',\'',
                        str_replace('\'', '\\\'', current($temp_a))
                    );
                    $temp_a[key($temp_a)]='';

                    //если существует следующий элемент в $temp_a,
                    //то это макрос - добавляем его к последнему параметру
                    if (next($temp_a)) {
                        $temp_b .= current($temp_a);
                        $temp_a[key($temp_a)]='';
                    }

                } while (next($temp_a) !== false);

                return array(
                    $temp_pos,
                    $temp_b
                );

            } elseif ($tplText[$tplTextPos] == '%') {
                //нашли начало нового макроса

                $temp_arr = modules_structure_tpl::strs01($tplText, $tplTextLength, $tplTextPos);

                if (count($temp_arr[2]) >= 1) {
                    end($temp_a);
                    if ($temp_arr[0] === false) {
                        $temp_a[key($temp_a)] .=
                            substr($tplText, $temp_pos2, $temp_arr[1] - $temp_pos2) .
                                $temp_arr[2];

                    } else {
                        $temp_a[key($temp_a)] .=
                            substr($tplText, $temp_pos2, $temp_arr[0]-$temp_pos2);

                        $temp_a[] = '\'.'.$temp_arr[1].'(\''.$temp_arr[2].'\').\'';
                        $temp_a[] = '';
                    }
                    $temp_pos2 = $tplTextPos+1;
                }

            } elseif ($tplText[$tplTextPos] == '{' && $tplText[$tplTextPos+1] == '{') {
                //нашли начало экранированной строки

                $temp_arr = modules_structure_tpl::stre01($tplText, $tplTextLength, $tplTextPos);

                if(count($temp_arr[1]) >= 1) {
                    $temp_a[key($temp_a)] .=
                        substr($tplText, $temp_pos2, $temp_arr[0] - $temp_pos2) .
                            $temp_arr[1];

                    $temp_pos2 = $tplTextPos+2;
                }
                $tplTextPos++;
            }
        }

        return array(
            false,
            $temp_pos+1,
            $temp_a
        );
    }

}