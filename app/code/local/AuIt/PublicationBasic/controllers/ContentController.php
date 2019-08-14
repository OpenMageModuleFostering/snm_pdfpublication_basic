<?php
class AuIt_PublicationBasic_ContentController extends Mage_Core_Controller_Front_Action
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
    public function datasheetAction()
    {
    	try {
	    	if ( ($sku = base64_decode($this->getRequest()->getParam('skub64')) )  
	    			||
	    		 ($sku = trim($this->getRequest()->getParam('sku')))
	  			)
	    	{
	    		$_product = Mage::getModel('catalog/product');
	    		$_product->setStoreId(Mage::app()->getStore()->getId());
	    		$pid = $_product->getIdBySku($sku);
	    		if ( $pid )
	    			$_product->load($pid);

	    		$toName = 'product_info_'.$sku.'.pdf';
	    		$directory = Mage::getBaseDir('media') . DS.'catalog'.DS.'product'.DS.'cache'.DS.'_snm_publication_previews'.DS;
	    		$path = $directory.$toName;
	    		
	    		if ( !Mage::getStoreConfigFlag('auit_publicationbasic/product_pdf/use_cache') || !file_exists($path) || filemtime($path) < strtotime($_product->getUpdatedAt()))
	    		{
	    			$io = new Varien_Io_File();
	    			if ( !$io->isWriteable(dirname($path)) && !$io->mkdir(dirname($path), 0777, true)) {
	    				$msg = Mage::helper('catalog')->__("Cannot create writeable directory '%s'.", $path);
	    				Mage::log($msg );
	    				Mage::getSingleton('adminhtml/session')->addError($msg );
	    				$this->_forward('noRoute');
	    				return;
	    			}
	    		 
		    		$model = null;
		    		$tuid= $this->getRequest()->getParam('tuid');
		    		if ( $tuid )
		    		{
		    			if ( $tuid[0] == 'J')
		    			{
		    				$model = Mage::getModel('auit_publicationbasic/jobqueue')->load(substr($tuid,1));
		    				if ( !$model->getId() )
		    					$model=null;
		    			}else {	
			    			$model = Mage::getModel('auit_publicationbasic/template')->load($tuid,'identifier');
			    			if ( !$model->getId() )
			    				$model=null;
		    			}
		    		}
		    		if ( !$model )
		    		{
			    		$tid= (int)$this->getRequest()->getParam('tid');
			    		if ( !$tid ){
			    			$tid = Mage::helper('auit_publicationbasic/pdf')->getDataSheetTemplateId($_product);
			    		}
			    		if ( $tid && $tid[0] == 'J')
			    		{
			    			$model = Mage::getModel('auit_publicationbasic/jobqueue')->load(substr($tid,1));
			    		}else {
			    			$model = Mage::getModel('auit_publicationbasic/template')->load($tid);
			    		}
		    		}	
		    		if ( $model->getId() )
		    		{
		    			file_put_contents($path, $model->createDataSheet($sku,Mage::app()->getStore()->getId()));
		    		}
	    		}
	    		if ( file_exists($path) )
	    		{
	    			$this->_prepareDownloadResponse($toName, file_get_contents($path), 'application/pdf');
	    			$this->_sendResponse();
	    			exit();
//	    			$directory = Mage::getBaseUrl('media') . DS.'catalog'.DS.'product'.DS.'cache'.DS.'_snm_publication_previews'.DS;
	//    			$path = $directory.$toName;
	  //  			$this->getResponse()->setRedirect($path);
	    			return;
	    		}
	    		 
	    	}
    	}
    	catch ( Exception $e )
    	{
    		Mage::logException($e);
    	}
   		$this->_forward('noRoute');
    }
    
    public function templAction()
    {
    	$data=null;
    	if ( $tid = $this->getRequest()->getParam('tid') )
    	{
    		$model = Mage::getModel('auit_publicationbasic/template')->load($tid,'identifier');
    		if ( $model->getId() )
    		{
    			$pid = $this->getRequest()->getParam('pid');
    			$store= $this->getRequest()->getParam('store');
    			$obj = Mage::helper('auit_publicationbasic')->cleanLayoutData($model->getData('data'));
				if ( !$pid )    			
					$pid=$obj['preview_sku'];
				if ( !$store )
					$store=$obj['preview_store'];
				$type= $model->getType();
				
    			$data['data']=$obj;
    			$data['productData']=Mage::helper('auit_publicationbasic')->getPreviewData($pid,$type,$store);
    			$data = Mage::helper('core')->jsonEncode($data);    			 
    		}
    	}else {
    		// GRoup produkt daten anfordern
    		$pid = $this->getRequest()->getParam('pid');
    		$store= $this->getRequest()->getParam('store');
    		
    		$data['productData']=Mage::helper('auit_publicationbasic')->getPreviewData($pid,AuIt_PublicationBasic_Helper_Data::TEMPLATE_PRODUCT,$store);
    		$data = Mage::helper('core')->jsonEncode($data);
    	}
    	if ( !$data )$data=Zend_Json::encode(array());
    	$this->getResponse()->setHeader('Content-type', 'application/json; charset=UTF-8');
    	$this->getResponse()->setBody($data);
    }
    public function blockAction()
    {
    	$data=array('html'=>'');
    	if ( $tid = $this->getRequest()->getParam('tid') )
    	{
    		$store= $this->getRequest()->getParam('store');
    		$data['html']=Mage::helper('auit_publicationbasic')->getStaticBlockHTML($tid,$store);
    	}
    	$this->getResponse()->setHeader('Content-type', 'application/json; charset=UTF-8');
    	$this->getResponse()->setBody(Zend_Json::encode($data));
	}
    public function generatorAction()
    {
    	$data=array('html'=>'');
    	if ( $tid = $this->getRequest()->getParam('tid') )
    	{
    		$store= $this->getRequest()->getParam('store');
    		$param= $this->getRequest()->getParam('param');
    		$pid= $this->getRequest()->getParam('pid');
    		$cls= $this->getRequest()->getParam('cls');
    		$data=Mage::helper('auit_publicationbasic')->getGeneratorHTML($param,$cls,$tid,$pid,$store);
    	}
    	$this->getResponse()->setHeader('Content-type', 'application/json; charset=UTF-8');
    	$this->getResponse()->setBody(Zend_Json::encode($data));
	}
    public function imageAction()
    {
        if ( $lbc = $this->getRequest()->getParam('lbc') )
    	{
    		$code = $this->getRequest()->getParam('data');
    		$class = $this->getRequest()->getParam('class');
    		$styleid = $this->getRequest()->getParam('styleid');

    		$logo = $this->getRequest()->getParam('logo');
			require_once 'snm3/auit/barcode.php';
			if ( $logo )
			{
				$helper = Mage::helper('auit_publicationbasic/filemanager');
				$logo = $helper->convertIdToPath($logo,false);
				if ( !is_file($logo) )
					$logo = false;
			}
    		$color = Mage::helper('auit_publicationbasic/style')->getComputedStyle($styleid,$class,'colour','black');
    		$b1d=array('EAN13','C128','C128A','C128B','C128C','C39');
			if ( in_array($lbc,$b1d )  )
			{
    			$barcode = new AuIt_Barcode1D($code, $lbc);
    			$w = $this->getRequest()->getParam('bw');
    			$h = $this->getRequest()->getParam('bh');
    			$code = $barcode->getBarcodeAsPng($logo,$w, $h, $color);
			}else {
				$barcode = new AuIt_Barcode($code, $lbc);
				$code = $barcode->getBarcodeWithLogo($logo,10, 10, $color);
								
			}
    		//$this->getResponse()->setHeader('Content-type', 'image/svg+xml');
    		$this->getResponse()->setHeader('Content-type', 'image/png');
    		$this->getResponse()->setBody($code);
	    	return;
    	}
    	if ( $limg = $this->getRequest()->getParam('limg') )
    	{
	    	$helper = Mage::helper('auit_publicationbasic/filemanager');
	    	
	    	$path = $helper->convertIdToPath($limg,false);
	    	if ( !is_file($path) )
	    	{
	    		
	    		$limg='ROOT/notfound.jpg';
	    		$path = $helper->convertIdToPath($limg,false);
	    	}
	    		
	    	if ( is_file($path) )
	    	{
	    	//	header_remove();
	    		Header('Location:'.$helper->getUrl($path));
	    		exit();
	    		/*
	    		$this->getResponse()->setHeader('Content-type', $helper->getMIMEType($path));
	    		$this->getResponse()->setBody(file_get_contents($path));
	    		*/
	    	}
    	}
       	else if ( $limg = $this->getRequest()->getParam('ldpi') )
    	{
	    	$helper = Mage::helper('auit_publicationbasic/filemanager');
	    	if ( substr($limg,0,4) != 'http')
	    		 $limg= $helper->convertIdToPath($limg,false);
	    	else {
	    		$limg = $helper->convertUrlToPathArea($limg);
	    		$limg= $helper->convertIdToPath($limg,false);
	    	}
    		$this->getResponse()->setBody($helper->getResolution($limg));
    	}
    }
    public function dataAction()
    {
    	$data=array();
    	if ( $style_class = $this->getRequest()->getParam('style_class') )
    	{
    		$data=Mage::helper('auit_publicationbasic/style')->getCssClasses(array('preview_style'=>$style_class) );
    		$this->getResponse()->setHeader('Content-type', 'application/json; charset=UTF-8');
    		$this->getResponse()->setBody(Zend_Json::encode($data));
    		return;
    	}
    	$store = $this->getRequest()->getParam('store');
		if ( $sku = $this->getRequest()->getParam('sku') )
    	{
    		if ( $type = $this->getRequest()->getParam('type') )
	    		$data = Mage::helper('auit_publicationbasic')->getPreviewData($sku,$type,$store);
		}
    	$this->getResponse()->setHeader('Content-type', 'application/json; charset=UTF-8');
    	$this->getResponse()->setBody(Zend_Json::encode($data));
    }
    /*
    public function searchAction()
    {
    	$data=array();
    	if ( $term = $this->getRequest()->getParam('term') )
    	{
    		$helper = Mage::getResourceHelper('core');
    		$likeExpression = $helper->addLikeEscape($term, array('position' => 'any'));
    		$collection = Mage::getResourceModel('catalog/product_collection')
    		->setStore(Mage::app()->getStore())
    		->addAttributeToSelect('name')
    		->addAttributeToFilter('visibility', array(
    				'neq' => Mage_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE))
    		->addAttributeToFilter('sku',array('like'=>$likeExpression));
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
    */
    public function cssAction()
    {
    	$css='';
    	$file   = Mage::getModuleDir('data', 'AuIt_PublicationBasic') . DS . 'css'.DS.'default.css';
    	if ( file_exists($file) )
    		$css=file_get_contents($file);
    		 
    	if ( $style_id = $this->getRequest()->getParam('style_id') )
    	{
    		$css .= Mage::helper('auit_publicationbasic/style')->getCss($style_id,'identifier');
    	}
    	$this->getResponse()->setHeader('Content-type', 'text/css; charset=UTF-8');
    	$this->getResponse()->setBody($css);
    }
    public function clipsvgAction()
    {
    	$this->getResponse()->clearAllHeaders();
    	header('Date: '.gmdate('D, d M Y H:i:s \G\M\T', time()));
    	header('Last-Modified: '.gmdate('D, d M Y H:i:s \G\M\T', time()));
    	
    	header("Cache-Control: public, max-age=" . 3600*24);
    	header('Expires: '.gmdate('D, d M Y H:i:s \G\M\T', time() + 3600*24));
    	
    	$this->getResponse()->setHeader('Content-type', 'image/svg+xml')
    	->setHeader('Pragma', 'public', true)
   // 	->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true)
    //	->setHeader('Date: '.gmdate('D, d M Y H:i:s \G\M\T', time()))
    	//->setHeader('Last-Modified: '.gmdate('D, d M Y H:i:s \G\M\T', time()))
    	//->setHeader('Expires: '.gmdate('D, d M Y H:i:s \G\M\T', time() + 3600))
    	;
    	
    	$this->getResponse()->setBody(Mage::helper('auit_publicationbasic/svg')->getDynSvg($this->getRequest()->getParam('mode'),$group = $this->getRequest()->getParam('group'),$this->getRequest()->getParam('file')));
    	$this->_sendResponse();
    	exit();
    }
}
