<?php
class AuIt_PublicationBasic_Block_Adminhtml_Jobqueue_Edit_Tab_Products
    extends Mage_Adminhtml_Block_Widget_Form
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
	public function __construct()
	{
		parent::__construct();
	//	$this->setTemplate('auit/publication/admin/jobqueue/products.phtml');
//		$this->setId('group_page_tree');
	}
	
    protected function _prepareForm()
    {
        $model = Mage::registry('auit_publicationbasic_jobqueue');
        if ($this->_isAllowedAction('save')) {
            $isElementDisabled = false;
        } else {
            $isElementDisabled = true;
        }

        $form = new Varien_Data_Form();

        $form->setHtmlIdPrefix('auit_publicationbasic_');
        
		//$this->_addElementTypes($fieldset);
        $this->_addElementTypes($form);
        if ( $model->getVariante() == AuIt_PublicationBasic_Model_Jobqueue::TYPE_BROCHURE_LIST )
        {
        	//$this->_addElementTypes($fieldset);
        	if ( !$model->getData('layout_def') )
        	{
        		$data = array(
        			'templates'=>array(
        				array('template'=>Mage::helper('auit_publicationbasic')->getTemplateIdentifier($model->getData('template')),'skus'=>$model->getData('catalog_skus'))
        			)
        		);
        		
//        		$data[]=array('template'=>$model->getData('template'),'skus'=>$model->getData('catalog_skus'));
        		$data = json_encode($data);
        		$model->setData('layout_def',$data);
        		
        	}
        	/*
        	$options = Mage::helper('auit_publicationbasic/style')->getCssStyles();
        	$options['']='';
        	$fieldset->addField('print_style', 'select', array(
        			'label'     => Mage::helper('auit_publicationbasic')->__('STYLE AUSWAHL'),
        			'title'     => Mage::helper('auit_publicationbasic')->__('Products (First Page)'),
        			'name'      => 'print_style',
        			'required'  => false,
        			'values'    =>$options 
        	));
        	
        	 * <select data-bind="combobox: preview_style">
						<?php echo Mage::helper('auit_publicationbasic')->asOptions(Mage::helper('auit_publicationbasic/style')->getCssStyles(),true);?>
					</select>
        	$fieldset->addField('first_page_skus', 'text', array(
        			'label'     => Mage::helper('auit_publicationbasic')->__('Products (First Page)'),
        			'title'     => Mage::helper('auit_publicationbasic')->__('Products (First Page)'),
        			'name'      => 'first_page_skus',
        			'required'  => false
        	));
        	
        	$fieldset->addField('last_page_skus', 'text', array(
        			'label'     => Mage::helper('auit_publicationbasic')->__('Products (Last Page)'),
        			'title'     => Mage::helper('auit_publicationbasic')->__('Products (Last Page)'),
        			'name'      => 'last_page_skus',
        			'required'  => false
        	));
        	*/
        	
        	$form->addField('layout_def', 'auit-publication-jobqueue-layout', array(
        			'label'     => Mage::helper('auit_publicationbasic')->__(' '),
        			'title'     => Mage::helper('auit_publicationbasic')->__(' '),
        			'name'      => 'layout_def',
        			//'update_element' => $elem->getHtmlId()
        	));
        	 
        }
        else {
        	
        	$fieldset = $form->addFieldset('base_fieldset', array('legend'=>Mage::helper('auit_publicationbasic')->__('&#160;'), 'class' => 'fieldset-wide'));
        	 
        
		$elem = $fieldset->addField('catalog_skus', 'text', array(
				'label'     => Mage::helper('auit_publicationbasic')->__('Products'),
				'title'     => Mage::helper('auit_publicationbasic')->__('Products'),
				'name'      => 'catalog_skus',
				'required'  => true,
//				'after_element_html' =>
	//			'<script type="text/javascript">' .
		//		($hideElements ? '$(\'' . 'print_placment_method' . '\').up(\'tr\').hide(); ' : '') .
			//	'</script>'
		));
		
		$form->addField('catalog_skus2', 'auit-publication-jobqueue-products', array(
				'label'     => Mage::helper('auit_publicationbasic')->__(' '),
				'title'     => Mage::helper('auit_publicationbasic')->__(' '),
				'name'      => 'catalog_skus2',
				'update_element' => $elem->getHtmlId()
		));
        }		
		$form->setValues($model->getData());
        $this->setForm($form);
        return parent::_prepareForm();
    }
    
    public function getTabLabel()
    {
        return Mage::helper('auit_publicationbasic')->__('Products/Layout');
    }
    public function getTabTitle()
    {
        return Mage::helper('auit_publicationbasic')->__('Products/Layout');
    }
    public function canShowTab()
    {
        $model = Mage::registry('auit_publicationbasic_jobqueue');
    	return ($model->getVariante() == AuIt_PublicationBasic_Model_Jobqueue::TYPE_BROCHURE_LIST||
    			$model->getVariante() == AuIt_PublicationBasic_Model_Jobqueue::TYPE_EBOOK_EPUB3
    			 );
    }
    public function isHidden()
    {
        return false;
    }
    protected function _isAllowedAction($action)
    {
        return true;
    }
    protected function _getAdditionalElementTypes()
    {
        $result = array(
            'auit-publication-jobqueue-products'  => Mage::getConfig()->getBlockClassName('auit_publicationbasic/adminhtml_jobqueue_edit_tab_products_field'),
            'auit-publication-jobqueue-layout'    => Mage::getConfig()->getBlockClassName('auit_publicationbasic/widget_form_element_satzeditor'),
        );
        return $result;
    }
}
