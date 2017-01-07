<?php
/**
 * Author: Hieu Nguyen
 */
require_once 'abstract.php';

class Minify_Stuff extends Mage_Shell_Abstract
{
    public function run()
    {
        Mage::getModel('juno_minify/observer')->minifyStuff();
    }
}

$shell = new Minify_Stuff();
$shell->run();