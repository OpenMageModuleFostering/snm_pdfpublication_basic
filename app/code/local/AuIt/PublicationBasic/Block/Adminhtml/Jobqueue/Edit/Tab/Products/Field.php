<?php
class AuIt_PublicationBasic_Block_Adminhtml_Jobqueue_Edit_Tab_Products_Field extends Varien_Data_Form_Element_Abstract
{
	public function getElementHtml()
	{
		$html = '<style>#auit_publicationbasic_catalog_skus2_content .pager input.page {width:2em !important;}</style>';
		
		$updElement = $this->getData('update_element'); 
		
		$html .="<script type='text/javascript'>
//<![CDATA[
	auit_l_product_fields={
		updateElement:'$updElement',
		selectedItems: null,
		init:function()
		{
			auit_l_product_fields.selectedItems= ".'$H({})'.";
		 	var elm = $(auit_l_product_fields.updateElement);
            var values = elm.value.split(','), s = '';
            for (i=0; i<values.length; i++) {
                s = values[i].strip();
                if (s!='') {
                   auit_l_product_fields.selectedItems.set(s,1);
                }
            }
		},
		gridRowClick:function(grid, event) { 
	        var trElement = Event.findElement(event, 'tr');
	        var isInput = Event.element(event).tagName == 'INPUT';
	        if (trElement) {
	            var checkbox = Element.select(trElement, 'input');
	            if (checkbox[0]) {
	                var checked = isInput ? checkbox[0].checked : !checkbox[0].checked;
	                grid.setCheckboxChecked(checkbox[0], checked);
	
	            }
	        }
		},
    	gridCheckboxCheck: function (grid, element, checked) {
    		if ( !auit_l_product_fields.selectedItems )
    			auit_l_product_fields.init(); 
	        if (checked) {
	            if (!element.up('th')) {
	                auit_l_product_fields.selectedItems.set(element.value,1);
	            }
	        } else {
	            auit_l_product_fields.selectedItems.unset(element.value);
	        }
	        grid.reloadParams = {'selected[]':auit_l_product_fields.selectedItems.keys()};
	        $(auit_l_product_fields.updateElement).value = auit_l_product_fields.selectedItems.keys().join(', ');
	    }
    	,gridRowInit: function (grid, row) {
    		if ( !auit_l_product_fields.selectedItems )
    			auit_l_product_fields.init(); 
    		if (!grid.reloadParams) {
	            grid.reloadParams = {'selected[]':auit_l_product_fields.selectedItems.keys()};
	        }
            var checkbox = Element.select(row, 'input');
            if (checkbox[0]) {
                if ( auit_l_product_fields.selectedItems.get(checkbox[0].value)==1 )
                	grid.setCheckboxChecked(checkbox[0], true);
            }
					
	    }
	};
//]]>
</script>";
		/*
		$html .='<div id="cms_page_grid_container">
			<div class="entry-edit">
			<div class="entry-edit-head">
			<div class="f-right">'.$this->getGridButtonsHtml().'</div>
			<h4 class="fieldset-legend head-cms-page-grid icon-head">'.Mage::helper('auit_publicationbasic')->__('Products').'</h4></div><fieldset>';
		*/
		$content = Mage::getSingleton('core/layout')
		->createBlock('auit_publicationbasic/adminhtml_jobqueue_edit_tab_products_grid');
		$content->setId($this->getHtmlId() . '_content')
		->setElement($this);
		$html .= $content->toHtml();
		//$html .= '</fieldset></div></div>';
		
		
		
		return $html;
	}
    public function getGridButtonsHtml()
    {
    	return '';
		$addButtonData = array(
    				'id'        => 'add_textmgr_text',
    				'label'     => Mage::helper('auit_publicationbasic')->__('Insert Products'),
    			//	'onclick'   => 'hierarchyNodes.pageGridAddSelected()',
    				'class'     => 'add',
  //  				'disabled'  => !$this->_isAllowedAction('save')
    	);
    	return Mage::getSingleton('core/layout')->createBlock('adminhtml/widget_button')
    		->setData($addButtonData)->toHtml();
    }
}