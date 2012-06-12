<?php
  class modules_structure_panel{

    /**
     * Draw tpl file with replaces
     */
    public function draw_template($tpl, $replace = '') {

      if (is_array($tpl)) {
        $replace = $tpl['replace'];
        $tpl     = $tpl['tpl'];
      }

      global $find,$REPL;
      $replace['link'] = admin::getModulesLink();
      $replace['userName'] = $_SESSION['user_login'];

      $REPL = $replace;

      $filename = 'templates/'.$tpl.'.tpl';
      $handle   = fopen($filename, "r");
      $tpl      = fread($handle, filesize($filename));
      fclose($handle);

      if ($replace!=''){
        do{
          $find = false;
          $tpl = preg_replace_callback(
                "/\%([A-Za-z0-9-_]{2,20})\%/",
                  create_function(
                    '$matches',
                    'return admin::replace($matches);'
                ),
                  $tpl
          );
        }while( $find );
      }

      echo $tpl;
    }


    /**
     * Draw sub template
     */
    public function draw_sub($tpl) {

      if (is_array($tpl)) {
        $tpl     = $tpl['tpl'];
      }

      $filename = 'templates/'.$tpl.'.tpl';
      $handle   = fopen($filename, "r");
      $tpl      = fread($handle, filesize($filename));
      fclose($handle);

      echo $tpl;
    }


    /**
     * Get tree
     */
    public function get_tree($parent) {

      if (isset($parent['parent'])) {
        $parent = $parent['parent'];
      } else {
        $parent = 0;
      }
      
      $nodes = modules_structure_sys::get(array(
        'name'       => '',
        'title'      => '',
        'id'         => '',
        'base_class' => '',
        'pos'        => '',
        'parent_id'  => (string) $parent
      ));

      $childs_temp = sys::sql('SELECT COUNT( id ) as child , parent_id FROM prefix_Sections GROUP BY parent_id', 1);

      foreach ($childs_temp as $key => $value) {
        $childs[$value['parent_id']] = $value['child'];
      }

      foreach ($nodes as $key => $value) {
        if (isset($childs[$value['id']])) {
          $nodes[$key]['child'] = $childs[$value['id']];
        } else {
          $nodes[$key]['child'] = 0;
        }
      }

      echo json_encode($nodes);
    }


    /**
     * Get node
     */
    public function get_node($param) {

      $param['base_class'] = '';
      $param['name']       = '';
      $param['title']      = '';
      $param['pos']        = '';

      $node = modules_structure_sys::get($param);
      $node = $node[0];

      $base_class = modules_structure_panel::get_base_class($node['base_class']);

      if (isset($base_class['attr'])) {
        foreach ($base_class['attr'] as $key => $value) {
          $node[$value['name']] = '';
        }
      }

      $values = modules_structure_sys::get($node);
      $values = $values[0];

      $out = array(
        'id'          => $values['id'],
        'name'        => $node['name'],
        'title'       => $node['title'],
        'base_class'  => $base_class,
        'pos'         => $node['pos']
      );

      if (isset($base_class['attr'])) {
        foreach ($base_class['attr'] as $key => $value) {
          $attrs[$value['name']] = $value;
          $attrs[$value['name']]['value'] = $values[$value['name']];
        }
      } else {
        $attrs = array();
      }

      $out['attrs'] = $attrs;

      echo json_encode($out);
    }


    /**
     * Get base_class
     */
    public function get_base_class($id) {

      $base_class_temp = sys::sql('SELECT * FROM prefix_ClassSections WHERE id='.$id, 1);
      $attrs_temp      = sys::sql('SELECT * FROM prefix_ClassSections WHERE parent_id='.$id.' AND type="attr" ORDER BY id', 1);
      $childs_temp     = sys::sql('SELECT * FROM prefix_ClassSections WHERE parent_id='.$id.' AND type="type_children" ORDER BY id', 1);

      $base_class = array(
        'id'    => $base_class_temp[0]['id'],
        'name'  => $base_class_temp[0]['name'],
        'title' => $base_class_temp[0]['title']
      );

      foreach ($attrs_temp as $key => $value) {

        $insert = array(
          'name'      => $value['name'],
          'title'     => $value['title'],
          'component' => $value['value']
        );

        if ($value['value'] == 'TText') {
          $settings = sys::sql('SELECT type FROM prefix_TTextSettings WHERE id='.$value['id'], 1);
          if (count($settings) == 0) {
            $insert['type'] = "0";
          } else {
            $insert['type'] = $settings[0]['type'];
          }
        }

        if ($value['value'] == 'TSelect') {
          $settings = sys::sql('SELECT title, name FROM prefix_TSelect_Settings WHERE parent_id='.$value['id'], 1);
          if (count($settings) == 0) {
            $insert['options'] = array();
          } else {
            foreach ($settings as $key => $value) {
              $options[] = array(
                'title' => $value['title'],
                'name'  => $value['name']
              );
            }
            $insert['options'] = $options;
          }
        }

        $base_class['attr'][] = $insert;
      }

      foreach ($childs_temp as $key => $value) {
        $base_class['child'][] = array(
          'name'  => $value['name'],
          'title' => $value['title']
        );
      }

      return $base_class;
    }


    /**
     * Get children models
     */
    public function get_child_model($data) {

      if ($data['id'] == 0) {
        $childs = sys::sql('
          SELECT
            models.id,
            models.name,
            models.title
          FROM
            prefix_ClassSections as models
          WHERE
                models.parent_id = 0
            AND models.type = "type"
        ', 1);
      } else {
        $childs = sys::sql('
          SELECT
            models.id,
            models.name,
            models.title
          FROM
            prefix_Sections as content,
            prefix_ClassSections as models
          WHERE
                content.id = '.$data['id'].'
            AND content.base_class = models.parent_id
            AND models.type = "type_children"
        ', 1);        
      }

      echo json_encode($childs);
    }


    /**
     * Add child node
     */
    public function add_new_element($data) {

      $id = modules_structure_sys::set($data);

      echo json_encode(array('id' => $id));
    }


    /**
     * Get models for model_tree
     */
    public function get_model_tree() {

      $models = sys::sql('SELECT * FROM prefix_ClassSections WHERE type="type"', 1);

      echo json_encode($models);
    }


    /**
     * Get model
     */
    public function get_model($data) {

      $id = $data['id'];

      $model  = sys::sql('SELECT * FROM prefix_ClassSections WHERE id = '.$id, 1);
      $childs = sys::sql('SELECT * FROM prefix_ClassSections WHERE parent_id = '.$id.' AND type="type_children"', 1);
      $attrs  = sys::sql('SELECT * FROM prefix_ClassSections WHERE parent_id = '.$id.' AND type="attr"', 1);

      $out = array(
        'id'     => $model[0]['id'],
        'title'  => $model[0]['title'],
        'name'   => $model[0]['name'],
        'childs' => $childs,
        'attrs'  => $attrs
      );

      echo json_encode($out);
    }


    /**
     *
     */
    public function model_add_child($data) {

      $id = $data['id'];

      $sql = sys::sql("SELECT
                `title`,
                `name`
              FROM
                `prefix_ClassSections`
              WHERE
                `id` = '".$data['type']."'
      ;",0);

      $type = mysql_fetch_array($sql);

      $sql = sys::sql("INSERT INTO
                `prefix_ClassSections`
              VALUES (
                '',
                '$id',
                '".$type['name']."',
                '".$type['title']."',
                'type_children',
                ''
              )
      ;", 0);

      echo mysql_insert_id();
    }


    /**
     *
     */
    public function model_remove_child($data) {

      $id = $data['id'];
      $sql = sys::sql("DELETE
              FROM
                `prefix_ClassSections`
              WHERE
                `id` = '$id'
              LIMIT 1
      ;",0);
    }


    /**
     *
     */
    public function model_add_attr($data) {

      $sql = sys::sql("INSERT INTO
                `prefix_ClassSections`
              VALUES (
                '',
                '".$data['id']."',
                '".$data['name']."',
                '".$data['title']."',
                'attr',
                '".$data['value']."'
              )
      ;",0);

      echo mysql_insert_id();
    }


    /**
     *
     */
    public function model_remove($data) {

      $id = $data['id'];
      // Выбираем элементы структуры основанные на данном базовом классе 
      $sql = sys::sql("SELECT
                `id`
              FROM
                `prefix_Sections`
              WHERE
                `base_class` = '$id'
      ;",0);

      // Удаляем все элементы структуры основанные на данном базовом классе
      while ($row = mysql_fetch_array($sql)){
        modules_structure_admin::deleteElement($row['id']);
      }

      // Узнаем имя удаляемого класса
      $sql = sys::sql("SELECT
                `name`
              FROM
                `prefix_ClassSections`
              WHERE
                `id` = '$id'
      ;",0);

      $name = mysql_result($sql,0);

      // Удаляем все дочерние элементы Базового класса
      $sql = sys::sql("DELETE
              FROM
                `prefix_ClassSections`
              WHERE
                `name` = '$name'
      ;",0);

      // Удаляем базовый класс
      $sql = sys::sql("DELETE
              FROM
                `prefix_ClassSections`
              WHERE
                `id` = '$id'
              LIMIT 1
      ;",0);
    }
  }
?>