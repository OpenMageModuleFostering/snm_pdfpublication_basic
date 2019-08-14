<?php


/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

$table = $installer->getConnection()
->newTable($installer->getTable('auit_publicationbasic/templates'))
->addColumn('template_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
		'identity'  => true,
		'unsigned'  => true,
		'nullable'  => false,
		'primary'   => true,
), 'Entity Id')
->addColumn('type', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
		'nullable'  => true,
), 'Type')
->addColumn('created_time', Varien_Db_Ddl_Table::TYPE_DATETIME, null, array(
		'nullable'  => true,
), 'created_time')
->addColumn('created_from', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
		'nullable'  => true,
), 'created_from')
->addColumn('update_time', Varien_Db_Ddl_Table::TYPE_DATETIME, null, array(
		'nullable'  => true,
), 'update_time')
->addColumn('update_from', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
		'nullable'  => true,
), 'update_from')
->addColumn('status', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
		'nullable'  => false,'default'=>0
), 'status')
->addColumn('name', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
		'nullable'  => true,
), 'name')
->addColumn('data', Varien_Db_Ddl_Table::TYPE_TEXT, '10m', array(
		'nullable'  => true,
), 'data')

->setComment('Auit publication templates');
if ( !$installer->getConnection()->isTableExists($installer->getTable('auit_publicationbasic/templates')) )
	$installer->getConnection()->createTable($table);

//////////////////////////////////////////////
$table = $installer->getConnection()
->newTable($installer->getTable('auit_publicationbasic/projects'))
->addColumn('project_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
		'identity'  => true,		'unsigned'  => true,		'nullable'  => false,		'primary'   => true,
), 'Entity Id')
->addColumn('type', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
		'nullable'  => true,
), 'Type')
->addColumn('created_time', Varien_Db_Ddl_Table::TYPE_DATETIME, null, array(
		'nullable'  => true,
), 'created_time')
->addColumn('created_from', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
		'nullable'  => true,
), 'created_from')
->addColumn('update_time', Varien_Db_Ddl_Table::TYPE_DATETIME, null, array(
		'nullable'  => true,
), 'update_time')
->addColumn('update_from', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
		'nullable'  => true,
), 'update_from')
->addColumn('status', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
		'nullable'  => false,'default'=>0
), 'status')
->addColumn('name', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
		'nullable'  => true,
), 'name')
->addColumn('data_based', Varien_Db_Ddl_Table::TYPE_TEXT, '64k', array(
		'nullable'  => true,
), 'data')
->addColumn('data', Varien_Db_Ddl_Table::TYPE_TEXT, '10m', array(
		'nullable'  => true,
), 'data')
->setComment('Auit publication projects');
if ( !$installer->getConnection()->isTableExists($installer->getTable('auit_publicationbasic/projects')) )
	$installer->getConnection()->createTable($table);
//////////////////////////
$table = $installer->getConnection()
->newTable($installer->getTable('auit_publicationbasic/styles'))
->addColumn('style_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
		'identity'  => true,		'unsigned'  => true,		'nullable'  => false,		'primary'   => true,
), 'Entity Id')
->addColumn('type', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
		'nullable'  => true,
), 'Type')
->addColumn('created_time', Varien_Db_Ddl_Table::TYPE_DATETIME, null, array(
		'nullable'  => true,
), 'created_time')
->addColumn('created_from', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
		'nullable'  => true,
), 'created_from')
->addColumn('update_time', Varien_Db_Ddl_Table::TYPE_DATETIME, null, array(
		'nullable'  => true,
), 'update_time')
->addColumn('update_from', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
		'nullable'  => true,
), 'update_from')
->addColumn('status', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
		'nullable'  => false,'default'=>0
), 'status')
->addColumn('name', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
		'nullable'  => true,
), 'name')
->addColumn('data', Varien_Db_Ddl_Table::TYPE_TEXT, '10m', array(
		'nullable'  => true,
), 'data')
->setComment('Auit publication styles');
if ( !$installer->getConnection()->isTableExists($installer->getTable('auit_publicationbasic/styles')) )
	$installer->getConnection()->createTable($table);
/////////////////////////////
$table = $installer->getConnection()
->newTable($installer->getTable('auit_publicationbasic/jobqueue'))
->addColumn('jobqueue_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
		'identity'  => true,		'unsigned'  => true,		'nullable'  => false,		'primary'   => true,
), 'Entity Id')
->addColumn('type', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
		'nullable'  => true,
), 'Type')
->addColumn('variante', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array('nullable'  => true), 'variante')
->addColumn('created_time', Varien_Db_Ddl_Table::TYPE_DATETIME, null, array(
		'nullable'  => true,
), 'created_time')
->addColumn('created_from', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
		'nullable'  => true,
), 'created_from')
->addColumn('update_time', Varien_Db_Ddl_Table::TYPE_DATETIME, null, array(
		'nullable'  => true,
), 'update_time')
->addColumn('update_from', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
		'nullable'  => true,
), 'update_from')
->addColumn('status', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
		'nullable'  => false,'default'=>0
), 'status')
->addColumn('prio', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
		'nullable'  => false,'default'=>0
), 'prio')
->addColumn('name', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
		'nullable'  => true,
), 'name')
->addColumn('queue_start_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
), 'Queue Start At')
->addColumn('queue_finish_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
), 'Queue Finish At')
->addColumn('queue_status', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
		'nullable'  => true,
), 'Queue Status')
->addColumn('data', Varien_Db_Ddl_Table::TYPE_TEXT, '10m', array(
		'nullable'  => true,
), 'data')
->setComment('Auit publication jobqueue');
if ( !$installer->getConnection()->isTableExists($installer->getTable('auit_publicationbasic/jobqueue')) )
	$installer->getConnection()->createTable($table);
