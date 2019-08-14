<?php
class AuIt_PublicationBasic_Block_Adminhtml_Jobqueue_Edit_Tab_Queue
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
        $data = $model->getData();
        

        $form = new Varien_Data_Form();
        $form->setHtmlIdPrefix('queue_');
        $fieldset = $form->addFieldset('base_fieldset', array('legend'=>Mage::helper('auit_publicationbasic')->__('Information'), 'class' => ''));
		$this->_addElementTypes($fieldset);

		$outputFormat = Mage::app()->getLocale()->getDateTimeFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);
        $fieldset->addField('date', 'date',array(
        		'name'      =>    'start_at',
        		'time'      =>    true,
        		'format'    =>    $outputFormat,
        		'label'     =>    Mage::helper('newsletter')->__('Queue Date Start'),
        		'image'     =>    $this->getSkinUrl('images/grid-cal.gif')
        ));
        
        if ($model->getQueueStartAt()) {
        	$data['date']=Mage::app()->getLocale()->date($model->getQueueStartAt(), Varien_Date::DATETIME_INTERNAL_FORMAT);
        }
        
        
        $fieldset->addField('prio', 'select', array(
            'label'     => Mage::helper('auit_publicationbasic')->__('Priority'),
            'title'     => Mage::helper('auit_publicationbasic')->__('Priority'),
            'name'      => 'prio',
            'required'  => true,
            'options'   => Mage::helper('auit_publicationbasic')->getJobQueuePriorityOptions(true)
        ));
        /*
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
        */

        $form->setValues($data);
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
        return Mage::helper('auit_publicationbasic')->__('Job Queue');
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return Mage::helper('auit_publicationbasic')->__('Job Queue');
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
        return true;
    }
    protected function _getAdditionalElementTypes()
    {
        $result = array(
//            'auit-storelocator-list'    => Mage::getConfig()->getBlockClassName('auit_publicationbasic/widget_form_element_list'),
        );
        return $result;
    }
}
