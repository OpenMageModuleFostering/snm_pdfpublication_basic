<?php

class AuIt_PublicationBasic_Model_Jobqueue extends Mage_Core_Model_Abstract
{
    const CACHE_TAG     = 'auit_publicationbasic_jobqueue';
    protected $_cacheTag= 'auit_publicationbasic_jobqueue';
    
    const STATE_WAIT = 0;
    const STATE_START_NOW = -1;
    const STATE_IN_PROGRESS = 1;
    const STATE_HOLD = 2;
    const STATE_CANCELED = 50;
    const STATE_EXCEPTION = 98;
    const STATE_COMPLETED = 99;
    
    const TYPE_COUPON_CARD 	= 100;
    const TYPE_BROCHURE_LIST= 200;
    const TYPE_EBOOK_EPUB3= 300;
    
    protected $_layoutArray;
    protected function _construct()
    {
        $this->_init('auit_publicationbasic/jobqueue');
    }
    public function getVersion()
    {
    	if ( $this->getLayoutDef() )
    		return 2;
    	return 1;
    }
    public function getTemplates()
    {
    	$tmpls = $this->getLayoutInfo('templates');
    	return $tmpls?$tmpls:array();
    }
    public function getJobStyle()
    {
    	return $this->getLayoutInfo('jobstyle');
    	 
    }

    public function getLayoutInfo($key=null)
    {
    	if ( is_null($this->_layoutArray) )
    	{
    		$this->_layoutArray = Mage::helper('core')->jsonDecode($this->getLayoutDef());
    		if ( !is_array($this->_layoutArray))
    			$this->_layoutArray=array();
    	}
    	if ( !$key )
    		return $this->_layoutArray;
    	if ( isset($this->_layoutArray[$key]))
    		return $this->_layoutArray[$key];
    	return null;
    }
    public function initialBasisData()
    {
    }
    //queue_status
    public function setQueueState($newState)
    {
    	$oldStatus = $this->getStatus();
    	switch ($newState)
    	{
    		case self::STATE_WAIT:
    			//if ( !$oldStatus )
    				$this->setStatus($newState);
			break;    		
    		case self::STATE_START_NOW:
    			if ( $oldStatus  != self::STATE_IN_PROGRESS)
    				$this->setStatus($newState);
			break;    		
			case self::STATE_IN_PROGRESS:
    			if ( $oldStatus  != self::STATE_COMPLETED && $oldStatus  != self::STATE_CANCELED)
    			{
    				$this->setStatus($newState);
    			}
    			//$this->runJob();
    			break;    		
    		case self::STATE_HOLD:
    			//if ( $oldStatus  == self::STATE_IN_PROGRESS)
    				$this->setStatus($newState);
    		break;    		
    		case self::STATE_CANCELED:
    			if ( $oldStatus  == self::STATE_IN_PROGRESS)
    				$this->setStatus($newState);
    		break;    		
    		case self::STATE_COMPLETED:
    		case self::STATE_EXCEPTION:
    			$this->setStatus($newState);
    		break;    		
    	}
    }
    
