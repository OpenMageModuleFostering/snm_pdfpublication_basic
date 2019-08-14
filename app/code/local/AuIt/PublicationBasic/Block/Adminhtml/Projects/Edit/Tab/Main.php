<?php
class AuIt_PublicationBasic_Block_Adminhtml_Projects_Edit_Tab_Main
    extends Mage_Adminhtml_Block_Widget_Form
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
    protected function _prepareForm()
    {
        $model = Mage::registry('auit_publicationbasic_project');
        if ($this->_isAllowedAction('save')) {
            $isElementDisabled = false;
        } else {
            $isElementDisabled = true;
        }


        $form = new Varien_Data_Form();

        $form->setHtmlIdPrefix('page_');

        $fieldset = $form->addFieldset('base_fieldset', array('legend'=>Mage::helper('auit_publicationbasic')->__('General Information'), 'class' => ''));
		$this->_addElementTypes($fieldset);
        if ($model->getProjectId()) {
            $fieldset->addField('project_id', 'hidden', array(
                'name' => 'project_id',
            ));
        }
        $fieldset->addField('type', 'select', array(
        		'name'      => 'type',
        		'label'     => $this->__('Project Type'),
        		'title'     => $this->__('Project Type'),
        		'values'    => Mage::helper('auit_publicationbasic')->getProjectsOptions(),
        		'required'  => true,
        		'disabled'  => true
        ));

        $fieldset->addField('name', 'text', array(
            'name'      => 'name',
            'label'     => Mage::helper('auit_publicationbasic')->__('Name'),
            'title'     => Mage::helper('auit_publicationbasic')->__('Name'),
            'required'  => true,
        ));
        $fieldset->addField('status', 'select', array(
            'label'     => Mage::helper('auit_publicationbasic')->__('Status'),
            'title'     => Mage::helper('auit_publicationbasic')->__('Status'),
            'name'      => 'status',
            'required'  => true,
            'options'   => array(
                '1' => Mage::helper('auit_publicationbasic')->__('Enabled'),
                '0' => Mage::helper('auit_publicationbasic')->__('Disabled'),
            ),
        ));

        if (!$model->getId()) {
            $model->setData('is_active', '1');
        }
        $form->setValues($model->getData());
        $this->setForm($form);
        return parent::_prepareForm();
    }

    /**
     * Prepare label for tab
     *
     * @return string
     */
    public function getTabLabel()
    {
        return Mage::helper('auit_publicationbasic')->__('Information');
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return Mage::helper('auit_publicationbasic')->__('Information');
    }

    /**
     * Returns status flag about this tab can be shown or not
     *
     * @return true
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Returns status flag about this tab hidden or not
     *
     * @return true
     */
    public function isHidden()
    {
        return false;
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
    protected function _getAdditionalElementTypes()
    {
        $result = array(
//            'auit-storelocator-list'    => Mage::getConfig()->getBlockClassName('auit_publicationbasic/widget_form_element_list'),
        );
        return $result;
    }
}
