<?php
class AuIt_PublicationBasic_Block_Adminhtml_Jobqueue_Edit_Tab_Settings
    extends Mage_Adminhtml_Block_Widget_Form
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
    protected function _prepareLayout()
    {
        $this->setChild('continue_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label'     => Mage::helper('catalog')->__('Continue'),
                    'onclick'   => "if (editForm.submit()) { return false }",
                    'class'     => 'save'
                )
            )
        );
        return parent::_prepareLayout();
    }

    /**
     * Prepare form before rendering HTML
     * Setting Form Fieldsets and fields
     *
     * @return Mage_Adminhtml_Block_Widget_Form
     */
    protected function _prepareForm()
    {

        $form = new Varien_Data_Form();
        $form->setHtmlIdPrefix('app_');
        $type = Mage::getSingleton('adminhtml/session')->getNewAuitPublication();
        
        switch ( $type )
        {
        	case 11:
		        $fieldset = $form->addFieldset('base_fieldset', array('legend' => $this->__('Promotions')));
        		$fieldset->addField('type', 'hidden', array('name' => 'type','value'=>$type));
		        $fieldset->addField('variante', 'select', array(
		                'name'      => 'variante',
		                'label'     => $this->__('Type'),
		                'title'     => $this->__('Type'),
		                'values'    => Mage::helper('auit_publicationbasic')->getPromoOptions(),
		                'required'  => true
		        ));
       		break;
        }
        $fieldset->addField('continue_button', 'note', array(
            'text' => $this->getChildHtml('continue_button'),
        ));
        //$form->setValues($model->getData());
        $this->setForm($form);
        return parent::_prepareForm();
    }

    /**
     * Tab label getter
     *
     * @return string
     */
    public function getTabLabel()
    {
        return $this->__('Settings');
    }

    /**
     * Tab title getter
     *
     * @return string
     */
    public function getTabTitle()
    {
        return $this->__('Settings');
    }

    /**
     * Check if tab can be shown
     *
     * @return bool
     */
    public function canShowTab()
    {
        return (bool) Mage::getSingleton('adminhtml/session')->getNewAuitPublication();
    }

    /**
     * Check if tab hidden
     *
     * @return bool
     */
    public function isHidden()
    {
        return false;
    }
}
