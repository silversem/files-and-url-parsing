<?php
/**
 * File: index.php
 * User: Pavlov Semyen
 * Date: 5/29/14
 * Time: 1:41 PM
 */
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
@ini_set('display_errors', '1');

require './Includes/ParseFile.php';
require './Includes/ChooseN.php';
require './Includes/ParseURL.php';


/**
 * URLs for testing.
 *
 * First task:
 * http://.../?task=1
 *
 * Second task:
 * http://.../?task=2&n=2 
 * http://.../?task=2&n=3
 * http://.../?task=2&n=4
 * http://.../?task=2&n=5
 * http://.../?task=2&n=6
 *
 * Third task:
 * http://.../?task=3&url=http://ria.ru/incidents/20140530/1009986601.html
 * http://.../?task=3&url=http://lenta.ru/news/2014/05/30/mswatch/
 * http://.../?task=3&url=http://sport.mail.ru/hockey2014/news/18381208/
 * http://.../?task=3&url=http://club443.ru/&searchTag=table
 *
 */

$task = !empty($_GET['task']) ? $_GET['task'] : 1;

switch ($task) {
    case 1:
        $t1 = new ParseFile("./Bootfont.bin");
        $t1->printPackets();
        break;
    case 2:
        $n = !empty($_GET['n']) ? $_GET['n'] : 2;
        $t2 = new ChooseN($n);
        $t2->printList();
        break;
    case 3:
        $url = !empty($_GET['url']) ? $_GET['url'] : 'http://forum.exler.ru';
        $searchTag = !empty($_GET['searchTag']) ? $_GET['searchTag'] : 'div';
        $t3 = new ParseURL($url, $searchTag);
        $t3->printContent();
        break;
}

