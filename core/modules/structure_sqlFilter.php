<?php

/**
 * Модуль структуры - работа с группой разделов
 * 
 * sqlFilter использует пользовательские файлы формирования запроса
 * к базе для выбора группы разделов при выводе дочерних разделов из
 * шаблона типа TABLE
 */
class modules_structure_sqlFilter {

	
	
	
	/*
	 * Возвращает количество дочерних разделов
	 */
	function countChild(){
		
		global $system;
		
		if( $system['tplLevel'][ modules_structure_tpl::getTplLevelLast() ]['tplType']=='table' ){
			$tplLevel = &$system['tplLevel'][ modules_structure_tpl::getTplLevelLast() ];
		}elseif( $system['tplLevel'][ modules_structure_tpl::getTplLevelLast() ]['tplType']=='line' ){
			$tplLevel = &$system['tplLevel'][ modules_structure_tpl::getTplLevelLast()-1 ];
		}else{
			$tplLevel = &$system['tplLevel'][ modules_structure_tpl::getTplLevelLast()-2 ];
		}
		
		
		if( empty( $TplLevel['countChild'] ) ){
			//если количество элементов для данного уровня в шаблонах еще не подсчитывалось
			//echo modules_structure_sqlFilter::createQuery( $idTplLevel, false );
			$result = sys::sql( modules_structure_sqlFilter::createQuery( $tplLevel, false ), 1);
			
			$system['tplLevel'][
				modules_structure_tpl::getTplLevelLast()
			]['countChild'] = $result[0]['countChild'];
		}
		
		return $tplLevel['countChild'];
		
	}
	
	
	
	
	/*
	 * Загрузить дочерние разделы
	 */
	function &loadChild()
	{
		global $system;
		
		if( $system['tplLevel'][ modules_structure_tpl::getTplLevelLast()-1 ]['tplType']=='line' ){
			$tplLevel = &$system['tplLevel'][ modules_structure_tpl::getTplLevelLast()-2 ];
		}else{
			$tplLevel = &$system['tplLevel'][ modules_structure_tpl::getTplLevelLast()-1 ];
		}
		
		if( empty( $tplLevel['child'] ) ){
			//если операция для данного уровня в шаблоне еще не производилась
			
			
			
			$result = sys::sql( modules_structure_sqlFilter::createQuery( $tplLevel ), 1);
			
			$tplLevel['child'] = array();
			
			
			foreach( $result as $value ){
				
				$system['section'][ $value['id'] ] = $value;
				
				$tplLevel['child'][] = &$system['section'][ $value['id'] ];
				
				modules_structure_view::newBaseClass( $value['base_class'] );
			}
			
		}
		
		return $tplLevel;
		
	}
	
	
	
	
	/**
	 * Возвращает часть запроса, используя файлы sqlFilter
	 * 
	 * Если $full==true, то будет возвращена часть запроса 
	 * которая подходит для полной загрузки дочерних разделов
	 * Иначе будет возвращена часть запроса для подсчета
	 * количества дочерних разделов.
	 * 
	 * @return string
	 * @param int &$idTplLevel
	 * @param boolean $full[optional]
	 */
	function createQuery( &$tplLevel, $full=true ){
		
		global $system;
		
		if( empty( $tplLevel['sqlFilter'] ) ){
			
			//установка переменныx sqlFiltr в значение по умолчанию
			$sqlFilter	= Array();
			
			$sqlSelect	= '';
			$sqlWhere	= ' section.`parent_id`="'.$tplLevel['level']['section']['id'].'" ';
			$sqlFrom	= '';
			$sqlOrder	= '';
			$sqlGroup	= '';
			$sqlLimit	= '';
			
			
			
			
			
			if( isset($tplLevel['tplParam']['limitChild'][0]) and $tplLevel['tplParam']['limitChild'][0]>=0 ){
				//если в шаблоне явно проставлено значение
				$tplLevel['limitChild'] = &$tplLevel['tplParam']['limitChild'][0];
			}else{
				//значение по умолчанию
				$tplLevel['limitChild'] = -1;
			}
			
			
			if( isset($tplLevel['tplParam']['part'][0]) and $tplLevel['tplParam']['part'][0]>=2 ){
				$tplLevel['part'] = &$tplLevel['tplParam']['part'][0];
			}else{
				$tplLevel['part'] = 1;
			}
			
			
			if( isset($tplLevel['tplParam']['startChild'][0]) and $tplLevel['tplParam']['startChild'][0]>=0 ){
				//если в шаблоне явно проставлено значение
				$tplLevel['startChild'] = &$tplLevel['tplParam']['startChild'][0];
			}else{
				//значение по умолчанию
				$tplLevel['startChild'] = 1;
			}
			
			
			if( $tplLevel['tplParam']['order'][0]=='random' ){
				$sqlOrder = 'RAND()';
			}
			
			
			
			//если уровень активного раздела, то добавляем фильтр по умолчанию
			if( $tplLevel['level']['section']['id']==$system['tplLevel'][0]['level']['section']['id'] ){
				
				$sqlFilter['default'] = true;
				
				
				//limitChild
				if( $tplLevel['limitChild']<=-1 )
				{
					if( view::attr('limitChild')>=0 && view::attr('limitChild')!=false && view::attr('limitChild')!='' )
					{
						$tplLevel['limitChild'] = view::attr('limitChild');		
					}
					elseif( $system['urlParam']['limitChild']>=0 )
					{
						$tplLevel['limitChild'] = $system['urlParam']['limitChild'];
					}
				}
				
				
				//part
				if( $tplLevel['part']<=1 )
				{
					if( view::attr('part')==1 )
					{
						$tplLevel['part'] = view::attr('part');		
					}
					elseif( $system['urlParam']['part']>=1 )
					{
						$tplLevel['part'] = $system['urlParam']['part'];
					}
				}
				
				
				//startChild
				if( $tplLevel['startChild']<=1 )
				{
					if( view::attr('startChild')>=1 )
					{
						$tplLevel['startChild'] = view::attr('startChild');		
					}
					elseif( $system['urlParam']['startChild']>=1 )
					{
						$tplLevel['startChild'] = $system['urlParam']['startChild'];
					}
					else
					{
						$tplLevel['startChild'] = 1;
					}
				}
				
				
			}
			
			
			
			
			
			//ищем в параметрах все sqlFilter
			if( isset( $tplLevel['tplParam']['sqlFilter'][0]) ){
				foreach( $tplLevel['tplParam']['sqlFilter'] as $value ){
					$sqlFilter[ $value ] = true;
				}
			}
			
			//инклудим файлы sqlFilter
			foreach( $sqlFilter as $key => $value ){
				if( $value ){
					$value_file = '../data/sqlFilter/'.$key.'.php';
					if( is_readable($value_file) ) include($value_file);
				}
			}
			
			
			
			
			//sqlOrder - по умолчанию по позиции
			if( !(strlen($sqlOrder)>3) ){
				$sqlOrder = 'section.`pos` ';
			}
			
			
			
			
			//окончательная обработка переменных sql
			$tplLevel['sqlFilter']['select'] = $sqlSelect;
			$tplLevel['sqlFilter']['where'] = ' WHERE '.$sqlWhere;
			$tplLevel['sqlFilter']['order'] = ' ORDER BY '.$sqlOrder;
			
			
			if( $sqlGroup=='' ){
				$tplLevel['sqlFilter']['group'] = '';
			}else{
				$tplLevel['sqlFilter']['group'] = ' GROUP BY '.$sqlGroup;
			}
			
			if( strlen($sqlFrom)>3 ){
				$tplLevel['sqlFilter']['from'] = ' , '.$sqlFrom;
			}else{
				$tplLevel['sqlFilter']['from'] = '';
			}
			
			
			
			if( $tplLevel['limitChild'] >= 0 && is_numeric($tplLevel['limitChild']) ){
				$tplLevel['sqlFilter']['limit'] = 'LIMIT '.
					(($tplLevel['part']-1) * $tplLevel['limitChild'] + ($tplLevel['startChild']-1) ).
					','.$tplLevel['limitChild'];
			}else{
				$tplLevel['sqlFilter']['limit'] = '';
			}

			
		}
		
		
		
		
		
		
		if( $full ){
			//подготовка запроса для полной выборки раделов
			return '
				SELECT section.`id`, section.`parent_id`, section.`name`, section.`title`,  section.`base_class`
				'.$tplLevel['sqlFilter']['select'].'
				FROM `prefix_Sections` section
				'.$tplLevel['sqlFilter']['from'].'
				'.$tplLevel['sqlFilter']['where'].'
				'.$tplLevel['sqlFilter']['group'].'
				'.$tplLevel['sqlFilter']['order'].'
				'.$tplLevel['sqlFilter']['limit'].'
				;
			';
		}else{
			//подготовка запроса для вычисления количества разделов
			return '
				SELECT COUNT(1) countChild
				FROM `prefix_Sections` section
				'.$tplLevel['sqlFilter']['from'].'
				'.$tplLevel['sqlFilter']['where'].'
				LIMIT 1
				;
			';
		}
		
		
	}
	
	
  
}


?>