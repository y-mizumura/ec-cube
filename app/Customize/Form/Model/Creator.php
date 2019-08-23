<?php

namespace Customize\Form\Model;

/**
 * Description of Creator
 *
 * @author yuta
 */
class Creator {
  
    private $name;
    
    private $info;   
    
    function getName() {
      return $this->name;
    }

    function getInfo() {
      return $this->info;
    }

    function setName($name) {
      $this->name = $name;
    }

    function setInfo($info) {
      $this->info = $info;
    } 
    
}