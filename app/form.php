<?php

class Form {
  protected $type, $fields;

  function __construct($type, $fields) {
    $this->type = $type;
    $this->fields = $fields;

    $res['title'] = $type;

    if (is_string($fields)) {
      $res['form_body'] = $fields;
    }
    else {
      $form_body = '';
      foreach ($fields as $key=>$field) {
        $required = isset($field['required']) && $field['required'] ? ' <span class="form-required" title="Required">*</span>' : '';

        $form_body .= "<div><label for='edit-$key'>$key$required</label>";

        switch ($field['type']) {
          case 'char' :
            $form_body .= "<input type='text' id='edit-$key' name='$key' value='{$field['value']}' size='{$field['size']}' maxlength='255' />";
            break;

          case 'int' :
            $form_body .= "<input type='text' id='edit-$key' name='$key' value='{$field['value']}' size='10' maxlength='255' />";
            break;

          case 'checkbox' :
            $checked = !empty($field['value']) ? 'checked' : ''; 
            if (!empty($field['value'])) $field['value']=1;
            $form_body .= "<input type='checkbox' id='edit-$key' name='$key' value='{$field['value']}' $checked />";

            break;

          case 'text' :
            $form_body .= "<textarea id='edit-$key' name='$key' cols='{$field['cols']}' rows='{$field['rows']}' >{$field['value']}</textarea>";
            break;

          case 'file' :
            $form_body .= "<input type='file' id='edit-$key' name='$key' />" . "{$field['value']}";
            if (!empty($field['value'])) {
              $form_body .= " <label for='edit-$key-delete'>Delete?</label><input type='checkbox' id='edit-$key-delete' name='$key-delete' />";
            }
            break;

        }
        $form_body .= "</div>";
      }
      $res['form_body'] = $form_body;
    }

    if ($page = new Render("form", $res)) print $page->content;
  }

  function execute() {
    if (count($_POST) && isset($_POST['op'])) {
      //Checkbox
      foreach ($this->fields as $key=>$field) {
        if ($field['type']=='checkbox') {  
          $_POST[$key] = !isset($_POST[$key]) ? 0 : 1;
        }
      }

      if ($this->validate()) return $_POST;
    }
    return FALSE;
  }

  private function validate() {
    $errors = 0;
    foreach ($this->fields as $key=>$field) {
      if (!isset($_POST[$key])) continue;      

      $input = trim($_POST[$key]);
   
      //Required
      if (isset($field['required']) && $field['required']) {
        if (!strlen($input)) { print "<div class='error'><em>$key</em> is required field</div>"; $errors++; }
      }

      //Int
      if ($field['type']=='int') {
        if (!is_numeric($input)) { print "<div class='error'><em>$key</em> is numeric field</div>"; $errors++; }
      }
    }
    return !$errors;
  }
}
