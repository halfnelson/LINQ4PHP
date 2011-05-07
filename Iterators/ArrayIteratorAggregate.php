<?php

namespace LINQ4PHP\Iterators;
/**
 * Created by JetBrains PhpStorm.
 * User: David
 * Date: 7/05/11
 * Time: 1:35 PM
 * To change this template use File | Settings | File Templates.
 */
 
class ArrayIteratorAggregate implements \IteratorAggregate {

    private $arrayReference;

    public function getIterator()
    {
        //using the stored array reference, create a new ArrayIterator instance
        return new \ArrayIterator($this->arrayReference);
    }

    public function __construct(&$sourceArray) {
        //take a copy of the reference
        $this->arrayReference = &$sourceArray;
    }

}
