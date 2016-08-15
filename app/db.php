<?php
require_once('config.php');

class DBQuery {
   private $type, $query_base, $table, $query_base2;
   private $link;

  function __construct($type, $query_base, $table=NULL, $query_base2=NULL) {
    $this->type = $type;
    $this->query_base = $query_base;
    $this->table = $table;
    $this->query_base2 = $query_base2;

    global $connect;

    if (!$connect) {
      global $connect_settings;
      $this->link = mysql_connect($connect_settings['host'], $connect_settings['username'], $connect_settings['password'])
        or die('Error: ' . mysql_error());
      mysql_select_db($connect_settings['database'], $this->link) or die('Error select db');

      if (version_compare(mysql_get_server_info(), '4.1.0', '>=')) {
       mysql_query('SET NAMES "utf8"', $this->link);
      }
   
      $connect = $this->link;
    }
    else {
      $this->link = $connect;
    }
   }

  function execute($single_res=TRUE) {
    $query_res = NULL;
    $query = $this->buildQuery();

    if ($this->link) {
      //print "<br><small>Do query= [$query] </small>";
      $result = mysql_query($query) or die("Error query:  [$query] " . mysql_error()); 

      if ($this->type=='select') {
        $query_res = array();
        while ($line = mysql_fetch_array($result, MYSQL_ASSOC)) {
          $query_res_item = array();
          foreach ($line as $col_key => $col_value) {
            $query_res_item[$col_key] = $col_value;
          }
          $query_res[] = $query_res_item;
          }
       }

      if ($result && is_resource($result)) mysql_free_result($result);
     }

     //print "<br>Result: ";
     //print_r($query_res);
     if (count($query_res)==1 && $single_res) {
       $query_res = $query_res[0];
     }

     return $query_res;
   }

  private function buildQueryArray($arr) {
     $_query = array();
     foreach ($arr as $key=>$val) {
        //if (is_string($val)) $val = mysql_real_escape_string($val);
        $_query[] = is_string($val) ? "$key='" . addcslashes($val,"'") . "'" : "$key=$val";
     }
     return $_query;
   }

  private function buildQuery() {
   
      //print "<br>buildQuery={$this->type}";
      switch ($this->type) {
        case 'select' : 
          if (is_array($this->query_base)) {
            $query_where = "where " . implode(" and ",$this->buildQueryArray($this->query_base));
            $query = "{$this->type} * from {$this->table} {$query_where}";
          }
          else {
            $query_where = mysql_real_escape_string($this->query_base);
            $query = "{$this->type} {$query_where}";
          }
          return $query;
        
        case 'insert' : 
          if (is_array($this->query_base)) {
            $query_set = "set " . implode(",",$this->buildQueryArray($this->query_base));
          }
          else {
            $query_set = mysql_real_escape_string($this->query_base);
          }
          return "{$this->type} into {$this->table} {$query_set}";

        case 'update' : 
          if (is_array($this->query_base)) {
            $query_set = "set " . implode(",",$this->buildQueryArray($this->query_base));
          }
          else {
            $query_set = mysql_real_escape_string($this->query_base);
          }

          if (is_array($this->query_base2)) {
            $query_where = "where " . implode(" and ",$this->buildQueryArray($this->query_base2));
          }
          else {
            $query_where = mysql_real_escape_string($this->query_base2);
          }

          return "{$this->type} {$this->table} {$query_set} {$query_where}";

        case 'delete' : 
          if (is_array($this->query_base)) {
            $query_where = "where " . implode(" and ",$this->buildQueryArray($this->query_base));
          }
          else {
            $query_where = mysql_real_escape_string($this->query_base);
          }
          return "{$this->type} from {$this->table} {$query_where}";
      }
   }

}
