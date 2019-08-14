<?php
/**
 * AuIt 
 *
 * @category   AuIt
 * @author     M Augsten
 * @copyright  Copyright (c) 2010 Ingenieurbüro (IT) Dipl.-Ing. Augsten (http://www.au-it.de)
 */
class AuIt_PublicationBasic_Block_Widget_Grid_Column_Renderer_Image extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function __construct() {
    }
    public function render(Varien_Object $row)
    {
        return $this->_getValue($row);
    }
    public function _getValue(Varien_Object $row)
    {
    	static $_dummyProduct;
    	if ( !$_dummyProduct)
    		$_dummyProduct = Mage::getModel('catalog/product');
    		
    	$image_file = $this->getColumn()->getDefault();
		$image_file = parent::_getValue($row);
		$html = "";
		$url='';
		
        $location = Mage::getBaseDir('media').'/catalog/product'. $image_file;
		if ( !$image_file || $image_file=='no_selection' )
		{
			$html = "";
		}
        else if ( !file_exists($location) || !is_file($location))
        {
    		$html = "<span style=\"color:red\">$image_file</span>";
        }
        else 
        {
       	//	$url = Mage::getBaseUrl('media').'/catalog/product'. $image_file;
    		//$html = "<span style=\"color:red\">$html</span>";
			try {
				$url = ''.Mage::helper('catalog/image')->init($_dummyProduct,$this->getColumn()->getData('index'),$image_file)->resize(75,75);
				$html = "<img src=". $url ." style=\"width:75px;border=0\"/>";
			}catch (Exception $e)
			{
				$html = "<span style=\"color:red\">$image_file</span>";
			}
        }
        if (  Mage::app()->getFrontController()->getRequest()->getParam('expexcelmode') )
        {
        	if ( $url )
        	{
        		return str_replace(Mage::getBaseUrl('media'),Mage::getBaseDir('media').DS,$url);
        	}
        	return $url;
        }
        return $html;
    }
}
