<?php

namespace LINQ4PHP\Iterators;
/**
 * wraps a simple Iterator into an IteratorAggregate
 * note that getIterator always returns the same instance of iterator, so
 * do not mess with the iterator instance during a query (join to itself etc)
 */
class NaiveIteratorAggregate implements \IteratorAggregate {
  
    private $theIterator;
    public function getIterator()
    {
        return $this->theIterator;
    }

    public function __construct(\Iterator $iterator) {
        $this->theIterator = $iterator;
    }
}
