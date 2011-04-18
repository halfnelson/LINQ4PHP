<?php
namespace LINQ4PHP\Iterators;
class RangeIterator implements \Iterator {
  private $start;
  private $end;
  private $current;
	
  public function __construct( $start, $count ) {
    $this->start = $start;
    $this->end = $start+($count-1);
  }
  function rewind() {
    $this->current = $this->start;
  }
  function current() {
    return $this->current;
  }
  function key() {
    return $this->current;
  }
  function next() {
    $this->current++;
  }
  function valid() {
    return ($this->current <= $this->end && $this->current >= $this->start);
  }
}