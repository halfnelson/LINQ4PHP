.. highlight:: php

A Simple Example
================

To get a general feel for the library, we will walk through a simple example.

Problem: Sort an array of integers but only those whose value is less than 5
and print the results

Solution:

.. sourcecode:: php

  <?php
    //our array to filter and sort
    $a = array(1,4,2,3,6,5,8,7);

    //our query on the array.
    $query = LINQ::Linq($a)
                ->Where(function($i){ return $i < 5; })
                ->OrderBy(function($i){ return $i; });
	
	//print our results	       
    foreach ($query as $r) {
        echo "$r,";
    }
  ?>

Returns:

.. sourcecode:: text
  
  1,2,3,4

Seems simple but lets break it down:

.. sourcecode:: php-inline
  
    //our array to filter and sort
    $a = array(1,4,2,3,6,5,8,7);
	
The first line of the solution defines the array we need to sort.

.. sourcecode:: php-inline

    //our query on the array.
    $query = LINQ::Linq($a)

This line makes our array queryable using the Linq() static method. It 

.. sourcecode:: php-inline

                ->Where(function($i){ return $i < 5; })

This adds a where filter which is applied to each element of $a when it is iterated.
It will only yield the item to the iteration if the return value of the provided function is true.

.. sourcecode:: php-inline

                ->OrderBy(function($i){ return $i; });

This adds an ordering to the result of the filtered array. The function parameter
specifies that the ordering is based on the value of the array element.

.. sourcecode:: php-inline

    foreach ($query as $r) {
        echo "$r,";
    }

When foreach is called the original array ``$a`` is looped through once and 
filtering and sorting specfied previously is applied.

.. note:: 

    Most LINQ operators are deferred/Lazy not actually doing anything 
    until you begint to loop over the results.

    
