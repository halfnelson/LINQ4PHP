<?php
namespace LINQ4PHP\Comparers;

class CallbackComparer implements IComparer {
	private $callback;
	public function __construct($callback) {
		if (!is_callable($callback)) {
			throw new \Exception("Callback Comparer constructed with a non callable callback");
		}
		$this->callback = $callback;
	}
	
	public function Compare($x,$y) {
		return call_user_func_array($this->callback,array($x,$y));
	}
}