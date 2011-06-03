<?php
namespace LINQ4PHP\Comparers;

class ThenByComparer extends BaseComparer {
	
	private $thenby;
	
	public function __construct($orderby = null, $thenby = null) {
		parent::__construct($orderby);
		//clean up thenby 
		$this->thenby = self::AsIComparer($thenby);
	}

	public function Compare($x, $y) {
		$orderresult = parent::Compare($x->getOrderByKey(),$y->getOrderByKey());
		 							   
		//only use thenby if the the orderby elements are the same.
		if ($orderresult != 0) {
			return $orderresult;
		} else {
			return $this->thenby->Compare($x->getThenByKey(), $y->getThenByKey());
		}
	}
}