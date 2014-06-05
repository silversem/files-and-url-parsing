<?php
/**
 * File: ChooseNTest.php
 * User: Pavlov Semyen
 * Date: 5/29/14
 * Time: 1:41 PM
 */

require './Includes/ChooseN.php';

/**
 * Class ChooseNTest - class for testing ChooseN.
 * Example of how to run test:
 * phpunit --verbose ./Tests/ChooseNTest
 */
class ChooseNTest extends PHPUnit_Framework_TestCase
{
    public function testCombinationLength()
    {
        $n = 3;
        $t3 = new ChooseN($n);
        $res = $t3->getList();
        //checking count of combinations
        $this->assertEquals(27, count($res));
        //checking first element
        $this->assertEquals('111', $res[0]);
        //checking last element
        $this->assertEquals('333', $res[26]);
    }
}
