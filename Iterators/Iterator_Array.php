<?php

namespace LINQ4PHP\Iterators;
class Iterator_Array implements \Iterator {
  private $myArray;
  private $valid;
  private $currentval;
  private $currentkey;
  
  public function __construct( $givenArray ) {
    $this->myArray = $givenArray;
    $this->valid = false;
  }
  
  private function getVal() {
  	$res = each($this->myArray);
    if (is_array($res)) {
    	list($this->currentkey,$this->currentval) = $res;
    	$this->valid = true;
    } else {
    	$this->valid = false;
    }
  }
  
  function rewind() {
    reset($this->myArray);
    $this->getVal();
  }
  function current() {
    return $this->currentval;
  }
  function key() {
    return $this->currentkey;
  }
  function next() {
    $this->getVal();
  }
  function valid() {
    return $this->valid;
  }
}