/////////////////////////////////////////////
$installer->getConnection()->addColumn($installer->getTable('auit_publicationbasic/jobqueue'), 'identifier', array(
		'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
		'length' => 256,'nullable' => true,'default' => null,'comment' => 'UniqID'
));
$installer->getConnection()->addColumn($installer->getTable('auit_publicationbasic/templates'), 'identifier', array(
		'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
		'length' => 256,'nullable' => true,'default' => null,'comment' => 'UniqID'
));
$installer->getConnection()->addColumn($installer->getTable('auit_publicationbasic/styles'), 'identifier', array(
		'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
		'length' => 256,'nullable' => true,'default' => null,'comment' => 'UniqID'
));
$installer->getConnection()->addColumn($installer->getTable('auit_publicationbasic/projects'), 'identifier', array(
		'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
		'length' => 256,'nullable' => true,'default' => null,'comment' => 'UniqID'
));

$table = $installer->getConnection()
->newTable($installer->getTable('auit_publicationbasic/generator'))
->addColumn('generator_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
		'identity'  => true,		'unsigned'  => true,		'nullable'  => false,		'primary'   => true,
), 'Entity Id')
->addColumn('type', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(	'nullable'  => true,), 'Type')
->addColumn('created_time', Varien_Db_Ddl_Table::TYPE_DATETIME, null, array('nullable'  => true,), 'created_time')
->addColumn('created_from', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(	'nullable'  => true,), 'created_from')
->addColumn('update_time', Varien_Db_Ddl_Table::TYPE_DATETIME, null, array(	'nullable'  => true,), 'update_time')
->addColumn('update_from', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array('nullable'  => true,), 'update_from')
->addColumn('status', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array('nullable'  => false,'default'=>0), 'status')
->addColumn('name', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array('nullable'  => true,), 'name')
->addColumn('identifier', Varien_Db_Ddl_Table::TYPE_TEXT, '256', array('nullable'  => true,'default'=>null), 'UniqID')
->addColumn('source', Varien_Db_Ddl_Table::TYPE_TEXT, '64k', array(), 'source')
->addColumn('data', Varien_Db_Ddl_Table::TYPE_TEXT, '64k', array('nullable'  => true,), 'data')
->setComment('Auit publication generator');
if ( !$installer->getConnection()->isTableExists($installer->getTable('auit_publicationbasic/generator')) )
	$installer->getConnection()->createTable($table);
/////////////////////////////////////////
$installer->getConnection()->addColumn($installer->getTable('auit_publicationbasic/templates'), 'dependence', array(
		'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
		'length' => '64k','nullable' => true,'default' => null,'comment' => 'dependence'
));


///////////////////////////////////
$installer->getConnection()->addColumn($installer->getTable('auit_publicationbasic/templates'), 'serie', array(
		'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
		'length' => '255','nullable' => true,'default' => null,'comment' => 'serie'
));
$installer->getConnection()->addColumn($installer->getTable('auit_publicationbasic/projects'), 'serie', array(
		'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
		'length' => '255','nullable' => true,'default' => null,'comment' => 'serie'
));
$installer->getConnection()->addColumn($installer->getTable('auit_publicationbasic/jobqueue'), 'serie', array(
		'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
		'length' => '255','nullable' => true,'default' => null,'comment' => 'serie'
));
$installer->getConnection()->addColumn($installer->getTable('auit_publicationbasic/styles'), 'serie', array(
		'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
		'length' => '255','nullable' => true,'default' => null,'comment' => 'serie'
));

//////////////////////////////
$installer->getConnection()->addColumn($installer->getTable('auit_publicationbasic/generator'), 'serie', array(
		'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
		'length' => '255','nullable' => true,'default' => null,'comment' => 'serie'
));
$installer->getConnection()->addColumn($installer->getTable('auit_publicationbasic/generator'), 'parameter', array(
		'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
		'length' => '64k','nullable' => true,'default' => null,'comment' => 'parameter'
));
///////
$installer->getConnection()->addColumn($installer->getTable('auit_publicationbasic/templates'), 'istoplevel', array(
		'type' => Varien_Db_Ddl_Table::TYPE_INTEGER,
		'nullable'  => true,'comment' => 'IsTopLevel'
));

$installer->endSetup();
