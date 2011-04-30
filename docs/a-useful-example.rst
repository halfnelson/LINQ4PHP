.. highlight:: php-inline

A Useful Example
================

Lets see how Linq can do something useful for us in real life. 

Problem: 
  We want to delete find all the .tmp files in the current directory and delete the
  2 largest ones.
  
Solution:

PHP's ``DirectoryIterator`` will help. But first lets LINQify it!::

    $files = LINQ::Linq(new DirectoryIterator('.'))
    
Then filter out just tmp files::

    ->Where(function($file){ return preg_match('/.*.tmp/', $file->getFilename());})
    
Then order by size (second parameter is for descending)::

    ->OrderBy(function($file){ return $file->getSize();}, true)
    
And we only want 2::

    ->Take(2);
    
Loop over our results and delete the files::

    foreach($files as $file) {
        unlink($file->getFilename());
    }

    



