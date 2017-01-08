<?php

/**
 * Author: Hieu Nguyen
 */
class Juno_Minify_Helper_Data extends Mage_Core_Helper_Abstract
{
    const PATH_HOST = 'juno_jscss/general/host';
    const PATH_ENABLE = 'juno_jscss/general/enabled';
    const LOG_FILE = 'juno_minify.log';

    protected $_minifiedStuff;
    /**
     * @param $file
     */
    public function minify($file)
    {
        $fileUrl = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB) . $file;
        if ($filePath = $this->getMinifiedFilePath(Mage::getBaseDir() . DS . $file)) {
            file_put_contents($filePath, file_get_contents($this->getMinifyFile($fileUrl)));
            $this->log(Mage::getBaseDir() . DS . $file);
        }
    }

    /**
     * @param $file
     * @return mixed|string
     */
    public function getMinifiedFilePath($file)
    {
        if (strpos($file, '.css')) {
            return str_replace('.css', '.junominify.css', $file);
        }

        if (strpos($file, '.js')) {
            return str_replace('.js', '.junominify.js', $file);
        }

        return '';
    }

    /**
     * @param $img
     */
    public function log($img)
    {
        $resource = Mage::getSingleton('core/resource');
        /**
         * @var $writeAdapter Magento_Db_Adapter_Pdo_Mysql
         */
        $writeAdapter = $resource->getConnection('core_write');
        $data = array(
            'path' => $img,
            'hash' => md5_file($img)
        );
        $writeAdapter->delete($resource->getTableName('juno_minify'), array('md5_file' => md5($img)));
        $writeAdapter->insert($resource->getTableName('juno_minify'), $data);
        Mage::log($img, null, self::LOG_FILE);
    }

    /**
     * @param $fileUrl
     * @return string
     */
    public function getMinifyFile($fileUrl)
    {
        $host = Mage::getStoreConfig(self::PATH_HOST);
        return 'http://' . $host . '?file=' . $fileUrl;
    }

    /**
     * @return array
     */
    public function getMinifiedStuff()
    {
        if (!isset($this->_minifiedStuff)) {
            $resource = Mage::getSingleton('core/resource');
            /**
             * @var $writeAdapter Magento_Db_Adapter_Pdo_Mysql
             */
            $writeAdapter = $resource->getConnection('core_write');
            $select = $writeAdapter->select()->from($resource->getTableName('juno_minify'), 'path');

            $this->_minifiedStuff = $writeAdapter->fetchCol($select);
            array_walk($this->_minifiedStuff, array($this, '_alterMinifiedStuff'));
        }

        return $this->_minifiedStuff;
    }

    /**
     * @param $item1
     */
    protected function _alterMinifiedStuff(&$item1)
    {
        $isSecure = Mage::app()->getStore()->isCurrentlySecure();
        $item1 = str_replace(Mage::getBaseDir() . DS,  Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB, $isSecure), $item1);

    }

    /**
     * @return boolean
     */
    public function isEnable()
    {
        return Mage::getStoreConfig(self::PATH_ENABLE);
    }
}