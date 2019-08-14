<?php
class AuIt_PublicationBasic_Model_Resource_Generator_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
	static $_sortField;
    protected function _construct()
    {
        $this->_init('auit_publicationbasic/generator');
       
    }
    
    public function toOptionArray()
    {
        return $this->_toOptionArray('generator_id', 'name');
    }
    protected function _beforeLoad()
    {
   // 	$this->walk('afterLoad');
    	return parent::_beforeLoad();
    }
    
    protected function _afterLoad()
    {
    	parent::_afterLoad();
    	foreach ($this->_items as $item) {
    		$item->afterLoad();
    	}
    	return $this;
    	 
    }
}
