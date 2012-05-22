<?php

/**
 * Модуль структуры - attr
 * Работа с атрибутами через компоненты
 * и с динамическими атрибутами
 */
class modules_structure_attr
{

    /*
     * получить атрибут раздела
     */
    function getAttributeSection($attrName = '', $idSection = '')
    {
        global $system;
		
		
        //узнаём метод поиска Section, атрибут которого запрошен
        if ($idSection == '')
            $idSection = Array('level', '-0');
        else{
			$idSection = explode('=', $idSection);
			
			if( empty($idSection[1]) ){
				//если указан только номер
				$idSection[1]=$idSection[0];
				$idSection[0]='level';
			}
		}

		
        //поиск id Section по указанному методу

        $idSection_int = (int)$idSection[1];


        if ($idSection[0] == 'id')
        {
            //по структуре id

            if ($idSection[1][0] == '-')
            {
                //от верхнего вниз

                $idSection = $system['level'][modules_structure_view::getLevelLast()]['section']['id'];

                while ($idSection_int <= -1)
                {
                    $idSection = $system['section'][modules_structure_view::newSection($idSection)]['parent_id'];
                    $idSection_int++;
                }

            }
			elseif ($idSection[1][0] == '+')
            {
                //от нижнего вверх

                $idSection = $system['level'][modules_structure_view::getLevelLast()]['section']['id'];
				
                //строим весь путь активных разделов по ид
                do {
                    $arr_id[] = modules_structure_view::newSection($idSection);
                    $idSection = $system['section'][$idSection]['parent_id'];
                } while ($idSection > 1);

                $idSection_int = count($arr_id)-$idSection_int-1;

                if ($idSection_int < 0){
                    return false; //<<error
                }

                $idSection = $arr_id[$idSection_int];
				print_r($idSection_int);
            } 
			else
            {
                //точное указание id Section

                $idSection = modules_structure_view::newSection($idSection_int);
				
                if (!($idSection > 0))
                {
                    return false; //<<error
                }

            }

        }
		else
        {
            //по структуре tamplates

            if ($idSection[1][0] == '-')
            {
                //от верхнего вниз

                $idSection = modules_structure_view::getLevelLast()+$idSection_int;

                if ($idSection < 0){
                    return false; //<<error
                }
                
                $idSection = $system['level'][$idSection]['section']['id'];
                

            }elseif ($idSection[1][0] == '+'){
            	
				$idSection = $system['level'][modules_structure_view::getLevelLast()]['section']['id'];
				
				//от верхнего еще выше, т.е. загружаем загружаем новые разделы
				if( $idSection_int==1 ){
					
					$result = sys::sql('
		            	SELECT `id`, `parent_id`, `name`, `title`, `base_class`
		            	FROM `prefix_Sections`
		            	WHERE `parent_id`="'.$idSection.'"
		            	LIMIT 1 ;
		            ', 1);
					
					$idSection = $result[0]['id'];
					
					//сохраняются данные атрибутов в системном массиве
		            $system['section'][ $idSection ] = $result[0];
					
		            //работа с базовым классом
		            modules_structure_view::newBaseClass($system['section'][$idSection]['base_class']);
					
				}
				
				
			}
			else
            {
                //от нижнего вверх (т.е. точное указание уровня)

                if ( empty($system['level'][$idSection_int])){
                    return false; //<<error
                }

                $idSection = $system['level'][$idSection_int]['section']['id'];

            }

        }
		
		
		
		
        //если запрошеного атрибута нет в кеше, пробуем загрузить его
        if ( empty($system['section'][$idSection][$attrName]))
        {

            if ($attrName[0] == '_')
            {
                //если запрошен динамический атрибут
                $system['section'][$idSection][$attrName] = 
					modules_structure_attr::getAttributeDynamic($attrName, $idSection);
            }
			else
            {
                //если запрошен атрибут, который пренадлежит компоненту

				
				//отделяем от имени атрибута, дополнительный параметр атрибута
				$attrNameParam = explode('.', $attrName, 2);
				
                if ( 
					empty($system['classSection'][
						$system['section'][$idSection]['base_class'] 
					][ 'attr.'.$attrNameParam[0] ])
				){
					view::debug_error('Не найден атрибут ('.$attrNameParam[0].') в классе ('.$system['section'][$idSection]['base_class'].')');
                    return false; //<<error
                }
				

                //запрашиваем значение атрибута у компонента
                eval (
                	'$system["section"]['.$idSection.']["'.$attrName.'"] = components_'.
                	$system['classSection'][ $system['section'][$idSection]['base_class'] ]['attr.'.$attrNameParam[0] ].
                	'::view("'.$attrNameParam[0].'",'.$idSection.',"'.$attrNameParam[1].'");'
                );
				
				

            }

        }
		
        return $system['section'][$idSection][$attrName];


    }





    /*
     * получить динамический атрибут
     */
    function getAttributeDynamic($attrName = '', $idSection = '')
    {

        global $system;

        $result = '>>getAttributeDynamic( attrName='.$attrName.', idSection='.$idSection.' )<<';

        if ($attrName == '_url')
        {

            $redirect_url = view::attr('redirect_url', 'id='.$idSection);

            if ( $redirect_url && strlen($redirect_url)>=1 )
            {
                $result = $redirect_url;
            }
			else
            {
                $result = '';

                do
                {
                    $result = '/'.view::attr('name', 'id='.$idSection).$result;

                    $idSection = & $system['section'][$idSection]['parent_id'];

                } while ($idSection >= 2);

            }

        }
		elseif( $attrName == '_countPart' )
		{
			if(
				isset($system['tplLevel'][ modules_structure_tpl::getTplLevelLast() ]['countChild']) &&
				$system['tplLevel'][ modules_structure_tpl::getTplLevelLast() ]['countChild']>=1 &&
				isset($system['tplLevel'][ modules_structure_tpl::getTplLevelLast() ]['limitChild']) &&
				$system['tplLevel'][ modules_structure_tpl::getTplLevelLast() ]['limitChild']>=1
			 ){
				return ceil(
					$system['tplLevel'][ modules_structure_tpl::getTplLevelLast() ]['countChild']/
					$system['tplLevel'][ modules_structure_tpl::getTplLevelLast() ]['limitChild']
				);
			}
			
			
		}
		elseif( $attrName == '_part' )
		{
			
			if(
				isset($system['tplLevel'][ modules_structure_tpl::getTplLevelLast() ]['part']) &&
				$system['tplLevel'][ modules_structure_tpl::getTplLevelLast() ]['part']>=1
			){
				return $system['tplLevel'][ modules_structure_tpl::getTplLevelLast() ]['part'];
			}
			
		}

        return $result;

    }



}
?>
