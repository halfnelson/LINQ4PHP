<?php
namespace LINQ4PHP\Comparers;

class ThenByKey {
	
	private $orderbykey;
	private $thenbykey;
	
	public function __construct($orderby, $thenby) {
		$this->orderbykey = $orderby;
		$this->thenbykey = $thenby;
	}
	
	public function getOrderByKey() {
		return $this->orderbykey;
	}

	public function getThenByKey() {
		return $this->thenbykey;
	}
	
}