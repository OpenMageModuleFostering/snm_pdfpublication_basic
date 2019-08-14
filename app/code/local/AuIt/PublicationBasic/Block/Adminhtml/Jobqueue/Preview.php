<?php 
class AuIt_PublicationBasic_Block_Adminhtml_Jobqueue_Preview extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        return $this->_getValue($row);
    } 
    protected function _getValue(Varien_Object $row)
    {       
    	if ( !$row->getData('extension') )
    	{
    		return '';
    	}
        $url = Mage::helper('auit_publicationbasic/pdf')->getPreviewJobImageUrl($row->getData('jobqueue_id'));
        $out = '<img onmouseover="this.style.height=\'auto\'" onmouseout="this.style.height=\'100px\'" src='. $url ." height='100px'/>"; 
        return $out;
    }
}