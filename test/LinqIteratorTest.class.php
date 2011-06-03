<?php


require_once __DIR__ . "/../LINQ4PHP.php";

use \LINQ4PHP\LINQ;
use \LINQ4PHP\Iterators\LinqIterator;



class LinqIteratorTest extends PHPUnit_Framework_TestCase
{
    private function asIterator($trav)
    {
        if ($trav instanceof \Iterator) {
            return $trav;
        } elseif ($trav instanceof \IteratorAggregate) {
            return $trav->getIterator();
        } elseif (is_array($trav)) {
            return new ArrayIterator($trav);
        }
        throw new Exception("asIterator can only convert array, iteratoraggregate and Iterator");
    }


    private function AssertIteratorsEqual($it1, $it2)
    {

        $it1 = $this->asIterator($it1);
        $it2 = $this->asIterator($it2);


        $it2->rewind();
        foreach ($it1 as $it) {
            if ($it !== $it2->current()) {
                print "Item1:";
                print_r($it);
                print "\n";
                print "Item2:";
                print_r($it2->current());
                print "\n";
                $this->assertTrue(FALSE, "Sequences are not equal $it <> {$it2->current()}");
            }
            $it2->next();
        }
        //list 2 was longer!
        if ($it2->valid()) {
            LINQ::Linq($it2)->PrintAll();
            $this->assertTrue(FALSE, "Second Sequence was longer than the first. Got:" . $it2->current());
        }
    }


    /**
     * @test
     */
    public function testConstruct()
    {
        $a = array(1, 2, 3, 4, 5, 6, 7, 8, 9, 10);
        $l = LINQ::Linq($a);
        $result = array();
        foreach ($l as $val) {
            $result[] = $val;
        }
        $this->assertSame($a, $result);
    }

    public function testSelect()
    {
        $a = array(1, 2, 3, 4, 5, 6, 7, 8, 9, 10);
        $l = LINQ::Linq($a)->Select(function($i)
            {
                return $i + 1;
            });
        $this->AssertIteratorsEqual(array(2, 3, 4, 5, 6, 7, 8, 9, 10, 11), $l);

    }

    public function testSelectMany()
    {
        $a = array(
            array(1, 2, 3),
            array(4, 5, 6),
            array(7, 8, 9, 10)
        );
        $l = LINQ::Linq($a)->SelectMany(function($i)
            {
                return $i;
            }, function($orig, $i)
            {
                return $i + 1;
            });
        $this->AssertIteratorsEqual(array(2, 3, 4, 5, 6, 7, 8, 9, 10, 11), $l);
    }

    public function testWhere()
    {
        $a = array(1, 2, 3, 4, 5, 6, 7, 8, 9, 10);
        $l = LINQ::Linq($a)->Where(function($i)
            {
                return $i % 2 == 0;
            });
        $this->AssertIteratorsEqual(array(2, 4, 6, 8, 10), $l);
    }

    public function testRange()
    {
        $a = LinqIterator::Range(1, 10);
        $this->AssertIteratorsEqual($a, array(1, 2, 3, 4, 5, 6, 7, 8, 9, 10));
    }

    public function testEmpty()
    {
        $a = LinqIterator::EmptyList();
        foreach ($a as $v) {
            $this->AssertTrue(FALSE, 'Empty list should be empty');
        }
    }

    public function testRepeat()
    {
        $a = LinqIterator::Repeat(9, 5);
        $this->AssertIteratorsEqual($a, array(9, 9, 9, 9, 9));
    }

    public function testCount()
    {
        $a = array(1, 2, 3, 4, 5, 6, 7, 8, 9, 10);
        $l = LINQ::Linq($a);
        $this->AssertEquals($l->Count(), count($a));
    }

    public function testCountWithWhere()
    {
        $a = array(1, 2, 3, 4, 5, 6, 7, 8, 9, 10);
        $l = LINQ::Linq($a);
        $this->AssertEquals($l->count(function($i)
                     {
                         return $i < 6;
                     }), 5);
    }

    public function testConcat()
    {
        $a = LINQ::Linq(array(1, 2, 3, 4, 5));
        $b = array(6, 7, 8, 9, 10);
        $this->AssertIteratorsEqual($a->Concat($b), array(1, 2, 3, 4, 5, 6, 7, 8, 9, 10));
    }

