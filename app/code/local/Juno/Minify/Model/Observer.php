<?php

/**
 * Author: Hieu Nguyen
 */
class Juno_Minify_Model_Observer
{
    /**
     * start minifying
     */
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

            // if minified is not exist, then generate the minified one
            $minifiedFile = Mage::helper('juno_minify')->getMinifiedFile($file);
            if (empty($minifiedFile)) {
                $result[] = $file;
                continue;
            }

            // if minified one is exist, but the original file is changed, then re-minify it
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

    public function clean()
    {
        $resource = Mage::getSingleton('core/resource');
        $sql = $this->_getWriteAdapter()
            ->select()->from($resource->getTableName('juno_minify'));
        $result = $this->_getWriteAdapter()->fetchAll($sql);
        foreach ($result as $item) {
            if (!file_exists($item['path'])) {
                $this->_getWriteAdapter()
                    ->delete($resource->getTableName('juno_minify'), 'path = "' . $item['path'] . '"');
            }
        }
    }

    /**
     * @return Magento_Db_Adapter_Pdo_Mysql
     */
    protected function _getWriteAdapter()
    {
        $resource = Mage::getSingleton('core/resource');
        /**
         * @var $writeAdapter Magento_Db_Adapter_Pdo_Mysql
         */
        $writeAdapter = $resource->getConnection('core_write');
        return $writeAdapter;
    }
}