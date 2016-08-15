<?php

/*
 function fetchPartial($template, $params = array()){
        extract($params);
        ob_start();
        include VIEWS_BASEDIR.$template.'.php';
        return ob_get_clean();
    }
*/

class Render {
  public $content;

  function __construct($template_path, $values=array()) {
      $this->content = '';    

      $template_file = $this->get_template_filename($template_path);
      if ($template_file) {
        $content = file_get_contents($template_file) or die("No content $template_file");

       //Admin
        if (isset($values['id']) && isset($values['class']) ) {
         $values['link'] = '';
         $values['link'] .= '<div class="admin_links">';
         $link = "/" . $values['class'] . "/" . $values['id'];
         $values['link'] .= "<a href='$link/edit'>Edit</a>";
         $values['link'] .= " <a href='$link/delete'>Delete</a>"; 
         $values['link'] .= "</div>";
        }

        //print '<pre>'; print_r($values); print '</pre>';

        // Loop support
        $res = preg_match( "|{%LOOP (.*)%}(.*){%ENDLOOP%}|s", $content, $matches);
        if ($res && count($matches)>2) {
          $var = trim($matches[1]);
          $item_body = $matches[2];
          if (isset($values[$var])) {
            $item_value = $values[$var];
            $new_content = '';
            if (is_array($item_value) && count($item_value)) {
              foreach ($item_value as $key => $item) {
                $item_body_ = $item_body;
                foreach ($item as $key2 => $item2) {
                  $key_ = "@" . strtoupper("$var.$key2") . "@";
                  $new_item_body = str_replace($key_,$item2,$item_body_);
                  if ($new_item_body != $item_body_) {
                    if (empty($item2)) {
                      $new_item_body = str_replace("[%ENDCHECK ".strtoupper("$var.$key2"). "%]","{%ENDDEL%}",$new_item_body);
                      $new_item_body = str_replace("[%CHECK ".strtoupper("$var.$key2"). "%]","{%DEL%}",$new_item_body);
                    }
                    $item_body_ = $new_item_body;
                  }
                }
                $new_content .= $item_body_;
              }
            }
            $content = str_replace($item_body,$new_content,$content);
          }
        }

        //Exist support
        $res = preg_match( "|{%EXIST (.*)%}(.*){%ENDEXIST%}|s", $content, $matches);
        if ($res && count($matches)>2) {
          $var = trim($matches[1]);
          $item_body = $matches[2]; 
          if (isset($values[$var])) {
            if (empty($values[$var])) {
              $content = str_replace($item_body,'',$content);
            }
          }
        }

        //Del support
        $res = preg_match( "|{%DEL%}(.*){%ENDDEL%}|s", $content, $matches);
        if ($res && count($matches)>1) {
          $item_body = $matches[1]; 
          $content = str_replace($item_body,'',$content);
        }
 
        //Replace flat @var
        foreach ($values as $key => $value) {
          if (is_string($value)) {
            $key_ = "@" . strtoupper($key) . "@";
            $content = str_replace($key_,$value,$content);
          }
        } 

        //Clear {var}
        $content = preg_replace("|\[%(.*?)%\]|s","",$content);
        $content = preg_replace("|{%(.*?)%}|s","",$content);
        $content = preg_replace("|@(.*?)@|s","",$content);

        $this->content = $content; 
      }
  }

  private function get_template_filename($template_path) {
    global $templates_dir;

    $templates = explode("/", $template_path);
    while ( count($templates) ) {
      $template_file = $templates_dir . "/" . implode("-",$templates) . ".tpl.php";
      if (file_exists($template_file)) {
        return $template_file;
      }     
      array_pop($templates);
    }
  
    return FALSE;
  }
 
}
