<?php
class AuIt_PublicationBasic_Model_Adminhtml_System_Config_Producttemplates
{
	public function toOptionArray()
	{
		$values = array(
				array('value' => '', 'label' => Mage::helper('auit_publicationbasic')->__('Please select'))
		);
		
		$values[] = 
			array('value' => Mage::helper('auit_publicationbasic')->getJobTemplates(), 
				 'label' => Mage::helper('auit_publicationbasic')->__('Job Defintion'));
		$values[] = 
			array('value' => Mage::helper('auit_publicationbasic')->getTemplatesForType(AuIt_PublicationBasic_Helper_Data::TEMPLATE_PRODUCT), 
				 'label' => Mage::helper('auit_publicationbasic')->__('Basic Templates'));
		
		return $values;//array_merge( $values,Mage::helper('auit_publicationbasic')->getTemplatesForType(AuIt_PublicationBasic_Helper_Data::TEMPLATE_PRODUCT));
	}
}
