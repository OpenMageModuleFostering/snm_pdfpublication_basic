<?php
class AuIt_PublicationBasic_Block_Adminhtml_Projects_Edit_Tab_Layout
    extends Mage_Adminhtml_Block_Widget_Form
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
    protected function _prepareForm()
    {
        $model = Mage::registry('auit_publicationbasic_project');
        /*
         * Checking if user have permissions to save information
         */
        if ($this->_isAllowedAction('save')) {
            $isElementDisabled = false;
        } else {
            $isElementDisabled = true;
        }

        $form = new Varien_Data_Form();
        $form->setHtmlIdPrefix('layout_');
//     	$fieldset = $form->addFieldset('base_fieldset', array('legend'=>Mage::helper('auit_publicationbasic')->__('Texte'), 'class' => 'fieldset-wide'));
	//	$this->_addElementTypes($fieldset);
		$this->_addElementTypes($form);

        $form->addField('data', 'auit-publication-canvas', array(
            'name'      => 'data',
        ));

        $form->setValues($model->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }
    public function getTabLabel()
    {
        return Mage::helper('auit_publicationbasic')->__('Layout');
    }
    public function getTabTitle()
    {
        return Mage::helper('auit_publicationbasic')->__('Layout');
    }
    public function canShowTab()
    {
        return true;
    }
    public function isHidden()
    {
        return false;
    }
    protected function _isAllowedAction($action)
    {
        return true;
    }
    protected function _getAdditionalElementTypes()
    {
        $result = array(
            'auit-publication-canvas'    => Mage::getConfig()->getBlockClassName('auit_publicationbasic/widget_form_element_publication'),
        );
        return $result;
    }
}
