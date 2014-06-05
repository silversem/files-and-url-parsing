<?php
/**
 * File: ParseFile.php
 * User: Pavlov Semyen
 * Date: 5/29/14
 * Time: 1:41 PM
 */

/**
 * Class ParseFile - for parsing file with packets.
 */
class ParseFile
{
    const DOUBLE_PRECISION_SHIFT = 1023;
    const EXTENDED_PRECISION_SHIFT = 16383;

    private $_fileName;

    /**
     * Construct object with filename.
     * @param string $fileName
     */
    public function __construct($fileName)
    {
        $this->_fileName = $fileName;
    }

    /**
     * Reading of file by bytes and processing.
     * @return array
     */
    function parseBinFile()
    {
        $packets = array();

        $handle = fopen($this->_fileName, 'r');
        while (!feof($handle)) {
            $currentPacket = array();
            $currentPacket['doublePrecision'] = $this->parseDoublePrecision($this->stringToBin(fread($handle, 8)));
            $currentPacket['firstExtendedPrecision'] = $this->parseExtendedPrecision($this->stringToBin(fread($handle, 10)));
            fseek($handle, 6, SEEK_CUR);
            $currentPacket['secondExtendedPrecision'] = $this->parseExtendedPrecision($this->stringToBin(fread($handle, 10)));
            fseek($handle, 6, SEEK_CUR);
            $currentPacket['dataSize'] = bindec($this->stringToBin(fread($handle, 1)));
            $currentPacket['data'] = $this->unpackData(fread($handle, $currentPacket['dataSize']));
            $packets[] = $currentPacket;
        }
        fclose($handle);

        return $packets;
    }

    /**
     * Getting double precision from binary.
     * @param string $doublePrecision
     * @return array - symbolic and bindec forms.
     */
    function parseDoublePrecision($doublePrecision)
    {
        if (strlen($doublePrecision) != 64) {
            echo 'Wrong size for double.';
            die();
        }

        $sign = $doublePrecision[0] == 0 ? '' : '-';
        $exponent = bindec(substr($doublePrecision, 1, 11)) - self::DOUBLE_PRECISION_SHIFT;
        $fraction = 1 + $this->fractionalBin2Dec(substr($doublePrecision, 12, 52));

        return array(
            'result_symbolic' => $sign . $fraction . ' * 2^' . $exponent,
            'result_bindec' => bindec($doublePrecision)
        );
    }

    /**
     * Getting extended precision from binary.
     * @param string $extendedPrecision
     * @return array - symbolic and bindec forms.
     */
    function parseExtendedPrecision($extendedPrecision)
    {
        if (strlen($extendedPrecision) != 80) {
            echo 'Wrong size for extended.';
            die();
        }

        $sign = $extendedPrecision[0] == 0 ? '' : '-';
        $exponent = bindec(substr($extendedPrecision, 1, 15)) - self::EXTENDED_PRECISION_SHIFT;
        $fraction = 1 + $this->fractionalBin2Dec(substr($extendedPrecision, 16, 64));

        return array(
            'result_symbolic' => $sign . $fraction . ' * 2^' . $exponent,
            'result_bindec' => bindec($extendedPrecision)
        );
    }

    /**
     * Converting fractional part from bin to dec.
     * @param string $fraction
     * @return float|number
     */
    function fractionalBin2Dec($fraction)
    {
        $dec = 0.0;
        $len = strlen($fraction);
        for ($i = 0; $i < $len; $i++) {
            $dec += pow(2, -$i-1) * $fraction[$i];
        }

        return $dec;
    }

    /**
     * Converting string to binary string.
     * @param string $buffer
     * @return string $binaryBuffer - binary string
     */
    function stringToBin($buffer)
    {
        $binaryBuffer = '';
        $length = strlen($buffer);
        for ($i = 0; $i < $length; $i++) {
            $binaryBuffer .= sprintf("%08b", ord($buffer[$i]));
        }

        return $binaryBuffer;
    }

    /**
     * Unpack data from binary string.
     * @param string $buffer - binary string
     * @return array
     */
    function unpackData($buffer)
    {
        return unpack('H*', $buffer);
    }

    /**
     * Printing result in nice view.
     * @return bool
     */
    function printPackets()
    {
        echo('<pre>');

        $packets = $this->parseBinFile();
        foreach ($packets as $key=>$item) {
            echo('<b><i>Packet ' . $key . ':</i></b><br>');
            var_dump($item);
            echo('<br>');
        }
        echo('(Total ' . count($packets) . ' packets in file "' . $this->_fileName .'")<br>');

        return true;
    }

}
