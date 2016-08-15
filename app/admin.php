<?php

class Admin {

  private function make_table( $name, $fields, $data ) {
    $actions = array('edit','delete','view');
    $table = array('head' => array_merge($fields,$actions));
    $table_data = array();

    foreach ($data as $val) {
      $class = str_replace("_","/",$name);
      if (isset($val['class'])) $class .= "/".$val['class'];

      $table_item = array(); 
      foreach ($fields as $key) {
          $table_item[$key] = $val[$key];
      }
      foreach ($actions as $action) {
        $link = "<a href='/$class/" . (isset($val['id']) ? $val['id']."/" : '') . "$action'>$action</a>";
        $table_item[$action] = $link;
      }
      $table_data[] = $table_item;
    }
    $table['data'] = $table_data;

    return $table;
  }

  private function show_table( $table, $caption=NULL ) {
    $out = '<table>';
    if ($caption) $out .= "<caption>$caption</caption>";
    $out .= '<tr>';
    foreach ($table['head'] as $val) {
      $out .= "<th>$val</th>";
    }
    $out .= '</tr>';
    
    foreach ($table['data'] as $row) {
      $out .= '<tr>';
      foreach ($row as $val) {
        $out .= "<td>$val</td>";
      }
      $out .= '</tr>';
    }
    $out .= '</table>';

    return $out;
  }

  private function admin_sql_table( $name, $fields, $create_link=NULL ) {
    $order = reset($fields);
    $dbquery = new DBQuery('select', "* FROM $name ORDER BY $order");
    $res = $dbquery->execute(FALSE);

    foreach ($res as $key0=>$item) {
      foreach ($item as $key=>$val) {
        if (in_array($key, array('created','updated'))) {
          $res[$key0][$key] = date("d.m.Y H:i", $val);
        }
      }
    }

    $table = $this->make_table($name, $fields, $res);  
   
    if (!$create_link) {
      $class = str_replace("_","/",$name);
      $create_link = "<a href='/$class/create'>create</a>";
    }
    return $this->show_table($table, "<h3>$name</h3>($create_link)" );

  }
  
  function view() {

    $body = '';
    $body .= $this->admin_sql_table( 'variables', array('name','value','description') );
    $body .= $this->admin_sql_table( 'entity_node', array('id','title','active','weight','created','updated') );

    $create_link = "<a href='/pages/teaser/create'>create teaser page</a>, <a href='/pages/landing/create'>create landing page</a>";
    $body .= $this->admin_sql_table( 'pages', array('id','title'), $create_link );

    if ($page = new Render("admin", array('title'=>'Admin', 'body' => $body) )) print $page->content;

  }

}

