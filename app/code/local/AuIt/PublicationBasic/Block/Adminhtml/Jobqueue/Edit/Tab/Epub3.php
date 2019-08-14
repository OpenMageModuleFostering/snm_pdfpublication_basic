<?php
class AuIt_PublicationBasic_Block_Adminhtml_Jobqueue_Edit_Tab_Epub3
    extends Mage_Adminhtml_Block_Widget_Form
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
    protected function _prepareForm()
    {
        $model = Mage::registry('auit_publicationbasic_jobqueue');
        if ($this->_isAllowedAction('save')) {
            $isElementDisabled = false;
        } else {
            $isElementDisabled = true;
        }

        $form = new Varien_Data_Form();

        $form->setHtmlIdPrefix('epub3_');

        $fieldset = $form->addFieldset('base_fieldset', array('legend'=>Mage::helper('auit_publicationbasic')->__('Option'), 'class' => ''));
		$this->_addElementTypes($fieldset);

		
		$fieldset->addField('template', 'select', array(
				'label'     => Mage::helper('auit_publicationbasic')->__('Cover Template'),
				'title'     => Mage::helper('auit_publicationbasic')->__('Cover Template'),
				'name'      => 'template',
				'required'  => true,
				'values'   => Mage::helper('auit_publicationbasic')->getTemplatesForType(array(AuIt_PublicationBasic_Helper_Data::TEMPLATE_STATIC,AuIt_PublicationBasic_Helper_Data::TEMPLATE_PRODUCT))
		));
		
		$fieldset->addField('template2', 'select', array(
				'label'     => Mage::helper('auit_publicationbasic')->__('Article Template'),
				'title'     => Mage::helper('auit_publicationbasic')->__('Article Template'),
				'name'      => 'template2',
				'required'  => true,
				'values'   => Mage::helper('auit_publicationbasic')->getTemplatesForType(AuIt_PublicationBasic_Helper_Data::TEMPLATE_PRODUCT)
		));
		
		
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
        return Mage::helper('auit_publicationbasic')->__('Ebook Option');
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return Mage::helper('auit_publicationbasic')->__('Ebook Option');
    }

    /**
     * Returns status flag about this tab can be shown or not
     *
     * @return true
     */
    public function canShowTab()
    {
    	$model = Mage::registry('auit_publicationbasic_jobqueue');
    	return ($model->getVariante() == AuIt_PublicationBasic_Model_Jobqueue::TYPE_EBOOK_EPUB3   	);
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
