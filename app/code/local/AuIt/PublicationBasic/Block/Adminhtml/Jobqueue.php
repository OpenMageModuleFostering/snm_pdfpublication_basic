<?php
class AuIt_PublicationBasic_Block_Adminhtml_Jobqueue extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
    	$this->_blockGroup = 'auit_publicationbasic';
        $this->_controller = 'adminhtml_jobqueue';
        $this->_headerText = Mage::helper('auit_publicationbasic')->__('Job Queue');
        //$this->_addButtonLabel = Mage::helper('auit_publicationbasic')->__('Add New Project');

        parent::__construct();
        $this->_removeButton('add');
    }
}
