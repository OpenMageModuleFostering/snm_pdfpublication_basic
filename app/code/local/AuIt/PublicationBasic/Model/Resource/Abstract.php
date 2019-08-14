<?php

abstract class AuIt_PublicationBasic_Model_Resource_Abstract extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _beforeSave(Mage_Core_Model_Abstract $object)
	{
    	$UsrName = 'System';
    	$editor = Mage::getSingleton('admin/session')->getUser();
    	if ( $editor &&  $editor->getName() )
    		$UsrName = $editor->getName();
    	if ( !$object->getId() )
    	{
    		$object->setData('creation_time',now());
    		$object->setData('creation_from',$UsrName);
    	}
    	if ( !$object->getIdentifier() )
    		$object->setIdentifier(md5(uniqid(rand(), true)));
    	$object->setData('update_time',now());
    	$object->setData('update_from',$UsrName);
    	
    	return parent::_beforeSave($object);
    }
    protected function _saveBlobData(Mage_Core_Model_Abstract $object,$fields)
	{
		$blob = (string)$object->getData('data');
		$data=(array)@unserialize($blob);
		foreach ( $fields as $blobField)
		{
			if (!is_null($object->getData($blobField)) )
				$data[$blobField]=(string)$object->getData($blobField);
		}
		$object->setData('data',(string)@serialize($data));
	}
    protected function _loadBlobData(Mage_Core_Model_Abstract $object,$fields)
	{
		$blob = (string)$object->getData('data');
		$data=(array)@unserialize($blob);
		foreach ( $fields as $blobField)
		{
			if ( isset($data[$blobField]) )
				$object->setData($blobField,$data[$blobField]);
		}
		$object->setData('extension','');
		if ( is_file($this->getJobFilePath($object).DS.$this->getFilename($object)))
			$object->setData('extension',$this->getFilename($object));
	}
	public function load(Mage_Core_Model_Abstract $object, $value, $field = null)
	{
		if (!is_numeric($value) && is_null($field)) {
			$field = 'identifier';
		}
		return parent::load($object, $value, $field);
	}
}