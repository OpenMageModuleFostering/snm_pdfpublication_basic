<?php
class AuIt_PublicationBasic_Model_Adminhtml_System_Config_Producttemplates
{
	public function toOptionArray()
	{
		$values = array(
				array('value' => '', 'label' => Mage::helper('auit_publicationbasic')->__('Please select'))
		);
		return array_merge( $values,Mage::helper('auit_publicationbasic')->getTemplatesForType(AuIt_PublicationBasic_Helper_Data::TEMPLATE_PRODUCT));
	}
}
