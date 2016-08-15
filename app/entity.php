<?php

class Entity {
  protected $name;

  function __construct() {}
  
  function create() {}

  function edit($id) {}

  function delete($id) {}

  function view($id) { return ''; }

  function load($name, $id) {
    $query = array('id' => $id);   
    $dbquery = new DBQuery('select', $query, 'entity_'.$name);
    $res = $dbquery->execute();
    return $res;
  }

}

class NodeEntity extends Entity {
  protected $name;

  function __construct() {
    $this->name = 'node';
  }

  private function _get_fields() {
      $fields = array();
      $fields['title'] = array('type'=>'char', 'size'=>60, 'required'=>TRUE);
      $fields['body'] = array('type'=>'text', 'cols'=>60, 'rows'=>5, 'required'=>TRUE);
      $fields['image'] = array('type'=>'file');

      $fields['weight'] = array('type'=>'int', 'required'=>TRUE, 'value'=>0);
      $fields['active'] = array('type'=>'checkbox');
     
      return $fields;
  }

  function create($values=NULL) {
    if (!$values) {
      $fields = $this->_get_fields();
      $fields['alias'] = array('type'=>'char', 'size'=>60, 'required'=>FALSE);

      $form = new Form("Create {$this->name}", $fields);
      $values = $form->execute();
      if (!$values) return;
    }
    
    // Add file
    $values['image'] = isset($fields['image']['value']) ? $fields['image']['value'] : '';
    if (!empty($_FILES["image"]["name"]) ) {
      global $files_dir;
      $target_file = $files_dir . "/" . basename($_FILES["image"]["name"]);
      move_uploaded_file($_FILES["image"]["tmp_name"], $target_file);
      $values['image'] = $target_file;
    }

    // Save to database
    $now = time();
    $query = array('created'=>$now , 'updated' => $now);
    foreach ($fields as $key=>$value) {
      if ($key != 'alias') {
        $query[$key] = $values[$key];
      }
    }
    $dbquery = new DBQuery('insert', $query, 'entity_'.$this->name);
    $dbquery->execute();

    $dbquery = new DBQuery('select', 'MAX(id) AS last_id FROM entity_'.$this->name);
    $res = $dbquery->execute();

    if( isset($res['last_id']) ) {
      $query = array('content_type'=>"entity/{$this->name}", 'content_id' =>$res['last_id'], 'alias'=>trim($values['alias']));
      $dbquery = new DBQuery('insert', $query, 'aliases');
      $dbquery->execute();

      print "Created {$this->name} : <a href='/entity/{$this->name}/{$res['last_id']}'>{$values['title']}</a>";
      return $res['last_id'];
    }

    return NULL;
  }

  function edit($id) {
  
    $entity = $this->load($id);
    if ($entity && count($entity)) {

      $fields = $this->_get_fields();
      foreach ($fields as $key=>$val) {
        $fields[$key]['value'] = $entity[$key];
      }

      $fields['alias'] = array('type'=>'char', 'size'=>60, 'required'=>FALSE, 'value' => '');

      $query = array('content_type'=>"entity/{$this->name}", 'content_id' =>$id);
      $dbquery = new DBQuery('select', $query, 'aliases');
      $alias = $dbquery->execute();
      if ($alias && count($alias)) {
        if (!empty($alias['alias'])) {
          $fields['alias']['value'] = $alias['alias'];
        }
      }

      $form = new Form("Edit {$this->name} {$entity['title']}", $fields);
      if ($values = $form->execute() ) {

        // Update file
        $values['image'] = isset($fields['image']['value']) ? $fields['image']['value'] : '';
        if (!empty($_FILES["image"]["name"]) ) {
          if (!empty($entity['image'])) unlink($entity['image']);
          global $files_dir;
          $target_file = $files_dir . "/" . basename($_FILES["image"]["name"]);
          move_uploaded_file($_FILES["image"]["tmp_name"], $target_file);
          $values['image'] = $target_file;
        }
        else {
          if (!empty($values['image']) && isset($values['image-delete']) ) {
            unlink($entity['image']);
            $values['image'] = '';
          }
        }
   
        // Update in database
        $query = array('updated' => time() );
        foreach ($fields as $key=>$value) {
          if ($key != 'alias') {
            $query[$key] = $values[$key];
          }
        }
        $query2 = array('id' => $id);
        $dbquery = new DBQuery('update', $query, 'entity_'.$this->name, $query2);
        $dbquery->execute();
 
        $values['alias'] = trim($values['alias']);
        if ($fields['alias']['value'] != $values['alias']) {
          //change alias
          if (empty($values['alias'])) {
            //delete alias
            $query = array('content_type'=>"entity/{$this->name}", 'content_id' =>$id);
            $dbquery = new DBQuery('delete', $query, 'aliases');
            $dbquery->execute();
          }
          else {
            if (empty($fields['alias']['value'])){
              //insert alias
              $query = array('content_type'=>"entity/{$this->name}", 'content_id' =>$id, 'alias'=>$values['alias']);
              $dbquery = new DBQuery('insert', $query, 'aliases');
              $dbquery->execute();
            }
            else {
              //update alias
              $query  = array('alias' => $values['alias']);
              $query2 = array('content_type'=>"entity/{$this->name}", 'content_id' =>$id);
              $dbquery = new DBQuery('update', $query, 'aliases', $query2);
              $dbquery->execute();
            }
          }
        } 

        print "Edited {$this->name}";
        Site::js_redirect("entity/" . $this->name . "/$id"); 

      }// form execute
    } 

  }

  function delete($id) {
  
    $entity = $this->load($id);
    $message = 'Delete "' . $entity['title'] . '"?';
    $form = new Form('Delete', $message);
    if ($form->execute() ) {

      if (!empty($entity['image'])) unlink($entity['image']);

      $query = array('content_type'=>"entity/{$this->name}", 'content_id' =>$id);
      $dbquery = new DBQuery('delete', $query, 'aliases');
      $dbquery->execute();

      $query = array('id' => $id);   
      $dbquery = new DBQuery('delete', $query, 'entity_'.$this->name);
      $dbquery->execute();

      print "Deleted {$this->name}";

    }
  }

  function view($id) {
    $entity = $this->load($id);
    if ($entity && count($entity)) {
      $entity['class'] = "entity/{$this->name}";
      if ($page = new Render("entity/{$this->name}/$id", $entity)) print $page->content;
    }                  
    else {
      print "Error: TODO 404";
    }                                    
  }

  function load($id) {
    return parent::load($this->name,$id);
  }

}