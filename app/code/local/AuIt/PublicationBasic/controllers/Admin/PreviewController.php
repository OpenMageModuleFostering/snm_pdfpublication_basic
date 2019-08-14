<?php
class AuIt_PublicationBasic_Admin_PreviewController extends Mage_Adminhtml_Controller_Action
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
    
    public function pdfAction()
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
    public function jobAction()
    {
    	$pdfPublication = Mage::getModel('auit_publicationbasic/renderer_pdf');
    	try {
    		if ( $this->getRequest()->getParams() )
    		{
    			$job =Mage::getModel('auit_publicationbasic/jobqueue');
    			$job->setData($this->getRequest()->getParams());
    			//Mage::log($job->getData());
    			$pdf = $pdfPublication->getPdfFromJobData('preview',$job);
    			$this->_prepareDownloadResponse('preview_'.Mage::getSingleton('core/date')->date('Y-m-d_H-i-s').'.pdf', $pdf->render(), 'application/pdf');
    			$this->_sendResponse();
    		}
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
    public function productpreviewAction()
    {
    	
    	if ( $sku = $this->getRequest()->getParam('tid') )
    	{
    		try {
    			
    		//	$sku =rawurldecode($sku);
    			
	    		$product = Mage::helper('catalog/product')->getProduct($sku,null,'sku');
	    		$url=''.Mage::helper('catalog/image')->init($product, 'thumbnail')->resize(75, 75);
	    		$this->getResponse()->setRedirect($url);
	    		return ;
    		}
	    	catch (Exception $p)
	    	{
	    		
	    	}
    	}
    	//$url='data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVQYV2NgYAAAAAMAAWgmWQ0AAAAASUVORK5CYII=';
    	$this->getResponse()->setRedirect( Mage::getBaseUrl('js').'spacer.gif');
    	return;// 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVQYV2NgYAAAAAMAAWgmWQ0AAAAASUVORK5CYII=';
    }
    public function templatepreviewAction()
    {
    	if ( $templateId = $this->getRequest()->getParam('tid') )
    	{
    		$update_time = $this->getRequest()->getParam('upd');
    		$spread = $this->getRequest()->getParam('spread');
    		if ( $spread == 0 )$spread=1;
    		$path = Mage::helper('auit_publicationbasic/pdf')->getPreviewImage($templateId,$update_time,true,$spread);
    		$url = Mage::getBaseUrl('js').'auit/publicationbasic/images/no-imagick.jpg';
    		if ( $path && file_exists($path) )
    		{
    			$fileName=basename($path);
	    		$url=Mage::getBaseUrl('media').'catalog/product/cache/_snm_publication_previews/'.$fileName.'?t='.$update_time;
    		}
    		$this->getResponse()->setRedirect($url);
    	}else if ( $jobId = $this->getRequest()->getParam('jid') )	{
    		$update_time = $this->getRequest()->getParam('upd');
			$job = Mage::getModel('auit_publicationbasic/jobqueue')->load($jobId);
    		$path = $job->getPreviewImage();
    		$url = Mage::getBaseUrl('js').'auit/publicationbasic/images/no-imagick.jpg';
    		if ( $path && file_exists($path) )
    		{
    			$fileName=basename($path);
    			$url=Mage::getBaseUrl('media').'catalog/product/cache/_snm_publication_previews/'.$fileName.'?t='.$update_time;
    		}
    		$this->getResponse()->setRedirect($url);
    		
    	}else {
    		// #3ebac6
    		$this->getResponse()->setRedirect( Mage::getBaseUrl('js').'auit/publicationbasic/images/empty_template.png');
    		//$this->getResponse()->setRedirect( Mage::getBaseUrl('js').'auit/publicationbasic/images/trash.svg');
    	}
    }
    public function infoAction()
    {
    	try {
    		$cmd = $this->getRequest()->getParam('operation');
    		switch ( $cmd )
    		{
    			case 'staticvariables':
    				$this->_staticvariables();
    				break;
    				break;
    		}
    	} catch ( Exception $e ) {
    			
    		Mage::log ( "AuIt_PublicationBasic_Admin_TemplatesController::Exception - " . $e->getMessage () );
    		$this->getResponse ()->setHeader ( 'Content-type', 'application/json; charset=UTF-8' );
    		$this->getResponse ()->setBody ( Zend_Json::encode ( array (
    				'status' => 0,
    				'error' => $e->getMessage ()
    		) ) );
    		return;
    	}
    }
    protected function _staticvariables()
    {
    	$id = $this->getRequest()->getParam('id');
    	$model = Mage::getModel('auit_publicationbasic/template')->load($id);
    	$data=array();
    	if ( $model->getId() )
    	{
    		$data = $model->buildObjectList();
    	}
    	$this->getResponse()->setHeader('Content-type', 'application/json; charset=UTF-8');
    	$this->getResponse()->setBody(Zend_Json::encode($data));
    }
    
}
