<?php

class AuIt_PublicationBasic_Model_Generator extends Mage_Core_Model_Abstract
{
	const TYPE_PHTML = 1;
	const TYPE_MARKUP = 2;


    protected function _construct()
    {
        $this->_init('auit_publicationbasic/generator');
    }
    public function initialBasisData()
    {
    	if ( $this->getId() ) return;
    	$file   = Mage::getModuleDir('data', 'AuIt_Publication') . DS . 'initial'.DS.'style'.DS.'type_'.$this->getType().'.json';
    	if ( file_exists($file) )
    	{
    		$this->setData('source',file_get_contents($file));
    	}
    }
    protected function _beforeSave()
    {
    	$data = $this->getData('parameter');
    	if ( is_array($data) )
    	{
    		$_deleteItems = array();
    		if ( isset($data['__deleted']) )
    		{
    			foreach ( $data['__deleted'] as $delItem => $b)
    			{
    				if ( $b )
    					unset($data[$delItem]);
    			}
    		}
    		if ( isset($data['__deleted']) )
    			unset($data['__deleted']);
    		if ( isset($data['__empty']) )
    			unset($data['__empty']);
    		$this->setData('parameter','base64:'.base64_encode(serialize($data)));
    	}
    	return parent::_beforeSave();
    }
    protected function _afterLoad()
    {
    	$data = $this->getData('parameter');
    	if ( !is_array($data) )
    	{
    		if ( strpos($data,'base64:') === 0 )
    		{
    			$data = base64_decode(substr($data,7));
    		}
    		$data=@unserialize($data);
    		$this->setData('parameter',$data);
    	}
    	return parent::_afterLoad();
    }
    
    public function exportTo(&$package,$rootDir,$templId)
    {
    	$Identifier = $this->getIdentifier();
    	if ( isset($package['generators'][$Identifier] ) )
    		return;
    	 
    	$data = $this->getData();
    	$data['generator_id']=null;
    	file_put_contents($rootDir.DS.'generator_'.$Identifier.'.ser', serialize($data));
    	$package['generators'][$Identifier]=1;
    }
    public function importData(&$messages,$templateDir,$templdata,$bvalidate)
    {
    	if ( isset($templdata['generator_id']) )
    		unset($templdata['generator_id']);
    	$this->addData($templdata);
    }
    public function addDependence(&$dep)
    {
    
    }
    
 }