    public function testAggregate()
    {
        $a = LINQ::Linq(array(2, 6, 3, 7, 5, 1));
        $max = $a->Aggregate(function($max, $i)
            {
                return $i > $max ? $i : $max;
            });
        $this->AssertEquals(7, $max);
    }

    public function testAggregateWithSeed()
    {
        $a = LINQ::Linq(array(2, 6, 3, 7, 5, 1));
        $has7 = $a->AggregateWithSeed(array(FALSE, 7), function($exists, $i)
            {
                return $exists[1] == $i ? array(TRUE, $exists[1]) : $exists;
            }, function($exists)
            {
                return $exists[0];
            });
        $this->AssertTrue($has7);
    }


    public function testAnyNoPredicate()
    {
        $a = LINQ::Linq(array(1, 2, 3, 4, 5));
        $this->AssertTrue($a->Any());
    }

    public function testAnyNoPredicateEmptyList()
    {
        $a = LINQ::Linq(new EmptyIterator());
        $this->AssertFalse($a->Any());
    }

    public function testAny()
    {
        $a = LINQ::Linq(array(1, 2, 3, 4, 5));
        $this->AssertTrue($a->Any(function($i)
                              {
                                  return $i % 2 == 0;
                              }));
    }

    public function testAnyEmptyList()
    {
        $a = LINQ::Linq(new EmptyIterator());
        $this->AssertFalse($a->Any(function($i)
                               {
                                   return $i % 2 == 0;
                               }));
    }

    public function testAll()
    {
        $a = LINQ::Linq(array(2, 4, 6, 8, 10));
        $this->AssertTrue($a->All(function($i)
                              {
                                  return $i % 2 == 0;
                              }));
        $this->AssertFalse($a->All(function($i)
                               {
                                   return $i < 3;
                               }));
    }

    public function testFirst()
    {
        $a = LINQ::Linq(array(2, 3, 4));
        $this->AssertEquals(2, $a->First());
    }

    public function testLast()
    {
        $a = LINQ::Linq(array(2, 3, 4));
        $this->AssertEquals(4, $a->Last());
    }

    public function testSingle()
    {
        $a = LINQ::Linq(array(2));
        $this->AssertEquals(2, $a->Single());
    }

    public function testDistinctNoCompare()
    {
        $a = LINQ::Linq(array(1, 1, 2, 3, 4, 5, 4));
        $this->AssertIteratorsEqual(array(1, 2, 3, 4, 5), $a->Distinct());
    }

    public function testDistinctWithCompare()
    {
        $a = LINQ::Linq(array(1, 1.0, 2, 3, 4, 5, 4));
        $this->AssertIteratorsEqual(array(1, 2, 3, 4, 5), $a->Distinct(function($a, $b)
                                                            {
                                                                return $a == $b;
                                                            }));
    }

    public function testUnionNoCompare()
    {
        $a = LINQ::Linq(array(1, 1, 2, 3, 4, 5, 4));
        $b = LINQ::Linq(array(5, 6, 6, 7));
        $this->AssertIteratorsEqual(array(1, 2, 3, 4, 5, 6, 7), $a->Union($b));

    }

    public function testIntersectNoCompare()
    {
        $a = LINQ::Linq(array(1, 1, 2, 3, 4, 5, 4));
        $b = LINQ::Linq(array(4, 4, 6, 6, 7));
        $this->AssertIteratorsEqual(array(4), $a->Intersect($b));

    }

    public function testExceptNoCompare()
    {
        $a = LINQ::Linq(array(1, 1, 2, 3, 4, 5, 4));
        $b = LINQ::Linq(array(4, 4, 6, 6, 7));
        $this->AssertIteratorsEqual(array(1, 2, 3, 5), $a->Except($b));
    }

    public function testToLookup()
    {

        $a = LINQ::Linq(array("one", "two", "three", "four"));
        $lu = $a->ToLookup(function($i)
            {
                return strlen($i);
            }, function($i)
            {
                return $i . '!';
            });
        $this->AssertIteratorsEqual($lu[3], array("one!", "two!"));
        $this->AssertIteratorsEqual($lu[4], array("four!"));
        $this->AssertIteratorsEqual($lu[5], array("three!"));

        //TODO: test comparer.
    }

