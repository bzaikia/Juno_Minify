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
     * @param $file
     */
    public function log($file)
    {
        $resource = Mage::getSingleton('core/resource');
        /**
         * @var $writeAdapter Magento_Db_Adapter_Pdo_Mysql
         */
        $writeAdapter = $resource->getConnection('core_write');
        $data = array(
            'path' => $file,
            'hash' => md5_file($file)
        );

        $writeAdapter->delete($resource->getTableName('juno_minify'), 'path = "'.$file.'"');
        $writeAdapter->insert($resource->getTableName('juno_minify'), $data);
        Mage::log($file, null, self::LOG_FILE);
    }

    /**
     * @param $fileUrl
     * @return string
     */
    public function getMinifyFile($fileUrl)
    {
        $host = Mage::getStoreConfig(self::PATH_HOST);
        return 'http://' . $host . '/?file=' . $fileUrl;
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
     * @param $normalFilePath
     * @return bool
     */
    public function getMinifiedFile($normalFilePath)
    {
        if ((strpos($normalFilePath, ".css") !== false) && file_exists(str_replace('.css', '.junominify.css', $normalFilePath))) {
            return str_replace('.css', '.junominify.css', $normalFilePath);
        }

        if ((strpos($normalFilePath, ".js") !== false) && file_exists(str_replace('.js', '.junominify.js', $normalFilePath))) {
            return str_replace('.js', '.junominify.js', $normalFilePath);
        }
        return false;
    }

    /**
     * @param $item1
     */
    protected function _alterMinifiedStuff(&$item1)
    {
        if (file_exists($item1)) {
            $isSecure = Mage::app()->getStore()->isCurrentlySecure();
            $item1 = str_replace(Mage::getBaseDir() . DS, Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB, $isSecure), $item1);
        }
    }

    /**
     * @return boolean
     */
    public function isEnable()
    {
        return Mage::getStoreConfig(self::PATH_ENABLE);
    }
}