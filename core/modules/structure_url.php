<?php

/*
 *
 */    
   
class modules_structure_url {

    /*
     *  Разбор URL адреса и построение массива $system['structure'].
     *  Если $request_uri это ID раздела, то он заменяется на URL этого раздела
     *  Если $request_uri это пустая строка или содержит только символы "/",
     *  откроется домашняя страница, или, если таковой не задано в настройках,
     *  будет взят URL первого подраздела из раздела с id=1 (Главное меню)
     */
    function recognizeUrl( $request_uri = '' ){
    
        global $system, $CONF;
        
        
        if($request_uri>=1){
            //если $request_uri это id раздела
            
            $paths = view::attr( '_url', 'id='.$request_uri );
            
            
        }else{
            //если $request_uri это uri путь
            
            //ищем REQUEST_URI, если неуказан $request_uri
            if( $request_uri=='' ){
                $request_uri = $_SERVER['REQUEST_URI'];
            }
            
            // Отделяем от адреса параметры страницы и запоминаем их
            $paths = explode('?', $request_uri);
            $paths = explode(':', $paths[0] );
            
            view::debug_point( $paths, 'parts===' );
            
            foreach( $paths as $key => $value ){
                
                if($key>0){
                    $param_page = explode('-',$value);
                    if( count($param_page)==2 ){
                        $system['urlParam'][$param_page[0]]=$param_page[1];
                    }
                }
            }
            
            
            
            // Проверяем адрес, если ничего не введено ищем домашнюю страницу
            if($paths[0]=='/'){
                //грузим домашнюю страницу
                
                $paths = modules_settings_sys::get('homePage');
                
                
                if( $paths>=1 ){
                    //если в $paths находится id раздела домашней страницы
                    $paths = view::attr('_url','id='.$paths);
                }
                
                if( strlen($paths)<=1 ){
                    //если в $paths указано неверное значение
                    //ищем первую подходящую страницу
                    
                    $result = sys::sql('
                        SELECT `name`
                        FROM `prefix_Sections`
                        WHERE `parent_id`="1"
                        LIMIT 1
                        ;
                    ', 1);
                    
                    $paths = $result[0]['name'];
                    
                }
                
                
            }else{
                //страница по указоному пути
                $paths = $paths[0];
            }
        
        }
        
        $paths = explode('/',$paths);
        
        view::debug_point($paths, 'URL= '.$request_uri, 0);
        
        modules_structure_url::buildingStructure($paths);
        
        
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
	function buildingStructure($paths){
		
		global $system;
		
		
		$temp = '';
		
		foreach($paths as $key => $value){
			
			if( $value!='' ){
				$temp .= ' or `name`="'.$value.'"';
				$paths_this[] = $value;
			}
			
		}
		
		unset( $paths );
		
		
		$result = sys::sql('
			SELECT `id`, `parent_id`, `name`
			FROM `prefix_Sections`
			WHERE 1=0 '.$temp.'
			;
		',1);
		
		
		
		$temp_a = count($result)-1;
		
		//ищем первый уровень
		while( $temp_a >= 0 ){
			//echo '='.$result[$temp_a]['parent_id'].'='.$result[$temp_a]['name'].'='.$paths_this[0].'=';
			if( $result[$temp_a]['parent_id']<=1 and $result[$temp_a]['name']==$paths_this[0] ){
				
				modules_structure_view::newLevel( $result[$temp_a]['id'] );
				
				$temp_a = -1;
			}
			
			$temp_a--;
			
		}
		
		
		
        if ($temp_a == -1){
			//небыло найдено первого раздела
			//<<error
        }
		
		
		
		//ищем все остальные разделы
		for( $i=1; $i<count($paths_this); $i++ ){
			
			$temp_a = count($result)-1;
			
			while( $temp_a >= 0 ){
				
				if( $result[$temp_a]['parent_id'] == $system['level'][modules_structure_view::getLevelLast()]['section']['id']
					and $result[$temp_a]['name'] == $paths_this[$i]
				){
					modules_structure_view::newLevel( $result[$temp_a]['id'] );
					$temp_a = -1;
				}
				$temp_a--;
			}
			
			if( $temp_a==-1 ){
				//небыло найдено очередного раздела
				//выходим из цикла
				$i=count($paths_this);
			}
			
		}
		
		
		$result ='';
		
		
	}
	
	
	
	
	
	/*
	 *  Производит изменения в массиве $system['structure']
	 *  в с учетом атрибутов __abstractParent у разделов в масиве	 
	 */
	function absctractParent(){
		
		global $system;
		
		//загружаем атрибут __abstractParent
		$abstractParent = view::attr('__abstractParent');
		
		//разбираем строку __abstractParent и вычисляем новый parent_id
		if( strlen($abstractParent)>0 ){
			$abstractParent = explode('/',$abstractParent);
			
			while( !($newAbstractParent>0) and list(,$value)=each($abstractParent) ){
				
				$conditions = explode(':',$value);
				
				if( isset($conditions[1]) ){
					
					$cond_array = explode(';',$conditions[1]);
					
					$uslovie=true;
					
					while( $uslovie and list(,$cond)=each($cond_array) ){
						
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
					
					if($uslovie) $newAbstractParent = $conditions[0];
					
					
					
				}else{
					$newAbstractParent = $conditions[0];
				}
				
			}
			
			
			
			
			if($newAbstractParent>0){
				
				$oldsection = $system['structure']['section'][ count($system['structure']['section'])-1 ];
				
				$id=$newAbstractParent;
				
				do {	
					$result = sys::sql('
						SELECT `name`, `parent_id`
						FROM `prefix_Sections`
						WHERE `id`="'.$id.'";
					',1);
					$paths[]=$result[0]['name'];
					$id = $result[0]['parent_id'];
				} while ($result[0]['parent_id']!='0' and $result[0]['parent_id']!='1');
				
				
				krsort($paths);
				
				
				
				if(count($paths)>0 and $paths[0]!=''){
					unset($system['structure']['section']);
					
					modules_structure_view::setSystemPagesData();
					
					modules_structure_url::buildingStructure($paths);
					
					modules_structure_view::setSystemPagesData($oldsection);
					
					//print_r($system);
					
				}
				
				
			}
			
		}
		
	}
	

}

?>