<?php
/**
 * AuIt 
 *
 * @category   AuIt
 * @author     M Augsten
 * @copyright  Copyright (c) 2010 Ingenieurbüro (IT) Dipl.-Ing. Augsten (http://www.au-it.de)
 */
class AuIt_PublicationBasic_Block_Adminhtml_Items extends Mage_Adminhtml_Block_System_Config_Form_Field_Array_Abstract
{
	protected $magentoAttributes;
	protected function _filterLayoutHandle($layoutHandle)
	{
		$wildCard = '/('.implode(')|(', $this->getLayoutHandlePatterns()).')/';
		if (preg_match($wildCard, $layoutHandle)) {
			return false;
		}
		return true;
	}
    public function __construct()
    {
    	$this->addColumn('attribute', array(
            'label' => Mage::helper('auit_publicationbasic')->__('Attribute'),
        	'style' => 'width:150px'
        ));
    	
    	$this->addColumn('value', array(
            'label' => Mage::helper('auit_publicationbasic')->__('value'),
        	'style' => 'width:150px'
        ));
        $this->addColumn('template', array(
        		'label' => Mage::helper('auit_publicationbasic')->__('Template'),
        		'style' => 'width:150px'
        ));
        
        $this->_addAfter = true;
        $this->_addButtonLabel = Mage::helper('auit_publicationbasic')->__('Add new');
        $this->setTemplate('auit/publicationbasic/renderer/array.phtml');
        parent::__construct();
 	}
	protected function _getProductAttributes()
    {
    	$hash=array();
        $allowedAttributes=array('date','price','boolean','text','textarea','select','multiselect','media_image');
    	$collection = Mage::getResourceModel('catalog/product_attribute_collection')
            ->addVisibleFilter();
        $hashOpt=array();
    	foreach ( $collection as $attr )
    	{
            $code = $attr->getAttributeCode();
            $type = $attr->getFrontendInput();
            if (!in_array($type, $allowedAttributes) || $attr->getFrontendInput() == 'hidden') {
                continue;
            }
    		$hash[$attr->getAttributeCode()]=$attr->getFrontendLabel();
    	}
    	$hash['attribute_set_id']=Mage::helper('catalog')->__('Attrib. Set Name');
    	$hash['type_id']=Mage::helper('catalog')->__('Product Type');
    	asort($hash);
    	return $hash;
    }
    protected function _prepareLayout()
    {
        return parent::_prepareLayout();
    }
    
	protected function _renderCellTemplate($columnName)
    {
        $column     = $this->_columns[$columnName];
        $inputName  = $this->getElement()->getName() . '[#{_id}][' . $columnName . ']';
    	
    	if($columnName == 'attribute' )
        {
        	$rendered = '<select name="'.$inputName.'" style="'.$column['style'].'">';
        	$options = Mage::getModel('auit_publicationbasic/adminhtml_system_config_product_attribute')->toOptionArray();
        	$rendered .= '<option value=""></option>';
        	foreach ( $options as $value => $label )
        	{
        		$rendered .= '<option value="'.$value.'">'.htmlentities($label,ENT_QUOTES).'</option>';
        	}
        	$rendered .= '</select>';
        	return $rendered;
        	
        }
        if($columnName != 'template' )
        	return parent::_renderCellTemplate($columnName);
        
        $sets = Mage::helper('auit_publicationbasic')->getTemplatesForType(AuIt_PublicationBasic_Helper_Data::TEMPLATE_PRODUCT);
        $rendered = '<select name="'.$inputName.'" style="'.$column['style'].'">';
    	foreach ( $sets as $item )
    	{
        	$rendered .= '<option value="'.$item['value'].'">'.htmlentities($item['label'],ENT_QUOTES).'</option>';
    	}
		$rendered .= '</select>';
        return $rendered;
    } 	
}
