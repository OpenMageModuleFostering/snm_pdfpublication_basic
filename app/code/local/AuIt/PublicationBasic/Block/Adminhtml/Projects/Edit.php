<?php
class AuIt_PublicationBasic_Block_Adminhtml_Projects_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{

    public function __construct()
    {
    	$this->_blockGroup 	= 'auit_publicationbasic';
        $this->_objectId 	= 'project_id';
        $this->_controller 	= 'adminhtml_projects';
        parent::__construct();

        $this->_removeButton('save');

        if (!Mage::getSingleton('adminhtml/session')->getNewAuitPublication()) {
	        if ($this->_isAllowedAction('save')) {
	            //$this->_updateButton('save', 'label', Mage::helper('auit_publicationbasic')->__('Save Project'));
	            $this->_addButton('save', array(
	                'label'     => Mage::helper('adminhtml')->__('Save'),
	                'onclick'   => 'saveAndContinueEdit(\''.$this->_getSaveUrl().'\')',
	                'class'     => 'save',
	            ), -100);
	        	$this->_addButton('saveandcontinue', array(
	                'label'     => Mage::helper('adminhtml')->__('Save and Continue Edit'),
	                'onclick'   => 'saveAndContinueEdit(\''.$this->_getSaveAndContinueUrl().'\')',
	                'class'     => 'save',
	            ), -100);
	            $this->_addButton('duplicate', array(
	                'label'     => Mage::helper('adminhtml')->__('Duplicate'),
	                'onclick'   => 'saveAndContinueEdit(\''.$this->_getDuplicateAndContinueUrl().'\')',
	                'class'     => 'add',
	            ), -100);
	        } else {
	            $this->_removeButton('save');
	        }

	        if ($this->_isAllowedAction('delete')) {
	            $this->_updateButton('delete', 'label', Mage::helper('auit_publicationbasic')->__('Delete Page'));
	        } else {
	            $this->_removeButton('delete');
	        }
         } else {
         	$this->removeButton('save');
         	$this->removeButton('delete');
         }
         $this->removeButton('reset');
    }

    /**
     * Retrieve text for header element depending on loaded page
     *
     * @return string
     */
    public function getHeaderText()
    {
        if (Mage::registry('auit_publicationbasic_project') && Mage::registry('auit_publicationbasic_project')->getId()) {
            return Mage::helper('auit_publicationbasic')->__("Edit Project '%s'", $this->htmlEscape(Mage::registry('auit_publicationbasic_project')->getName()));
        }
        else {
            return Mage::helper('auit_publicationbasic')->__('New Project');
        }
    }

    /**
     * Check permission for passed action
     *
     * @param string $action
     * @return bool
     */
    protected function _isAllowedAction($action)
    {
        return true;//Mage::getSingleton('admin/session')->isAllowed('auit_publicationbasic/group/' . $action);
    }

    /**
     * Getter of url for "Save and Continue" button
     * tab_id will be replaced by desired by JS later
     *
     * @return string
     */
    protected function _getSaveUrl()
    {
        return $this->getUrl('*/*/save', array(
          //  '_current'  => false,
           // 'active_tab'       => '{{tab_id}}'
        ));
    }
    protected function _getSaveAndContinueUrl()
    {
        return $this->getUrl('*/*/save', array(
            '_current'  => true,
            'back'      => 'edit',
            'active_tab'       => '{{tab_id}}'
        ));
    }
    protected function _getDuplicateAndContinueUrl()
    {
        return $this->getUrl('*/*/duplicate', array(
            '_current'  => true,
            'back'      => 'edit',
            'active_tab'       => '{{tab_id}}'
        ));
    }
    public function getFormActionUrl()
    {
        if ($this->hasFormActionUrl()) {
            return $this->getData('form_action_url');
        }
        return $this->getUrl('*/*/save');
    }

    /**
     * @see Mage_Adminhtml_Block_Widget_Container::_prepareLayout()
     */
    protected function _prepareLayout()
    {
    	if (!Mage::getSingleton('adminhtml/session')->getNewAuitPublication()) {


        $tabsBlock = $this->getLayout()->getBlock('auit_publicationbasic_group_edit_tabs');
        if ($tabsBlock) {
            $tabsBlockJsObject = $tabsBlock->getJsObjectName();
            $tabsBlockPrefix = $tabsBlock->getId() . '_';
        } else {
            $tabsBlockJsObject = 'page_tabsJsTabs';
            $tabsBlockPrefix = 'page_tabs_';
        }

        $this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('page_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'page_content');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'page_content');
                }
            }

            function saveAndContinueEdit(urlProject) {
                if ( AuIt.Publication && AuIt.Publication.save )
                	AuIt.Publication.save()
                var tabsIdValue = " . $tabsBlockJsObject . ".activeTab.id;
                var tabsBlockPrefix = '" . $tabsBlockPrefix . "';
                if (tabsIdValue.startsWith(tabsBlockPrefix)) {
                    tabsIdValue = tabsIdValue.substr(tabsBlockPrefix.length)
                }

                var project = new Template(urlProject, /(^|.|\\r|\\n)({{(\w+)}})/);
                var url = project.evaluate({tab_id:tabsIdValue});
                editForm.submit(url);
            }
        ";
    	}
        return parent::_prepareLayout();
    }


}
