<?php

class AuIt_PublicationBasic_Model_Resource_Jobqueue extends AuIt_PublicationBasic_Model_Resource_Abstract
{
	
    public function _construct()
    {
        $this->_init('auit_publicationbasic/jobqueue', 'jobqueue_id');
    }
    public function getBlobFields()
    {
    	return array('salesrule_rule','template','template2','use_doc_size','user_page_size','user_page_orientation','placment_method','use_bleed','catalog_skus','print_store','layout_def');
    }
    public function getJobRoot()
    {
    	return Mage::getBaseDir('media').DS.'snm-portal'.DS.'publication'.DS.'jobs';
    }
    public function getJobFilePath(Mage_Core_Model_Abstract $object)
    {
    	return $this->getJobRoot().DS.'job_'.$object->getId();
    }
    public function getFilename(Mage_Core_Model_Abstract $object)
    {
    	if ( $object->getVariante() == AuIt_PublicationBasic_Model_Jobqueue::TYPE_EBOOK_EPUB3 )
    		return 'ebook_'.$object->getId().'.epub';
    	 
    	return 'job_'.$object->getId().'.pdf';
    }
    protected function _beforeSave(Mage_Core_Model_Abstract $object)
    {
    	$this->_saveBlobData($object,$this->getBlobFields());    	 
    	return parent::_beforeSave($object);
    }
    protected function _afterLoad(Mage_Core_Model_Abstract $object)
    {
    	$this->_loadBlobData($object,$this->getBlobFields());    	 
    	return parent::_afterLoad($object);
    }
}