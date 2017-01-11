<?php

/**
 * Author: Hieu Nguyen
 */
class Juno_Minify_Model_Observer
{
    public function minifyStuff()
    {
        if (!Mage::helper('juno_minify')->isEnable()) {
            return;
        }
        if (file_get_contents('http://' . Mage::getStoreConfig(Juno_Minify_Helper_Data::PATH_HOST)) != 'ok') {
            return;
        }
        $jsPath = Mage::getBaseDir() . DS . 'js';
        $stuff = array_merge($this->_getStuffPath(Mage::getBaseDir('skin')), $this->_getStuffPath($jsPath));
        foreach ($stuff as $file) {
            $file = str_replace(Mage::getBaseDir() . DS, '', $file);
            Mage::helper('juno_minify')->minify($file);
        }
    }

    /**
     * @param $folder
     */
    protected function _getStuffPath($folder)
    {
        $result = array();
        $directory = new RecursiveDirectoryIterator($folder);
        $iterator = new RecursiveIteratorIterator($directory);
        $files = new RegexIterator($iterator, '/^.+\.(css|js)$/i', RecursiveRegexIterator::GET_MATCH);
        foreach ($files as $file) {
            $file = array_shift($file);
            if (strpos($file, '.junominify.') !== false) {
                continue;
            }
            $hashData = $this->_getMinifiedData($file);
            $minifiedFile = Mage::helper('juno_minify')->getMinifiedFile($file);
            if (!$minifiedFile) {
                $result[] = $file;
                continue;
            }

            if (!empty($hashData['hash']) && ($hashData['hash'] != md5_file($file))) {
                $result[] = $file;
                continue;
            }
        }

        return $result;
    }

    /**
     * @param $path
     * @return mixed
     */
    public function _getMinifiedData($path)
    {
        $resource = Mage::getSingleton('core/resource');
        /**
         * @var $writeAdapter Magento_Db_Adapter_Pdo_Mysql
         */
        $writeAdapter = $resource->getConnection('core_write');
        $select = $writeAdapter->select()->from($resource->getTableName('juno_minify'))
            ->where('path = ?', $path);

        return $writeAdapter->fetchRow($select);
    }
}