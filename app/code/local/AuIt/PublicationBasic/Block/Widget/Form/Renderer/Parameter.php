<?php
class AuIt_PublicationBasic_Block_Widget_Form_Renderer_Parameter extends Mage_Adminhtml_Block_System_Config_Form_Field_Array_Abstract
{
	protected $magentoAttributes;
    public function __construct()
    {
    	$this->setHtmlId('_' . uniqid());
    	
        $this->addColumn('code', array(
            'label' => Mage::helper('auit_publicationbasic')->__('Code'),
        	'style' => 'width:95%'
        ));
        $this->addColumn('name', array(
            'label' => Mage::helper('auit_publicationbasic')->__('Name'),
        	'style' => 'width:95%'
        ));
        $this->addColumn('type', array(
            'label' => Mage::helper('auit_publicationbasic')->__('Type'),
        	'style' => 'width:95%'
        ));
        $this->addColumn('unit', array(
            'label' => Mage::helper('auit_publicationbasic')->__('Unit'),
        	'style' => 'width:95%'
        ));
        $this->addColumn('default', array(
            'label' => Mage::helper('auit_publicationbasic')->__('Default'),
        	'style' => 'width:95%',
        	'class' => 'input-text'
        ));
        
        $this->_addAfter = false;
        $this->_addButtonLabel = Mage::helper('auit_publicationbasic')->__('Add new Parameter');
        $this->setTemplate('auit/publicationbasic/renderer/array_date.phtml');
        parent::__construct();
 	}
	protected function _renderCellTemplate($columnName)
    {
        $column     = $this->_columns[$columnName];
        $inputName  = $this->getElement()->getName() . '[#{_id}][' . $columnName . ']';
    	
    	if($columnName == 'type' )
    	{
	        $html = '<select class="select " name="'.$inputName.'" style="width:100%">';
	        switch ( $columnName )
	        {
	       		case 'type':
	       			$html .= '<option value="input">'.Mage::helper('checkout')->__('Text').'</option>';
	       			//$html .= '<option value="area">'.Mage::helper('checkout')->__('Text Area').'</option>';
	       			$html .= '<option value="number">'.Mage::helper('checkout')->__('Number').'</option>';
	       			$html .= '<option value="color">'.Mage::helper('checkout')->__('Color').'</option>';
				break;
	        }
			$html .= '</select>';
	        return $html;
    	}
        return parent::_renderCellTemplate($columnName);
    } 	
    public function getDateFormat()
    {
    	return Mage::app()->getLocale()->getDateStrFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);
        $displayFormat = Varien_Date::convertZendToStrFtime(Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT), true, false);
        $displayTimeFormat = Varien_Date::convertZendToStrFtime(Mage::app()->getLocale()->getTimeFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT), false, true);
        return $displayFormat . ' ' .$displayTimeFormat; 
    }
    public function getArrayRows()
    {
        if (null !== $this->_arrayRowsCache) {
            return $this->_arrayRowsCache;
        }
    	$result = parent::getArrayRows();
    	foreach ( $result as &$row )
    	{
			foreach ( array('time_start','time_end') as $field) 
			{
				if ( isset($row [$field]) )
				{
					$row [$field]=$this->formatDate($row [$field]);//,Mage_Core_Model_Locale::FORMAT_TYPE_SHORT,true);
				}
			} 
    	}
    	$this->_arrayRowsCache = $result;
        return $this->_arrayRowsCache;
    }
    
}