    public function testJoin()
    {
        $a = LINQ::Linq(array("one", "two", "three", "four"));
        $b = LINQ::Linq(array("five", "six"));
        $i = $a->Join($b,
            function($i)
            {
                return strlen($i);
            },
            function($i)
            {
                return strlen($i);
            },
            function($oa, $ob)
            {
                return $oa . '-' . $ob;
            }
        );
        $this->AssertIteratorsEqual(array("one-six", "two-six", "four-five"), $i);
    }

    public function testGroupBy()
    {
        $a = LINQ::Linq(array("one", "two", "three", "four"));
        $l = $a->GroupBy(function($i)
            {
                return strlen($i);
            }, NULL, function($k, $g)
            {
                $t = '';
                foreach ($g as $i) {
                    $t .= $i . ',';
                }
                return $k . ':' . $t;
            });
        $this->AssertIteratorsEqual(array('3:one,two,', '5:three,', '4:four,'), $l);
    }

    public function testGroupJoin()
    {
        $a = LINQ::Linq(array("one", "two", "three", "four"));
        $b = LINQ::Linq(array("five", "six", "ten"));
        $i = $a->GroupJoin($b,
            function($i)
            {
                return strlen($i);
            },
            function($i)
            {
                return strlen($i);
            },
            function($k, $g)
            {
                $t = '';
                foreach ($g as $i) {
                    $t .= $i . ',';
                }
                return $k . ':' . $t;
            }
        );
        $this->AssertIteratorsEqual(array("one:six,ten,", "two:six,ten,", "three:", "four:five,"), $i);

    }

    public function testTakeWhile()
    {
        $a = LINQ::Linq(array(1, 1, 1, 6, 1, 1, 1, 1, 1, 1));
        $i = $a->TakeWhile(function($v, $idx)
            {
                $v < 5;
            });
        $this->AssertIteratorsEqual(array(1, 1, 1), $i);
    }

    public function testTake()
    {
        $a = LINQ::Linq(array(1, 1, 1, 6, 1, 1, 1, 1, 1, 1));
        $i = $a->Take(5);
        $this->AssertIteratorsEqual(array(1, 1, 1, 6, 1), $i);
    }

    public function testSkipWhile()
    {
        $a = LINQ::Linq(array(1, 1, 1, 6, 1, 1, 1, 1, 1, 1));
        $i = $a->SkipWhile(function($v, $idx)
            {
                return $v < 5;
            });
        $this->AssertIteratorsEqual(array(6, 1, 1, 1, 1, 1, 1), $i);
    }

    public function testSkip()
    {
        $a = LINQ::Linq(array(1, 1, 1, 6, 1, 1, 1, 1, 1, 1));
        $i = $a->Skip(5);
        $this->AssertIteratorsEqual(array(1, 1, 1, 1, 1), $i);
    }

    public function testToArray()
    {
        $a = LINQ::Linq(array(1, 2, 3, 4, 5));
        $this->AssertEquals($a->ToArray(), array(1, 2, 3, 4, 5));
    }

    public function testToList()
    {
        $a = LINQ::Linq(array(1, 2, 3, 4, 5));
        $this->AssertIteratorsEqual($a->ToList(), array(1, 2, 3, 4, 5));
    }

    public function testOrderBy()
    {
        $a = LINQ::Linq(array("one", "two", "three", "four"));
        $i = $a->OrderBy(function($i)
            {
                return strlen($i);
            });
        $this->AssertIteratorsEqual(array("one", "two", "four", "three"), $i);
    }

    public function testOrderByDescending()
    {
        $a = LINQ::Linq(array("one", "two", "three", "four"));
        $i = $a->OrderByDescending(function($i)
            {
                return strlen($i);
            });
        $this->AssertIteratorsEqual(array("three", "four", "two", "one"), $i);
    }

    public function testThenBy()
    {
        $a = LINQ::Linq(array("13", "23", "12", "24"));
        $i = $a->OrderBy(function($i)
            {
                return substr($i, 0, 1);
            })
                ->ThenBy(function($i)
            {
                return substr($i, 1, 1);
            });
        $this->AssertIteratorsEqual(array("12", "13", "23", "24"), $i);
    }

