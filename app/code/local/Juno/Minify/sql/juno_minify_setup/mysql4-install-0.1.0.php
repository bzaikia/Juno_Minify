<?php
/**
 * Author: Hieu Nguyen
 */
/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

$installer->run("
DROP TABLE IF EXISTS {$this->getTable('juno_minify')};
");

$table = $installer->getConnection()
    ->newTable($installer->getTable('juno_minify'))
    ->addColumn('id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
    ), 'Id')
    ->addColumn('path', Varien_Db_Ddl_Table::TYPE_VARCHAR, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '',
    ), 'path')
    ->addColumn('hash', Varien_Db_Ddl_Table::TYPE_VARCHAR, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '',
    ), 'path');

$installer->getConnection()->createTable($table);

$installer->endSetup();