    public function setQueueStartAtByString($startAt)
    {
    	if(is_null($startAt) || $startAt == '') {
    		$this->setQueueStartAt(null);
    	} else {
    		$locale = Mage::app()->getLocale();
    		$format = $locale->getDateTimeFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);
    		$time = $locale->date($startAt, $format)->getTimestamp();
    		$this->setQueueStartAt(Mage::getModel('core/date')->gmtDate(null, $time));
    	}
    	return $this;
    }
    public function getJobRoot()
    {
    	return $this->getResource()->getJobRoot();
    }
    public function getJobFilePath()
    {
    	return $this->getResource()->getJobFilePath($this);
    }
    public function getFilename()
    {
    	return $this->getResource()->getFilename($this);
    }
    public function downloadExists()
    {
    	return file_exists($this->getJobFilePath() . DS . $this->getFileName());
    }
    public function getFileSize()
    {
    	if (  $this->downloadExists() )
    		return filesize($this->getJobFilePath() . DS . $this->getFileName());
    	return 0;
    }
    public function outputFile()
    {
	    $ioAdapter = new Varien_Io_File();
    	if (  $this->downloadExists() )
    	{
		    $ioAdapter->open(array('path' => $this->getJobFilePath()));
		    
		    $ioAdapter->streamOpen($this->getFileName(), 'r');
		    while ($buffer = $ioAdapter->streamRead()) {
		    	echo $buffer;
		    }
		    $ioAdapter->streamClose();
    	}
	}
    public function run()
    {
    	
    	$helper = Mage::helper('auit_publicationbasic/filemanager');
    	if ( $helper->checkFolder($this->getJobFilePath()) )
    	{    	
    		//$filename = $output.DS.$this->getFilename();
    		if ( $this->getVariante() == AuIt_PublicationBasic_Model_Jobqueue::TYPE_EBOOK_EPUB3 )
    		{
    			$pdfPublication = Mage::getModel('auit_publicationbasic/renderer_ebook');
    			$pdfPublication->runJob($this);
    		} else {
    			$pdfPublication = Mage::getModel('auit_publicationbasic/renderer_pdf');
    			$pdfPublication->runJob($this);
    		}
    	}	 
    }
    public function runJob()
    {
    	$this->setQueueState(AuIt_PublicationBasic_Model_Jobqueue::STATE_IN_PROGRESS);
    	$this->save();
    	try {
	    	$this->setQueueStatus('');
    		$this->run();
	    	$this->setQueueState(AuIt_PublicationBasic_Model_Jobqueue::STATE_COMPLETED);
	    	$this->setQueueFinishAt(now());
	    	$this->save();
    	}catch ( Exception $e )
    	{
    		$this->setQueueState(AuIt_PublicationBasic_Model_Jobqueue::STATE_EXCEPTION);
    		$this->setQueueStatus($e->getMessage());
    		$this->setQueueFinishAt(now());
    		$this->save();
    	}
    }
    public function getPreviewImage()
    {
    	if (!$this->getId() || !$this->downloadExists()) {
    		return '';
    	}
    	$fileName = $this->getJobFilePath() . DS . $this->getFileName();
    	$asThumb=true;
    	$spread=1;
    	$toName = 'preview_job_'.$this->getId() .'_'.($asThumb?'t':'').'s'.$spread.'.jpg';
    	$directory = Mage::getBaseDir('media') . DS.'catalog'.DS.'product'.DS.'cache'.DS.'_snm_publication_previews'.DS;
    	$path = $directory.$toName;
    	if ( extension_loaded('imagick')) {
    		if ( !file_exists($path) || filemtime($path) < strtotime($this->getUpdateTime()))
    		{
    			$io = new Varien_Io_File();
    			if ( !$io->isWriteable(dirname($path)) && !$io->mkdir(dirname($path), 0777, true)) {
    				$msg = Mage::helper('catalog')->__("Cannot create writeable directory '%s'.", $path);
    				Mage::log($msg );
    				Mage::getSingleton('adminhtml/session')->addError($msg );
    			}else {
   					$this->createPreviewImage($fileName,$path,$asThumb);
    			}
    		}
    	}
    	return $path;
    }
    protected function createPreviewImage($pdf,$path,$asThumb=true)
    {
    	try {
    		$im = new Imagick($pdf.'[0]');
    	//	$im->setResolution(300,300);
    		//$im->setBackgroundColor(new ImagickPixel('white'));
    		//$im->readimageblob($pdf->render());
    		$im->setBackgroundColor(new ImagickPixel('white'));
    		$im = $im->flattenImages();
    		$im->setImageFormat('jpg');
    		$im->setiteratorindex(0);
    		//if ( $asThumb )
    			//$im->scaleImage(400,400,true);
    		$im->writeimage($path);
    		$im->clear();
    		$im->destroy();
    			
    	} catch (Exception $e) {
    		Mage::logException($e);
    	}
    }
    public function createDataSheet($sku,$storeId)
    {
    	$pdfPublication = Mage::getModel('auit_publicationbasic/renderer_pdf');
    	$firstTemplate=true;
    	foreach ( $this->getTemplates() as $template )
    	{
    		$template = new Varien_Object($template);
    		$pdfPublication->setCurrentJobTemplate($template);
    		$model = Mage::getModel('auit_publicationbasic/template')->load($template->getTemplate());
    		if ( $model->getId() )
    		{
    			$data = Mage::helper('core')->jsonDecode($model->getData('data'));
    			$skus = $template->getSkus();
    			if (  $firstTemplate )
    			{
    				$firstTemplate=false;
    				$s = explode(',',$skus);
    				array_shift($s);
    				array_unshift($s,$sku);
    				$skus = implode(',', $s);
    			}
    			$data['preview_sku']=$skus;
    			$data['preview_store']=$storeId;
    			$pdfPublication->renderData($data,$this);
    		}
    	}
    	$pdfPublication->setCurrentJobTemplate(null);
    	return $pdfPublication->getPdfStream();
    }
    
}