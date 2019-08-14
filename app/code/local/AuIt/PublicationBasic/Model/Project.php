<?php

class AuIt_PublicationBasic_Model_Project extends Mage_Core_Model_Abstract
{
    const CACHE_TAG     = 'auit_publicationbasic_project';
    protected $_cacheTag= 'auit_publicationbasic_project';
    protected function _construct()
    {
        $this->_init('auit_publicationbasic/project');
    }
    public function initialBasisData()
    {
    	if ( $this->getId() ) return;
    	$file   = Mage::getModuleDir('data', 'AuIt_Publication') . DS . 'initial'.DS.'project'.DS.'type_'.$this->getType().'.json';
    	if ( file_exists($file) )
    	{
			$this->setData('data',file_get_contents($file));
    	}
    }
 }