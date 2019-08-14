<?php
class AuIt_PublicationBasic_Model_Resource_Styles_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
	static $_sortField;
    protected function _construct()
    {
        $this->_init('auit_publicationbasic/styles');
    }

    public function toOptionArray()
    {
        return $this->_toOptionArray('style_id', 'name');
    }
}
