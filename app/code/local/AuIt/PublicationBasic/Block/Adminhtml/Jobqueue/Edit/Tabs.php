<?php
class AuIt_PublicationBasic_Block_Adminhtml_Jobqueue_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('page_tabs');
        $this->setDestElementId('edit_form');
       	$this->setTitle(Mage::helper('auit_publicationbasic')->__('Manage Project'));
    }
    protected function _prepareLayout()
    {
    	if (Mage::getSingleton('adminhtml/session')->getNewAuitPublication()) {
    		$this->addTab('set', array(
    				'label'     => $this->__('Settings'),
    				'content'   => $this->getLayout()
    				->createBlock('auit_publicationbasic/adminhtml_jobqueue_edit_tab_settings')
    				->toHtml(),
    				'active'    => true
    		));
    	}
    	
    	return parent::_prepareLayout();
    }

}
