
Overview
========

LINQ4PHP is an implementation of collection processing methods in PHP, corresponding to those found in the IEnumerable interface from the .Net framework's "LINQ to Objects" implementation.

It allows for the construction of queries on, and processing of, a stream/array of data in an incremental, fluent and efficient manner.

Think of it as a replacement for an army of foreach statements.

.. sourcecode:: php

  <?php
	$query = Linq($languages)->Where(function($i){ return $i->supportsLinq(); })

	if ($query->Any(function($i){ return $i->getName() == 'PHP')) {
	    echo "LINQ4PHP makes LINQ to objects possible in PHP";
	}


LINQ4PHP contains two types of methods,

Projection Methods:
  These create a new collection from the existing collection by applying an operation to each element.

  * Select
  * Where
  * GroupBy
  * Join
  * Take
  * Skip
  * OrderBy
  
Aggregation Methods:
  These combine elements of the list into a result
  
  * Max,Min,Avg
  * All
  * Any
  * Aggregate
  * ToArray
  * ToLookup


  

  


