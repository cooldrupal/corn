<?php

class Pages {

 protected $name;

  function __construct() {}
  
  function create() {}

  function edit($id) {}

  function delete($id) {}

  function view($id) { return ''; }

  function load($id) {
    $query = array('id' => $id);   
    $dbquery = new DBQuery('select', $query, 'pages');
    $res = $dbquery->execute();
    return $res;
  }


}

class TeaserPages extends Pages {
  function __construct() {
    $this->name = 'teaser';
  }

  private function _get_fields() {
    $fields = array();
    $fields['title'] = array('type'=>'char', 'size'=>60, 'required'=>TRUE);
    $fields['class'] = array('type'=>'char', 'size'=>60, 'required'=>TRUE);
    $fields['tables'] = array('type'=>'char', 'size'=>60, 'required'=>TRUE);
    $fields['filter'] = array('type'=>'text', 'cols'=>60, 'rows'=>5, 'required'=>FALSE);
    $fields['sort'] = array('type'=>'text', 'cols'=>60, 'rows'=>5, 'required'=>FALSE);
    $fields['header'] = array('type'=>'text', 'cols'=>60, 'rows'=>5, 'required'=>FALSE);
    $fields['footer'] = array('type'=>'text', 'cols'=>60, 'rows'=>5, 'required'=>FALSE);
    $fields['pager'] = array('type'=>'char', 'size'=>10, 'required'=>FALSE);
     
    return $fields;
  }

  function create($values=NULL) {
    if (!$values) {
      $fields = $this->_get_fields();

      $fields['pager']['value'] = 0;
      $fields['alias'] = array('type'=>'char', 'size'=>60, 'required'=>FALSE, 'value'=>'');

      $form = new Form("Create page {$this->name}", $fields);
      $values = $form->execute();
      if (!$values) return;
    }
    
    // Save to database
    $query = array();
    foreach ($fields as $key=>$value) {
      if ($key != 'alias') {
        $query[$key] = $values[$key];
      }
    }
    $dbquery = new DBQuery('insert', $query, 'pages');
    $dbquery->execute();

    $dbquery = new DBQuery('select', 'MAX(id) AS last_id FROM pages');
    $res = $dbquery->execute();

    if( isset($res['last_id']) ) {
      $query = array('content_type'=>"pages/{$this->name}", 'content_id' =>$res['last_id'], 'alias'=>trim($values['alias']));
      $dbquery = new DBQuery('insert', $query, 'aliases');
      $dbquery->execute();

      print "Created {$this->name} : <a href='/pages/{$this->name}/{$res['last_id']}'>{$values['title']}</a>";
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

      $fields['alias'] = array('type'=>'char', 'size'=>60, 'required'=>FALSE, 'value'=>'');

      $query = array('content_type'=>"pages/{$this->name}", 'content_id' =>$id);
      $dbquery = new DBQuery('select', $query, 'aliases');
      $alias = $dbquery->execute();
      if ($alias && count($alias)) {
        if (!empty($alias['alias'])) {
          $fields['alias']['value'] = $alias['alias'];
        }
      }

      $form = new Form("Edit {$this->name} {$entity['title']}", $fields);
      if ($values = $form->execute() ) {
     
        // Update in database
        $query = array();
        foreach ($fields as $key=>$value) {
          if ($key != 'alias') $query[$key] = $values[$key];
        }
        $query2 = array('id' => $id);
        $dbquery = new DBQuery('update', $query, 'pages', $query2);
        $dbquery->execute();
 
        $values['alias'] = trim($values['alias']);
        if ($fields['alias']['value'] != $values['alias']) {
          //change alias
          if (empty($values['alias'])) {
            //delete alias
            $query = array('content_type'=>"pages/{$this->name}", 'content_id' =>$id);
            $dbquery = new DBQuery('delete', $query, 'aliases');
            $dbquery->execute();
          }
          else {
            if (empty($fields['alias']['value'])){
              //insert alias
              $query = array('content_type'=>"pages/{$this->name}", 'content_id' =>$id, 'alias'=>$values['alias']);
              $dbquery = new DBQuery('insert', $query, 'aliases');
              $dbquery->execute();
            }
            else {
              //update alias
              $query  = array('alias' => $values['alias']);
              $query2 = array('content_type'=>"pages/{$this->name}", 'content_id' =>$id);
              $dbquery = new DBQuery('update', $query, 'aliases', $query2);
              $dbquery->execute();
            }
          }
        } 

      print "Edited pages {$this->name}";
    
      }// form execute
    } 

  }

  function delete($id) {
    $entity = $this->load($id);
    $message = 'Delete "' . $entity['title'] . '"?';
    $form = new Form('Delete', $message);
    if ($form->execute() ) {

      $query = array('content_type'=>"pages/{$this->name}", 'content_id' =>$id);
      $dbquery = new DBQuery('delete', $query, 'aliases');
      $dbquery->execute();

      $query = array('id' => $id);   
      $dbquery = new DBQuery('delete', $query, 'pages');
      $dbquery->execute();

      print "Deleted {$this->name}";
    }
  }


 function view($id) {
    $pages = $this->load($id);
    if ($pages && count($pages)) {

      $query = " * from " . $pages['tables'];
      if (!empty($pages['filter'])) $query .= (" where " . $pages['filter']);
      if (!empty($pages['sort']))   $query .= (" order by " . $pages['sort']);
      $dbquery = new DBQuery('select', $query, $pages['tables']);
      $res = $dbquery->execute(FALSE);

      // Add aliases
      $content_type = str_replace("_","/",$pages['tables']);

      foreach ($res as $key=>$item) {
        $query = array('content_type'=> $content_type, 'content_id' =>$item['id']);
        $dbquery = new DBQuery('select', $query, 'aliases');
        $alias = $dbquery->execute();
        if (!empty($alias)) {
          $res[$key]['url'] = $alias['alias'];
        }
        else {
          $res[$key]['url'] = $content_type . "/" . $item['id'];
        } 
      }

      $values = array('title' => $pages['title'], 'header'=>$pages['header'], 'footer'=>$pages['footer'], 'item' => $res);
      if ($page = new Render("pages/{$this->name}/$id", $values)) print $page->content;
    }                                                        
  }

  function load($id) {
    return parent::load($id);
  }


}

//////////////////////////////////////////////////////////////////////////////////////////

class LandingPages extends TeaserPages {
  function __construct() {
    $this->name = 'landing';
  }

 function view($id) {
    $pages = $this->load($id);
    if ($pages && count($pages)) {
      $query = " * from " . $pages['tables'];
      if (!empty($pages['filter'])) $query .= (" where " . $pages['filter']);
      if (!empty($pages['sort']))   $query .= (" order by " . $pages['sort']);
      $dbquery = new DBQuery('select', $query, $pages['tables']);
      $res = $dbquery->execute(FALSE);

      $values = array('title' => $pages['title'], 'header'=>$pages['header'], 'footer'=>$pages['footer'], 'item' =>$res);
      if ($page = new Render("pages/{$this->name}/$id", $values)) print $page->content;
    }                                                        
  }


}
