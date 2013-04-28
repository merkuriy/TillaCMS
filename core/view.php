<?php

/*
 * движок
 */

$start_script=gettimeofday();
$CONF['colsql'] = 0;

session_start();
header('Content-type: text/html; charset="utf-8"', true);

include('../config.php');
include('../core/__autoload_class.php');

$CONF['defaultLanguage'] = modules_settings_sys::get('defaultLanguage');



//POST
//=========
if ($_GET['action'] == 'deauth') {
    modules_users_sys::deauth();
}

$result_ajax = '';
$paramItem = 1;

while (isset($_POST['param'.$paramItem])) {

    $macros=explode('.',$_POST['param'.$paramItem]);
    if ($macros[0]=='components'){
        eval('$result_ajax .= '.$macros[0].'_'.$macros[1].'::'.$macros[2].'($_POST,$_FILES);');
    } else {
        $class_name = explode('_',$macros[0]);

        if(empty($class_name[1])) $class_name[1]='view';

        if($class_name[0]=='system' or $class_name[0]==''){
            $className=$class_name[1];
        }else{
            $className='modules_'.$class_name[0].'_'.$class_name[1];
        }

        eval('$result_ajax .= '.$className.'::'.$macros[1].'($_POST);');
    }

    $paramItem++;
}

if ($_POST['result'] == 'ajax' or $_GET['result'] == 'ajax') {
    if ($result_ajax == '') $result_ajax = '+';
    die($result_ajax);
}


ob_start();


if (!file_exists('../data/viewHandler.php') || (include '../data/viewHandler.php') === false) {
    modules_structure_url::recognizeUrl();
    $resultHTML = view::tpl('page');
}


view::debug_point($system['section'], 'конец - sedtion');
view::debug_point($system['tplFile'], 'конец - template');

$debug = ob_get_clean();



$end_script=gettimeofday();
$totaltime1 = (float)($end_script['sec'] - $start_script['sec']) + ((float)($end_script['usec'] - $start_script['usec'])/1000000);


echo $resultHTML;

view::debug_print($debug);



class view {

    static public $data = array();

    function tpl ($tpl_type = '', $tpl_name = '', $id_section = '', $tpl_param = '') {
        return modules_structure_tpl::tpl($tpl_type, $tpl_name, $id_section, $tpl_param);
    }

    function attr ($attr_name = '', $level = '') {
        return modules_structure_attr::getAttributeSection( $attr_name, $level );
    }

    function attrLang ($attr_name = '', $level = '') {

        global $CONF;

        if ($CONF['defaultLanguage'] != $CONF['language'] and
            $result=modules_structure_attr::getAttributeSection($attr_name . '_' . $CONF['language'], $level)
        ) {
            return $result;
        }
        return modules_structure_attr::getAttributeSection($attr_name, $level);
    }

    function attrTable ($attr_name = '', $level = '', $tpl_name = '') {

        $result = '';
        $i = 1;

        while (
            view::attr(
                view::data('attrIndex', str_pad($i, 2, '0', STR_PAD_LEFT)) . '_' . $attr_name, $level
            )
        ) {
            $result .= view::tpl('attrTable', $tpl_name );
            $i++;
        }

        return $result;
    }

    function data ($name, $val = false) {
        if ($val) return view::$data[$name]=$val;
        return view::$data[$name];
    }

    function clearData () {
        view::$data = array();
    }

    function isEmpty ($val, $true = '', $false = '') {

        if ($val == '' or $val == 0) {
            return $false;
        }else{
            return $true;
        }
    }

    function debug_point (&$var, $msg = '', $status = 0) {

        global $debug_point;
        $debug_point++;

        echo '<a class="debug_point debug_point'.$status.'" name="'.$debug_point.'" title="/debug/point'.$debug_point.'.txt">&raquo;&raquo;&raquo;point#'.$debug_point.'      '.$msg.'</a>';

        $dump_var = print_r($var, true);

        $file = fopen('../debug/point'.$debug_point.'.txt', 'w+');
        fwrite($file, $dump_var);
        fclose($file);
    }

    function debug_error ($msg = '') {
        echo '<span class="debug_error">'.$msg.'</span>';
    }

    function debug_print ($bufer) {

        global $totaltime1, $CONF;

        $file = fopen('../debug/main.txt', 'w+');
        fwrite($file, $bufer . '<br/><br/>workTime=' . $totaltime1 . '<br/><br/>sqlQuery=' . $CONF['colsql']);
        fclose($file);
    }

}