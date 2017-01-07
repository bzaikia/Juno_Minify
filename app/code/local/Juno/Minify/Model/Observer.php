<?php

/**
 * Author: Hieu Nguyen
 */
class Juno_Minify_Model_Observer
{
    public function minifyStuff()
    {
        $stuff = array_merge($this->_getStuffPath(Mage::getBaseDir('skin')), $this->_getStuffPath(Mage::getBaseDir('media')));
        foreach ($stuff as $file) {
            $file = array_shift($file);
            if ($minified = $this->_getMinifiedData($file)) {
                if ($minified['hash'] == md5_file($file)) continue;
            }
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
            if (is_null(strpos($file, '.junominify'))) {
                $result[] = $file;
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