<?php

namespace Customize\Form\Model;

/**
 * Description of Exhibitor
 *
 * @author yuta
 */
class Exhibitor {
  
    private $name;
    
    private $login_id;
    
    private $password;
    
    private $email;
    
    private $type;

    private $department;
    
    private $exhibitor_info;   
    
    function getName() {
      return $this->name;
    }

    function getLoginId() {
      return $this->login_id;
    }

    function getPassword() {
      return $this->password;
    }

    function getEmail() {
      return $this->email;
    }
    
    function getType() {
      return $this->type;
    }

    function getDepartment() {
      return $this->department;
    }
    
    function getExhibitorInfo() {
      return $this->exhibitor_info;
    }

    function setName($name) {
      $this->name = $name;
    }

    function setLoginId($login_id) {
      $this->login_id = $login_id;
    }

    function setPassword($password) {
      $this->password = $password;
    }

    function setEmail($email) {
      $this->email = $email;
    }
    
    function setType($type) {
      $this->type = $type;
    }

    function setDepartment($department) {
      $this->department = $department;
    }
    
    function setExhibitorInfo($exhibitor_info) {
      $this->exhibitor_info = $exhibitor_info;
    } 
    
}