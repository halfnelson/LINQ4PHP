<?php
namespace LINQ4PHP;

function Loader($class)
{
    $file = __DIR__.'/../'.str_replace('\\', '/', $class) . '.php';
    if(file_exists($file))
    {
        require $file;
    }
}

//load our gear.
spl_autoload_register('LINQ4PHP\Loader');


class LINQ {
	public static function From(&$traversable) {
        $ia = Iterators\LinqIterator::asIteratorAggregate($traversable);
        return new Iterators\LinqIterator(function () use ($ia) { return $ia->getIterator(); });
    }


	public static function Linq($traversable) {
        $ia = Iterators\LinqIterator::asIteratorAggregate($traversable);
        return new Iterators\LinqIterator(function () use ($ia) { return $ia->getIterator(); });
	}

}






