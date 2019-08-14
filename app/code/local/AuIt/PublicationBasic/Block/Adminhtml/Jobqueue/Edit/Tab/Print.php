<?php
class AuIt_PublicationBasic_Block_Adminhtml_Jobqueue_Edit_Tab_Print
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
/**
 * 
 * 
Anschnitt	Bleed : 2mm
Steg :	1.5mm
Info-Bereich : Slug 
 */        
        // Offset 0-25,4


        $form = new Varien_Data_Form();

        $form->setHtmlIdPrefix('print_');

        $fieldset = $form->addFieldset('base_fieldset', array('legend'=>Mage::helper('auit_publicationbasic')->__('PDF Page Offset'), 'class' => ''));
		$this->_addElementTypes($fieldset);

		
		
		if ( !$model->getData('use_doc_size') )
			$model->setData('use_doc_size',1);
		if ( !$model->getData('user_page_size') )
			$model->setData('user_page_size','DIN-A#210#297');
		/*
		$data = $model->getData();
		$blob='';
		if ( isset($data['data']) )
			$blob = $data['data'];
		$data['use_doc_size']=Mage::helper('auit_publicationbasic')->getBlobValue($blob,'use_doc_size');
			if ( !$data['use_doc_size'] )
				$data['use_doc_size']=1;
		$data['user_page_size']=Mage::helper('auit_publicationbasic')->getBlobValue($blob,'user_page_size');
		if ( !$data['user_page_size'])
			$data['user_page_size'] = 'DIN-A#210#297';
		$data['user_page_orientation']=Mage::helper('auit_publicationbasic')->getBlobValue($blob,'user_page_orientation');
		$data['placment_method']=Mage::helper('auit_publicationbasic')->getBlobValue($blob,'placment_method');
		$data['use_bleed']=Mage::helper('auit_publicationbasic')->getBlobValue($blob,'use_bleed');
		*/
		$fieldset->addField('use_doc_size', 'select', array(
				'label'     => Mage::helper('auit_publicationbasic')->__('Use Document Size'),
				'title'     => Mage::helper('auit_publicationbasic')->__('Use Document Size'),
				'name'      => 'use_doc_size',
				'required'  => true,
				'options'   => array(
						'1' => Mage::helper('auit_publicationbasic')->__('Yes'),
						'2' => Mage::helper('auit_publicationbasic')->__('No')
				),
				'onchange' => '_setdoc_sizechange(this)',
				'after_element_html' =>
				'<script type="text/javascript">
					;function _setdoc_sizechange(cbm)
					{
						var v = cbm.value;
						$(\'' . 'print_user_page_size' . '\').up(\'tr\')[v==1? \'hide\' : \'show\' ]();
						$(\'' . 'print_user_page_orientation' . '\').up(\'tr\')[v==1? \'hide\' : \'show\' ]();
						$(\'' . 'print_placment_method' . '\').up(\'tr\')[v==1? \'hide\' : \'show\' ]();
						$(\'' . 'print_use_bleed' . '\').up(\'tr\')[v!=1? \'hide\' : \'show\' ]();
    				};
				</script>'
		));
//						

		$hideElements=$model->getData('use_doc_size')==1;
		$fieldset->addField('user_page_size', 'select', array(
				'label'     => Mage::helper('auit_publicationbasic')->__('Page Size'),
				'title'     => Mage::helper('auit_publicationbasic')->__('Page Size'),
				'name'      => 'user_page_size',
				'required'  => true,
				'options'   => Mage::helper('auit_publicationbasic')->getPageFormate(false),
				'after_element_html' =>
				'<script type="text/javascript">' .
				($hideElements ? '$(\'' . 'print_user_page_size' . '\').up(\'tr\').hide(); ' : '') .
				'</script>'
		));
		$fieldset->addField('user_page_orientation', 'select', array(
				'label'     => Mage::helper('auit_publicationbasic')->__('Orientation'),
				'title'     => Mage::helper('auit_publicationbasic')->__('Orientation'),
				'name'      => 'user_page_orientation',
				'required'  => true,
				'options'   => Mage::helper('auit_publicationbasic')->getOrientation(),
				'after_element_html' =>
				'<script type="text/javascript">' .
				($hideElements ? '$(\'' . 'print_user_page_orientation' . '\').up(\'tr\').hide(); ' : '') .
				'</script>'
		));
		$fieldset->addField('placment_method', 'select', array(
				'label'     => Mage::helper('auit_publicationbasic')->__('Placment Method'),
				'title'     => Mage::helper('auit_publicationbasic')->__('Placment Method'),
				'name'      => 'placment_method',
				'required'  => true,
				'options'   => array(
						'grid' => Mage::helper('auit_publicationbasic')->__('Grid'),
						'list' => Mage::helper('auit_publicationbasic')->__('List')
				),
				'after_element_html' =>
				'<script type="text/javascript">' .
				($hideElements ? '$(\'' . 'print_placment_method' . '\').up(\'tr\').hide(); ' : '') .
				'</script>'
		));
		
		//Anschnittseinstellungen des Dokuments verwenden
		$fieldset->addField('use_bleed', 'select', array(
				'label'     => Mage::helper('auit_publicationbasic')->__('Use Document Bleed Settings'),
				'title'     => Mage::helper('auit_publicationbasic')->__('Use Document Bleed Settings'),
				'name'      => 'use_bleed',
				'required'  => true,
				'options'   => array(
						'1' => Mage::helper('auit_publicationbasic')->__('Yes'),
						'0' => Mage::helper('auit_publicationbasic')->__('No')
				),
				'after_element_html' =>
				'<script type="text/javascript">' .
				(!$hideElements ? '$(\'' . 'print_use_bleed' . '\').up(\'tr\').hide(); ' : '') .
				'</script>'
		));
		
/**		
		foreach ( array('BleedBox','TrimBox') as $boxName )
		{
			if ( isset($this->_pageboxes['/'.$boxName]))
				$format[$boxName]=array('llx'=>$this->_pageboxes['/'.$boxName]['llx'],
						'lly'=>$this->_pageboxes['/'.$boxName]['lly'],
						'urx'=>$this->_pageboxes['/'.$boxName]['urx'],
						'ury'=>$this->_pageboxes['/'.$boxName]['ury']);
		}
*/
        $form->setValues($model->getData());
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
        return Mage::helper('auit_publicationbasic')->__('Print Option');
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return Mage::helper('auit_publicationbasic')->__('Print Option');
    }

    /**
     * Returns status flag about this tab can be shown or not
     *
     * @return true
     */
    public function canShowTab()
    {
    	$model = Mage::registry('auit_publicationbasic_jobqueue');
    	return ($model->getVariante() != AuIt_PublicationBasic_Model_Jobqueue::TYPE_EBOOK_EPUB3    	);
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
