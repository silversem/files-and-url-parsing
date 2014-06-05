<?php
/**
 * File: ParseURL.php
 * User: Pavlov Semyen
 * Date: 5/29/14
 * Time: 1:41 PM
 */

class ParseURL
{
    const DIV_OUTPUT_DEPTH = 1;
    const SEARCH_TAG_DIV = 'div';

    private $_url;
    private $_searchTag;
    private $_tagToRemove = array('script', 'link', 'meta', 'footer', 'section');

    /**
     * Construct takes up to two parameters:
     * @param string $url
     * @param string $searchTag
     */
    public function __construct($url, $searchTag = 'div')
    {
        $this->_url = $url;
        $this->_searchTag = $searchTag;
    }

    /**
     * Main function for parsing URL. Returns "main" content on HTML page.
     * @return string
     */
    function parsePage()
    {
        $page = $this->loadPage();

        $dom = new DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML(mb_convert_encoding($page, 'HTML-ENTITIES', "UTF-8"));
        libxml_clear_errors();

        $this->_deleteDustFromHTML($dom);

        if ($this->_searchTag == self::SEARCH_TAG_DIV) {
            $res = $this->_getDivWithDepth($dom, $this->_searchTag);
            $topRatedElement = !empty($res[0]) ? $this->_getParentForOutput($res[0]) : false;
            $res = !empty($topRatedElement->ownerDocument) ? $topRatedElement->ownerDocument->saveHTML($topRatedElement) : 'Empty...';
        } else {
            $res = $this->_getTagWithWeight($dom, $this->_searchTag);
            $topRatedElement = !empty($res[0]['node']) ? $res[0]['node'] : false;
            $res = !empty($topRatedElement->ownerDocument) ? $topRatedElement->ownerDocument->saveHTML($topRatedElement) : 'Empty...';
        }

        return mb_convert_encoding($res, 'HTML-ENTITIES', "UTF-8");
    }

    /**
     * For printing we need to find parent div with most large content.
     * @param $element - array of DOM elements with depth.
     * @return DOMElement
     */
    private function _getParentForOutput($element)
    {
        $depth = $element['depth'];
        $node = $element['node'];

        $nodeWithMaxContent = array('node' => false, 'contentLength' => 0);
        for ($i = $depth; $i > self::DIV_OUTPUT_DEPTH; $i--) {
            $node = $node->parentNode;

            if (($node->tagName == $this->_searchTag) and ($nodeWithMaxContent['contentLength'] < strlen($node->nodeValue))) {
                $nodeWithMaxContent['node'] = $node;
                $nodeWithMaxContent['contentLength'] = strlen($node->nodeValue);
            }
        }

        return $nodeWithMaxContent['node'];
    }

    /**
     * Calculating depth for divs.
     * @param DOMDocument $dom
     * @return array - sorted array of divs with depth.
     */
    private function _getDivWithDepth(DOMDocument $dom)
    {
        $out = array();

        $nodes = array();
        $domNodeList = $dom->getElementsByTagname($this->_searchTag);
        foreach ( $domNodeList as $domElement ) {
            $nodes[] = $domElement;
        }

        foreach ($nodes as $domElement) {
            $out[] = array(
                'node' => $domElement,
                'depth' => $this->_getNodeDepth($domElement)
            );
        }

        usort(
            $out,
            create_function(
                '$a, $b',
                'if($a["depth"] == $b["depth"]) {return 0;} return ($a["depth"] < $b["depth"]) ? 1 : -1;'
            )
        );

        return $out;
    }

    /**
     * Calculating node depth.
     * @param DOMElement $node
     * @return int
     */
    private function _getNodeDepth(DOMElement $node)
    {
        $depth = -1;

        while ($node != null)
        {
            if ($node->tagName != $this->_searchTag) {
                return $depth;
            }
            if ($node->tagName == 'body') {
                return $depth;
            }
            $depth++;
            $node = $node->parentNode;
        }

        return $depth;
    }

    /**
     * Calculating weights of DOM elements.
     * @param DOMDocument $dom
     * @return array - sorted DOM elements with it's weights
     */
    private function _getTagWithWeight(DOMDocument $dom)
    {
        $out = array();

        $nodes = array();
        $domNodeList = $dom->getElementsByTagname($this->_searchTag);
        foreach ( $domNodeList as $domElement ) {
            $nodes[] = $domElement;
        }

        foreach ($nodes as $domElement) {
            $out[] = array(
                'node' => $domElement,
                'contentLength' => strlen($domElement->nodeValue)
            );
        }

        usort(
            $out,
            create_function(
                '$a, $b',
                'if($a["contentLength"] == $b["contentLength"]) {return 0;} return ($a["contentLength"] < $b["contentLength"]) ? 1 : -1;'
            )
        );

        return $out;
    }

    /**
     * Deleting unnecessary tags from dom.
     * @param DOMDocument $dom
     */
    private function _deleteDustFromHTML(DOMDocument $dom)
    {
        $nodesToRemove = array();
        foreach ($this->_tagToRemove as $tag) {
            $domNodeList = $dom->getElementsByTagname($tag);
            foreach ( $domNodeList as $domElement ) {
                $nodesToRemove[] = $domElement;
            }
        }
        foreach ($nodesToRemove as $domElement) {
            $domElement->parentNode->removeChild($domElement);
        }
    }

    /**
     * Getting page with curl.
     * @return mixed
     */
    function loadPage()
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $this->_url);
        //curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $res = curl_exec ($ch);

        curl_close ($ch);

        return $res;
    }

    /**
     * Printing result.
     * @return bool
     */
    function printContent()
    {
        echo($this->parsePage());

        return true;
    }

}
