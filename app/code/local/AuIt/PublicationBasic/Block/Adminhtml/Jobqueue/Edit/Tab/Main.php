<?php
class AuIt_PublicationBasic_Block_Adminhtml_Jobqueue_Edit_Tab_Main
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

        $form->setHtmlIdPrefix('page_');

        $fieldset = $form->addFieldset('base_fieldset', array('legend'=>Mage::helper('auit_publicationbasic')->__('General Information'), 'class' => ''));
		$this->_addElementTypes($fieldset);
        if ($model->getJobqueueId()) {
            $fieldset->addField('jobqueue_id', 'hidden', array(
                'name' => 'jobqueue_id',
            ));
        }
		$fieldset->addField('type', 'hidden', array('name' => 'type'));
		$fieldset->addField('variante', 'hidden', array('name' => 'variante'));
		/*
        $fieldset->addField('type', 'select', array(
        		'name'      => 'type',
        		'label'     => $this->__('Type'),
        		'title'     => $this->__('Type'),
        		'values'    => Mage::helper('auit_publicationbasic')->getJobQueueTypeOptions(),
        		'required'  => true,
        		'disabled'  => true
        ));
        
        $fieldset->addField('variante', 'select', array(
        		'name'      => 'variante',
        		'label'     => $this->__('Type'),
        		'title'     => $this->__('Type'),
        		'values'    => Mage::helper('auit_publicationbasic')->getPromoOptions(),
        		'required'  => true,
        		'disabled'  => true
        ));
        */
        $fieldset->addField('name', 'text', array(
            'name'      => 'name',
            'label'     => Mage::helper('auit_publicationbasic')->__('Name'),
            'title'     => Mage::helper('auit_publicationbasic')->__('Name'),
            'required'  => true,
        ));
        
        $fieldset->addField('print_store', 'select', array(
        		'label'     => Mage::helper('auit_publicationbasic')->__('Store View'),
        		'title'     => Mage::helper('auit_publicationbasic')->__('Store View'),
        		'name'      => 'print_store',
        		'required'  => true,
        		'values'   => Mage::helper('auit_publicationbasic')->getPreviewStores(true)
        ));
        
        if ( $model->getVariante() == AuIt_PublicationBasic_Model_Jobqueue::TYPE_COUPON_CARD )
        {
	        $fieldset->addField('salesrule_rule', 'select', array(
	        		'label'     => Mage::helper('auit_publicationbasic')->__('Shopping Cart Price Rule'),
	        		'title'     => Mage::helper('auit_publicationbasic')->__('Shopping Cart Price Rule'),
	        		'name'      => 'salesrule_rule',
	        		'required'  => true,
	        		'values'   => Mage::helper('auit_publicationbasic')->getPromos()
	        ));
	        $fieldset->addField('template', 'select', array(
	        		'label'     => Mage::helper('auit_publicationbasic')->__('Template'),
	        		'title'     => Mage::helper('auit_publicationbasic')->__('Template'),
	        		'name'      => 'template',
	        		'required'  => true,
	        		'values'   => Mage::helper('auit_publicationbasic')->getTemplatesForType(AuIt_PublicationBasic_Helper_Data::TEMPLATE_COUPON)
	        ));
        }        
        elseif ( $model->getVariante() == AuIt_PublicationBasic_Model_Jobqueue::TYPE_BROCHURE_LIST )
        {
        	$fieldset->addField('print_customer', 'text', array(
        			'label'     => Mage::helper('auit_publicationbasic')->__('Use CustomerID'),
        			'title'     => Mage::helper('auit_publicationbasic')->__('Use CustomerID'),
        			'name'      => 'print_customer',
        			'required'  => false
        	));
        	 
        	/*
	        $fieldset->addField('template', 'select', array(
	        		'label'     => Mage::helper('auit_publicationbasic')->__('Template'),
	        		'title'     => Mage::helper('auit_publicationbasic')->__('Template'),
	        		'name'      => 'template',
	        		'required'  => true,
	        		'values'   => Mage::helper('auit_publicationbasic')->getTemplatesForType(AuIt_PublicationBasic_Helper_Data::TEMPLATE_PRODUCT)
	        ));
	        $fieldset->addField('first_page', 'select', array(
	        		'label'     => Mage::helper('auit_publicationbasic')->__('First Page'),
	        		'title'     => Mage::helper('auit_publicationbasic')->__('First Page'),
	        		'name'      => 'first_page',
	        		'required'  => false,
	        		'values'   => Mage::helper('auit_publicationbasic')->getTemplatesForType(array(AuIt_PublicationBasic_Helper_Data::TEMPLATE_PRODUCT,AuIt_PublicationBasic_Helper_Data::TEMPLATE_STATIC),true)
	        ));
	        $fieldset->addField('last_page', 'select', array(
	        		'label'     => Mage::helper('auit_publicationbasic')->__('Last Page'),
	        		'title'     => Mage::helper('auit_publicationbasic')->__('Last Page'),
	        		'name'      => 'last_page',
	        		'required'  => false,
	        		'values'   => Mage::helper('auit_publicationbasic')->getTemplatesForType(array(AuIt_PublicationBasic_Helper_Data::TEMPLATE_PRODUCT,AuIt_PublicationBasic_Helper_Data::TEMPLATE_STATIC),true)
	        ));
	        */
        }        
        elseif ( $model->getVariante() == AuIt_PublicationBasic_Model_Jobqueue::TYPE_EBOOK_EPUB3 )
        {
        	/*
	        $fieldset->addField('template', 'select', array(
	        		'label'     => Mage::helper('auit_publicationbasic')->__('Template'),
	        		'title'     => Mage::helper('auit_publicationbasic')->__('Template'),
	        		'name'      => 'template',
	        		'required'  => true,
	        		'values'   => Mage::helper('auit_publicationbasic')->getTemplatesForType(AuIt_PublicationBasic_Helper_Data::TEMPLATE_PRODUCT)
	        ));
	        */
        }        
        if (!$model->getId()) {
            $model->setData('is_active', '1');
        }
        /*
        $data = $model->getData();
        $blob='';
        if ( isset($data['data']) )
        	$blob = $data['data'];
        $data['salesrule_rule']=Mage::helper('auit_publicationbasic')->getBlobValue($blob,'salesrule_rule');
        $data['template']=Mage::helper('auit_publicationbasic')->getBlobValue($blob,'template');
        */
       
        $form->setValues($model->getData());
//        $form->setValues($model->getData());
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