    public function testThenByDescending()
    {
        $a = LINQ::Linq(array("13", "23", "12", "24"));
        $i = $a->OrderBy(function($i)
            {
                return substr($i, 0, 1);
            })
                ->ThenByDescending(function($i)
            {
                return substr($i, 1, 1);
            });
        $this->AssertIteratorsEqual(array("13", "12", "24", "23"), $i);
    }

    public function testReverse()
    {
        $a = LINQ::Linq(array("one", "two", "three", "four"));
        $i = $a->Reverse();
        $this->AssertIteratorsEqual(array("four", "three", "two", "one"), $i);
    }

    public function testSum()
    {
        $a = LINQ::Linq(array(1, 2, 3, 4, 5, 6, 7, 8, 9));
        $sum = $a->Sum();
        $this->AssertEquals($sum, 45);
    }

    public function testSumWithSelector()
    {
        $a = LINQ::Linq(array(1, 2, 3, 4, 5, 6, 7, 8, 9));
        $sum = $a->Sum(function($i)
            {
                return $i + 1;
            });
        $this->AssertEquals($sum, 54);
    }

    public function testMax()
    {
        $a = LINQ::Linq(array(1, 10, 2, 3, 4, 5, 6, 7, 8, 9));
        $max = $a->Max();
        $this->AssertEquals($max, 10);
    }

    public function testMaxWithSelector()
    {
        $a = LINQ::Linq(array(1, 10, 2, 3, 4, 5, 6, 7, 8, 9));
        $max = $a->Max(function($i)
            {
                return $i + 1;
            });
        $this->AssertEquals($max, 11);
    }

    public function testMin()
    {
        $a = LINQ::Linq(array(1, 10, 2, 3, 4, 5, 6, 7, 8, 9));
        $min = $a->Min();
        $this->AssertEquals($min, 1);
    }

    public function testMinWithSelector()
    {
        $a = LINQ::Linq(array(1, 10, 2, 3, 4, 5, 6, 7, 8, 9));
        $min = $a->Min(function($i)
            {
                return $i + 1;
            });
        $this->AssertEquals($min, 2);
    }

    public function testAverage()
    {
        $a = LINQ::Linq(array(1, 2, 3, 4, 5));
        $avg = $a->Average();
        $this->AssertEquals($avg, 3);
    }

    public function testAverageWithSelector()
    {
        $a = LINQ::Linq(array(1, 2, 3, 4, 5));
        $avg = $a->Average(function($i)
            {
                return $i + 1;
            });
        $this->AssertEquals($avg, 4);
    }

    public function testElementAt()
    {
        $a = LINQ::Linq(array(1, 2, 3, 4, 5));
        $ele = $a->ElementAt(3);
        $this->AssertEquals($ele, 4); //zero based index
    }

    public function testContains()
    {
        $a = LINQ::Linq(array("a", "b", "c"));
        $con = $a->Contains("b");
        $this->AssertEquals($con, TRUE);
    }

    public function testContainsWithComparer()
    {
        $a = LINQ::Linq(array("a", "b", "c"));
        $con = $a->Contains("B", function($a, $b)
            {
                return strcasecmp($a, $b);
            });
        $this->AssertEquals($con, TRUE);
    }

    public function testSequenceEquals()
    {
        $a = LINQ::Linq(array(1, 2, 3, 4, 5));
        $b = array(1, 2, 3, 4, 5);
        $this->AssertTrue($a->SequenceEqual($b));
    }

    public function testZip()
    {
        $a = LINQ::Linq(array(1, 2, 3, 4, 5, 6));
        $b = LINQ::Linq(array(1, 2, 3, 4, 5));
        $i = $a->Zip($b, function($i, $j)
            {
                return $i + $j;
            });
        $this->AssertIteratorsEqual(array(2, 4, 6, 8, 10), $i);
    }

    public function testSelfReference()
    {
        $a = LINQ::Linq(array(1, 2, 3, 4, 5));
        $i = $a->Zip($a, function($i, $j)
            {
                return $i . ":" . $j;
            });
        $this->AssertIteratorsEqual($i, array("1:1", "2:2", "3:3", "4:4", "5:5"));
    }
}