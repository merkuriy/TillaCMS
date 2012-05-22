<?php
		
/* 
 *	Модуль структуры
 */


class modules_structure_view {

	/*
     * Новый Базовый класс (BaseClass)
     * 
     * Если запрошеный Базовый Класс еще не создавался,
     * загружаются его параметры
     */
    function newBaseClass($idBaseClass)
    {
		global $system;
		
        if ( empty($system['classSection'][$idBaseClass]))
        {
            //если небыл загружен ClassSection, загружаем
			
            $result = sys::sql('
        		SELECT CONCAT( `type`, ".", `name` ) attr, `value`
        		FROM `prefix_ClassSections`
        		WHERE 
					`parent_id`="'.$idBaseClass.'"
        			and `type`="attr"
        		;
        	', 1);
			
			//print_r($result);
			
            foreach ($result as $value)
            {
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
     *
     */
    function newSection($idSection = 1)
    {
        global $system;

        if ( empty($system['section'][$idSection]))
        {
            //если раздел еще незагрудался

            $result = sys::sql('
            	SELECT `parent_id`, `name`, `title`, `base_class`
            	FROM `prefix_Sections`
            	WHERE `id`="'.$idSection.'"
            	LIMIT 1 ;
            ', 1);

            //сохраняются данные атрибутов в системном массиве
            $system['section'][$idSection] = $result[0];
            $system['section'][$idSection]['id'] = $idSection;

            //работа с базовым классом
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
    function newLevel($idSection)
    {
        global $system;
		
		
		//вычисление номера для нового уровня
		$number = count($system['level']);
		
		//добавление нового уровеня
        $system['level'][$number]['number'] = $number;
		$system['level'][$number]['section'] = 
			&$system['section'][modules_structure_view::newSection($idSection)];
		
        return $number;

    }



	
    /*
     * Удаление последнего уровеня разделов
     */
    function removeLevel()
    {
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
    function getLevelLast()
    {
        global $system;

        return count($system['level'])-1;

    }





    //==========================================
    // Функция подсчета элементов для статистики
    function getCountCS($id)
    {
        $IDs = explode(';', $id);
        $count = 0;
        foreach ($IDs as $value)
        {
            $result = sys::sql("SELECT `id` FROM `prefix_Sections` WHERE `base_class`='$value'", 0);
            $count += mysql_num_rows($result);
        }
        return $count;
    }


    //==========================================
    // Подсчета комментариев
    function getKomments($id)
    {
        $sql = sys::sql("SELECT
							COUNT(sect.`id`)
						FROM
							`prefix_Sections` sect,
							`prefix_ClassSections` class,
							`prefix_TInteger` integ
						WHERE
							class.`parent_id` = '0' AND
							class.`name` = 'komment' AND
							sect.`base_class` = class.`id` AND
							integ.`name` = 'partID' AND
							integ.`parent_id` = sect.`id` AND
							integ.`data`='$id'
		", 0);

        return mysql_result($sql, 0);
    }
}
?>
