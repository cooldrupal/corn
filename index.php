<?php
// Setting internal encoding to UTF-8.
if (!ini_get('mbstring.internal_encoding')) {
    @ini_set("mbstring.internal_encoding", 'UTF-8');
    mb_internal_encoding('UTF-8');
}

require_once('app/db.php');
require_once('app/global.php');

function autoload_classes($param) {
  $param = strtolower($param);
  $classes = array('entity','pages','admin','render','form');

  foreach ($classes as $class) {
   if ( preg_match("|".$class."$|", $param, $matches) ) {
     include_once("app/$class.php");
     return;
    }
  }
}

spl_autoload_register('autoload_classes');

//Current path

$currpath = "<front>";
if (isset($_GET['q']) && $currpath = trim($_GET['q'], "/")) {
  //Check alias
  $dbquery = new DBQuery('select', array('alias' => $currpath), 'aliases');
  $res = $dbquery->execute();
  if ($res && isset($res['id'])) {
    $currpath = $res['content_type'] . "/" . $res['content_id'];
  }
}

//Frontpage
if ( $currpath == "<front>" && $frontpage = Site::get_variable('frontpage') ) {
  $currpath = $frontpage;
}

$arg = explode('/', $currpath);

if ( count($arg) >= 2 ) {
   //Determine controller, id, action
   $action = array_pop($arg);

   if (is_numeric($action)) {
     $id = $action;
     $action = 'view';
   }
   else {
     $noid_actions = array('create');
     $id = in_array($action,$noid_actions) ? NULL : array_pop($arg);
   }

   //$controller = '';
   foreach (array_reverse($arg) as $part) {
     $controller .= ucfirst($part);
   }

   //print "<br>controller=$controller id=$id action=$action";

   //Run class
   $instance = new $controller(); 
   if ($instance) {
     if ($id) {
       $instance->$action($id);
     }
     else {
       $instance->$action();
     }
   }
   else { 
     //Error page
     print "Error: no exist class $controller";
   }
}
else {

  switch ($currpath) {    
    case 'admin' : 
      $instance = new Admin();
      $instance->view();
      break;
    default:
      if ($page = new Render("index")) {
	print $page->content;
      }
  };

}  


//Cleanup

global $connect;
if ($connect) {
  mysql_close($connect); 
}