<?php
class AuIt_PublicationBasic_Admin_ShapeController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Index action
     */
    public function indexAction()
    {
    }
    protected function _sendResponse()
    {
    	$this->getResponse()->sendHeaders();
    	echo $this->getResponse()->getBody();
    }
    public function clipSVGAction()
    {
    	$pdfPublication = Mage::getModel('auit_publicationbasic/renderer_pdf');
    	try {
    		$pdf = $pdfPublication->getPdfFromData('preview',$this->getRequest()->getParam('preview'));
    	//	$data = $pdf->render();
    //		file_put_contents('test.pdf', $data);
    		$this->_prepareDownloadResponse('preview_'.Mage::getSingleton('core/date')->date('Y-m-d_H-i-s').'.pdf', $pdf->render(), 'application/pdf');
			$this->_sendResponse();
			exit;
		}
    	catch ( Exception $e )
    	{
    		Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
    		$this->_redirect('adminhtml');
    		return;


    	}
    }
    public function searchAction()
    {
    	$data=array();
    	if ( $ping = $this->getRequest()->getParam('ping') )
    	{
    		$this->getResponse()->setHeader('Content-type', 'image/png');
    		$this->getResponse()->setBody('');
    		return;
    	}
    	
    	if ( $term = $this->getRequest()->getParam('term') )
    	{
    		$helper = Mage::getResourceHelper('core');
    		$likeExpression = $helper->addLikeEscape($term, array('position' => 'any'));
    		$collection = Mage::getResourceModel('catalog/product_collection')
    		->setStore(Mage::app()->getStore())
    		->addAttributeToSelect('name')
    		->addAttributeToFilter('visibility', array('neq' => Mage_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE))
    		//->addAttributeToFilter('sku',array('like'=>$likeExpression));
    		->addAttributeToFilter(
    				array(
    						array('attribute' => 'name', array('like'=>$likeExpression)),
    						array('attribute' => 'sku', array('like'=>$likeExpression))
    				)
    		);
    		
    		foreach ( $collection as $product )
    		{
    			$data[]=array("id"=>$product->getId(), "label"=>$product->getSku().' / '.$product->getName(), "value"=>$product->getSku());
    		}
    		if ( !count($data) )
    			$data[]=array("id"=>'', "label"=>$this->__('No data found'), "value"=>'');
    	}
    	$this->getResponse()->setHeader('Content-type', 'application/json; charset=UTF-8');
    	$this->getResponse()->setBody(Zend_Json::encode($data));
    }
    public function templatepreviewAction()
    {
    	if ( $templateId = $this->getRequest()->getParam('tid') )
    	{
    		$update_time = $this->getRequest()->getParam('upd');
    		$path = Mage::helper('auit_publicationbasic/pdf')->getPreviewImage($templateId,$update_time);
    		/*
    		
    		$fileName = 'preview_template_'.$templateId.'.jpg';
    		$directory = Mage::getBaseDir('media') . DS.'catalog'.DS.'product'.DS.'cache'.DS.'_snm_publication_previews'.DS;
    		$path = $directory.$fileName;
    		if ( extension_loaded('imagick')) {
    				
    			if ( !file_exists($path) || filemtime($path) < $update_time)
    			{
    				$io = new Varien_Io_File();
    				if ( !$io->isWriteable(dirname($path)) && !$io->mkdir(dirname($path), 0777, true)) {
    					$msg = Mage::helper('catalog')->__("Cannot create writeable directory '%s'.", $path);
    					Mage::log($msg );
    					Mage::getSingleton('adminhtml/session')->addError($msg );
    				}else {
    					$model = Mage::getModel('auit_publicationbasic/template')->load($templateId);
    					if ( $model->getId() ) {
    						Mage::helper('auit_publicationbasic/pdf')->createPreviewImage($model,$path);
    					}
    				}
    			}
    		}
    		*/
    		$url = Mage::getBaseUrl('js').'auit/publicationbasic/images/no-imagick.jpg';
    		if ( $path && file_exists($path) )
    		{
    			$fileName=basename($path);
	    		$url=Mage::getBaseUrl('media').'catalog/product/cache/_snm_publication_previews/'.$fileName.'?t='.$update_time;
    		}
    		$this->getResponse()->setRedirect($url);
    	}
    }
}
