<?php

/* 
 * Модуль Structure - структура
 */
class modules_structure_view {

	/*
     * Новый Базовый класс (BaseClass)
     * 
     * Если запрошеный Базовый Класс еще не создавался,
     * загружаются его параметры
     */
    static function newBaseClass($idBaseClass) {

		global $system;
		
        if (empty($system['classSection'][$idBaseClass])) {
            // Если небыл загружен ClassSection, загружаем
			
            $result = sys::sql('
        		SELECT CONCAT( `type`, ".", `name` ) attr, `value`
        		FROM `prefix_ClassSections`
        		WHERE 
					`parent_id`="'.$idBaseClass.'"
        			and `type`="attr"
        		;
        	', 1);
			
            foreach ($result as $value) {
                $system['classSection'][$idBaseClass][$value['attr']] = $value['value'];
            }
        }
    }
	
    /*
     * Новый раздел (Section)
     *
     * Если запрошеный раздел еще не создавался,
     * он добавляется в системный маcсив
     * и загружаются его основные атрибуты.
     * Дополнительно вызывается и загрузка базового класса,
     * к которому принадлежит этот раздел.
     */
    static function newSection ($idSection = 1) {

        global $system;

        if (empty($system['section'][$idSection])) {
            // Если раздел еще не загружался

            $result = sys::sql('
            	SELECT `parent_id`, `name`, `title`, `base_class`
            	FROM `prefix_Sections`
            	WHERE `id`='.$idSection.'
            	LIMIT 1 ;
            ', 1);

            // Сохраняются данные атрибутов в системном массиве
            $system['section'][$idSection] = $result[0];
            $system['section'][$idSection]['id'] = $idSection;

            // Загрузка базового класса
            modules_structure_view::newBaseClass($system['section'][$idSection]['base_class']);
        }

        return $idSection;
    }
	
    /*
     * Новый уровень разделов (Level)
     * 
     * Добавляется новый уровень разделов, для определённого раздела.
     * Дополнительно вызывается и загрузка этого раздела.
     */
    static function newLevel ($idSection) {

        global $system;
		
		// Вычисление номера для нового уровня
		$number = count($system['level']);
		
		// Добавление нового уровеня
        $system['level'][$number]['number'] = $number;
		$system['level'][$number]['section'] =& $system['section'][modules_structure_view::newSection($idSection)];
		
        return $number;
    }
	
    /*
     * Удаление последнего уровеня разделов
     */
    static function removeLevel () {

        global $system;
        //поиск и удаление уровня разделов
        unset ($system['level'][modules_structure_view::getLevelLast()]);
    }

    /*
     * Последний уровень
     * 
     * Возвращает номер последнего уровeня разделов,
     * т.е. тот который в данный момент используется (активный)
     */
    static function getLevelLast () {
        global $system;
        return count($system['level'])-1;
    }

}

