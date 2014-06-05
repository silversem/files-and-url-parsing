<?php
/**
 * File: ChooseN.php
 * User: Pavlov Semyen
 * Date: 5/29/14
 * Time: 1:41 PM
 */

/**
 * Class ChooseN - for generation sequence of combinations with N variants.
 */
class ChooseN
{
    protected $_n;

    /**
     * Construct object with number of variants.
     * @param int $n - number of variants.
     */
    public function __construct($n = 2)
    {
        if (($n < 2) or ($n > 6)) {
            echo('Error! N should be between 2 and 6.');
            die();
        }
        $this->_n = $n;
        $this->_combinations = pow($n, $n);
    }

    /**
     * Generating sequence of combinations.
     * @return array
     */
    function getList()
    {
        $out = array();
        for($i = 0; $i < $this->_combinations; $i++) {
            $curCombination = str_split(sprintf("%0" . $this->_n . "d", base_convert($i, 10, $this->_n)));
            array_walk($curCombination, create_function('&$value, $key', '$value = $value + 1;'));
            $out[] = implode('', $curCombination);
        }

        return $out;
    }

    /**
     * Printing result in nice view.
     * @return bool
     */
    function printList()
    {
        echo('<pre>');

        foreach ($this->getList() as $item) {
            echo($item . '<br>');
        }
        echo('(Total ' . $this->_combinations . ' strings for N=' . $this->_n .')<br>');

        return true;
    }

}
