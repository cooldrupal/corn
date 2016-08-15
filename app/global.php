<?php

class Site {

  public static function get_variable($name) {
    $query = array('name' => $name);   
    $dbquery = new DBQuery('select', $query, 'variables');
    $res = $dbquery->execute();
    if ($res && isset($res['value'])) return trim($res['value']);
    return NULL;
  }

  public static function set_variable($name, $value) {
    $query = array('name' => $name);   
    $dbquery = new DBQuery('delete', $query, 'variables');
    $dbquery->execute();

    $query['value'] = $value;
    $dbquery = new DBQuery('insert', $query, 'variables');
    $dbquery->execute();
  }


  public static function redirect($url, $http_response_code = 302) {
    $url = "http://".$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF']).$url;
    //print "<br>$url";
    header('Location: ' . $url, TRUE, $http_response_code);
    exit;
  }

  public static function js_redirect($url) {
    $url = "http://".$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF']).$url;
    print '<script type="text/javascript">';
    print 'window.location = "'.$url.'";';
    print '</script>';  
  }

}
