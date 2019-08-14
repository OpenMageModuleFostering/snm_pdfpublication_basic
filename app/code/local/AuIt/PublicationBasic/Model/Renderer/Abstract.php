<?php 
class AuIt_PublicationBasic_Model_Renderer_Abstract extends Varien_Object
{
	protected $_ProductData=array();
	protected $_mainData;
	protected $_ProductArray=array();
	protected $_barcodes=array();
	protected $_OriginX;
	protected $_OriginY;
	protected $_Style;
	protected $_backData;
	protected $_processor;
	protected $_pageNr;
	protected $_firstObject;
	protected $_jobQueueObj;
	protected $_currentSpread=0;
	protected $_currentJobTemplate;
	public function __destruct()
	{
		foreach ( $this->_barcodes as $file )
			@unlink($file);
		$this->_barcodes=array();
	}
	
	protected function _getJobQueueVar($blockInfo,$name)
	{
		if ( $this->_currentJobTemplate )
		{
			if ( isset($this->_currentJobTemplate['vars']) )
			{
				foreach ($this->_currentJobTemplate['vars'] as $var )
				{
					if ( $var['uid']==$blockInfo->getUid() && $var['spread']== $this->_currentSpread)
					{
						if ( $var['type'] == 'p_img')
							return $var['src'];
					//	if ( !$var['def'])
						//	Mage::log($var);
						return $var['def'];
					}
				}
			}
		}
		return $blockInfo->getData($name);
	}
		
	protected function _getProcessor()
	{
		if ( !$this->_processor )
		{
			$processor = Mage::getModel('auit_publicationbasic/email_template_filter');
			$data = array(
					'helper'=> Mage::getModel('auit_publicationbasic/email_template_helper')->setProcessor($processor),
//					'page_current'=>$this->_tcpdf->getRSCPage().$this->_tcpdf->getAliasNumPage(),
	//				'page_count'=>$this->_tcpdf->getAliasNbPages(),
			);
	
			$processor->setVariables($data);
			$this->_processor=$processor;
		}
		return $this->_processor;
	}
	protected function getAttribute($code)
	{
		return  isset($this->_ProductData[$code])?$this->_ProductData[$code]:'not found:'.$code;
	}
	protected function addUnit($value,$unit)
	{
		if ( strlen(floatval($value)) == strlen($value) )
			return $value.$unit;
		return $value;
	}
	
	protected function xPOS($blockInfo)
	{
		return $this->_OriginX + $blockInfo->getX();
	}
	protected function yPOS($blockInfo)
	{
		return $this->_OriginY + $blockInfo->getY();
	}
	
	protected function render($tid,$jobQueue)
	{
		$model = Mage::getModel('auit_publicationbasic/template')->load($tid);
		if ( $model->getId() )
		{
			$data = Mage::helper('core')->jsonDecode($model->getData('data'));

			$this->_printMode='jobqueue';
			if ( $jobQueue->getCatalogSkus() )
				$data['preview_sku']=$jobQueue->getCatalogSkus();
				
			$this->renderData($data,$jobQueue);
						
		}
	}
	protected function renderData($data,$jobQueue=null)
	{
		$this->_jobQueueObj=$jobQueue;
		$this->_backData=array();
		$data['original_width']=$data['width'];
		$data['original_height']=$data['height'];
		$data['original_orientation']=$data['orientation'];
		
		$sender = Mage::getModel('core/email_template');
		$storeId = $data['preview_store'];
		if ( $jobQueue && $jobQueue->getPrintStore() )
			$storeId=$jobQueue->getPrintStore();
		if ( !$storeId )
			$storeId ='default';
		
		try {
			$sender->emulateDesign($storeId);
		}
		catch ( Exception $e )
		{
			
		}

		$this->_Style = Mage::getModel('auit_publicationbasic/styles');
		if ( $jobQueue && $jobQueue->getJobStyle() )
		{
			$data['preview_style']=$jobQueue->getJobStyle();
			
		}
		if ( isset($data['preview_style']) && $data['preview_style'])
		{
			$this->_Style->load($data['preview_style']);
		}
		if ( is_null($this->_pageNr))
			$this->_pageNr=0;
		$this->startRender($data);
		$this->drawDataMain($data,$jobQueue);
		$this->endRender($data);
	}
	protected function startRender($data)
	{
	}
	protected function endRender($data)
	{
	}
	protected function drawDataMain($data,$jobQueue)
	{
		$this->drawData($data,0,0,true);
	}
	protected function drawData($data,$oX,$oY,$isBasis=false,$productData=null)
	{
		// Original Document 1:1
		array_push($this->_backData, array(
			'_ProductData'=>$this->_ProductData,
			'_ProductArray'=>$this->_ProductArray,
			'_OriginX'=>$this->_OriginX,
			'_OriginY'=>$this->_OriginY
		));
		$this->_OriginX=$oX;
		$this->_OriginY=$oY;
		
		$type = (int)@$data['object']['type'];
		if ( !$productData )
			$objectDatas=Mage::helper('auit_publicationbasic')->getObjectData($data['preview_sku'],$type,
					Mage::app()->getStore()->getId(),true,$this->_printMode!='preview',$this->_printMode);
		else
			$objectDatas=array($productData);
		//if ( !is_array($objectDatas) )
			//$objectDatas=array($objectDatas);
		
		$this->_ProductArray = $objectDatas;
		while ( $objData = array_shift($this->_ProductArray))
		{
			$this->_firstObject=true;
			$this->_ProductData=$objData;
			$this->_currentSpread=0;
			if ( isset($data['spreads']) )
			foreach ( $data['spreads'] as $spread )
			{
				$this->_currentSpread++;
				if ( $this->_currentJobTemplate && $this->_currentJobTemplate->getUsespread() 
					&& $this->_currentJobTemplate->getUsespread() != $this->_currentSpread)
					continue;
				if ( $isBasis )
					$this->startSpread($data,$spread);
				foreach ( $spread['pages'] as $page )
				{
					$this->startPage($data,$spread,$page);
					$this->drawBoxes($spread['boxes']);
					if ( $isBasis )
						$this->_OriginX+=$data['original_width'];
					$this->endPage($data,$spread,$page);
					if ( !$isBasis )
						break;
				}
				$this->endSpread($data,count($spread['pages']));
				if ( !$isBasis )
					break;
			}
			if ( $this->_printMode == 'preview' )
				break;
		}
		$d = array_pop($this->_backData);
		$this->_ProductData = $d['_ProductData'];
		$this->_ProductArray= $d['_ProductArray'];
		$this->_OriginX= $d['_OriginX'];
		$this->_OriginY= $d['_OriginY'];
	}
	protected function drawBoxes($boxes)
	{
		foreach ( $boxes as $box )
		{
			$this->drawBox($box);
		}
	}
	protected function startPage($data,$spread,$page)
	{
		$this->_pageNr++;
	}
	protected function endPage($data,$spread,$page)
	{
	}
	protected function startSpread($data,$spread)
	{
	}
	protected function endSpread($data,$spread)
	{
	}
	protected function startRenderTemplate($blockInfo)
	{
	}
	protected function endRenderTemplate($blockInfo)
	{
	}
	protected function drawBackgroundBox($blockInfo,$bgcolor)
	{
	}
	protected function drawBox($box)
	{
		
		$blockInfo  = Mage::helper('auit_publicationbasic/style')->getStyleItem($box);
		// Draw Background Box
		if ( $blockInfo->getClass() && $this->_Style || $blockInfo->getStyleColourBackground())
		{
			if ( $blockInfo->getStyleColourBackground() )
				$bgcolor = $blockInfo->getStyleColourBackground();
			else
				$bgcolor = $this->_Style->getComputedStyle($blockInfo->getClass(),'colour_background','');
			if ( $bgcolor )
			{
				$this->drawBackgroundBox($blockInfo,$bgcolor);
			}
		}
		switch ( $box['type'])
		{
			case 'p_img':
				$this->drawImage($blockInfo);
				break;
			case 'p_bc':
				$this->drawBarcode($blockInfo);
				break;
			case 'p_group':
				$this->renderGroup($blockInfo);
				break;
			case 'p_templ':
				$this->renderTemplate($blockInfo);
				break;
			default:
				$this->drawTextBox($this->getBoxText($blockInfo),$blockInfo);
				break;
		}
	}
	protected function renderChilds($blockInfo)
	{
		$xoff=$blockInfo->getX() ; $yoff=$blockInfo->getY();
		foreach ( $blockInfo->getChilds() as $child )
		{
			$child['x'] += $xoff;
			$child['y'] += $yoff;
			$this->drawBox($child);
		}
	}
	protected function renderGroup($blockInfo)
	{
		array_push($this->_backData, array(
		'_ProductData'=>$this->_ProductData,
		'_OriginX'=>$this->_OriginX,
		'_OriginY'=>$this->_OriginY
		));

		$sku = $blockInfo->getPOpt3();
		if ( $sku != '*'  )
		{
			if ($sku && $this->_printMode == 'preview' && !$this->_currentJobTemplate)
			{
				$objectDatas=Mage::helper('auit_publicationbasic')->getObjectData($sku,AuIt_PublicationBasic_Helper_Data::TEMPLATE_PRODUCT,
						Mage::app()->getStore()->getId(),true,$this->_printMode!='preview',$this->_printMode);
				$this->_ProductData = array_shift($objectDatas);
			}else if ( $this->_currentJobTemplate){
				if ( !$this->_firstObject)
					$this->_ProductData = array_shift($this->_ProductArray);
				$this->_firstObject=false;
			}
		}
		if ( $this->_ProductData)
		{
			$this->renderChilds($blockInfo);
		}

		$d = array_pop($this->_backData);
		$this->_ProductData = $d['_ProductData'];
		$this->_OriginX= $d['_OriginX'];
		$this->_OriginY= $d['_OriginY'];
		
	}
	protected function renderTemplate($blockInfo)
	{
		$tid = $blockInfo->getPOpt();
		$model = Mage::getModel('auit_publicationbasic/template')->load($tid);
		if ( $model->getId() )
		{
			$obj = Mage::helper('auit_publicationbasic')->cleanLayoutData($model->getData('data'));
			$productData = null;
			$sku = $blockInfo->getPOpt3();
			if ( $sku == '*' )
				$productData = $this->_ProductData;
	
			if ( $sku  )
				$obj['preview_sku']=$sku;

			
			$this->startRenderTemplate($blockInfo);
			$this->drawData($obj,$this->xPOS($blockInfo),$this->yPOS($blockInfo),false,$productData);
			$this->endRenderTemplate($blockInfo);
		}
	}
	protected function format($value,$fkt)
	{
		if ( $fkt == 'AL.digits(value)' )
		{
			return (int)$value;
		}else if ( $fkt == 'AL.decimals(value,2)' )
		{
			$dec = 2;
			if ( !$dec ) $dec=2;
			$f  = explode('.',$value);
			if ( count($f) > 1 )
			{
				$f = $f[1];
				$f = substr($f,0,$dec+1);
				while ( strlen($f) < ($dec+1) )
					$f+='0';
				$f = ((int)$f) / 10;
				$f =  round($f);
				return $f;
			}
			return '00';
		}
		return $value;
	}
	protected function getBoxText($blockInfo)
	{
		switch ( $blockInfo->getType() )
		{
			case 'p_attr':
				return $this->getAttribute($blockInfo->getPOpt());
				break;
			case 'p_price':
				$txt  =  $this->getAttribute($blockInfo->getPOpt());
				if ( $blockInfo->getPOpt2())
				{
					$txt  = $this->format($txt,$blockInfo->getPOpt2());
				}
				return $txt;
			break;
			case 'p_block':
				return  $this->_getProcessor()->filter("{{block id='".$blockInfo->getPOpt()."'}}");
			break;
			case 'p_gen':
				$data =  Mage::helper('auit_publicationbasic')->getGeneratorHTML($blockInfo->getPOpt3(),$blockInfo->getClass(),$blockInfo->getPOpt(),$this->getAttribute('sku'));
				if ( isset($data['childs']) && count($data['childs']) )
				{
					$xoff=$blockInfo->getX() ; $yoff=$blockInfo->getY();
					foreach ( $data['childs'] as $child )
					{
						$child['x'] += $xoff;
						$child['y'] += $yoff;
						$this->drawBox($child);
					}
					return '';
				}
				return $data['html'];
				break;
			case 'p_free':
				$text = $this->_getJobQueueVar($blockInfo,'p_opt');
				if ( $text )
					return $this->_getProcessor()->filter($text);
				//if ( $blockInfo->getPOpt() )
					//return $this->_getProcessor()->filter($blockInfo->getPOpt());
				return '';
				break;
			default:
				Mage::log("getBoxText: unknown :".$blockInfo->getType());
				break;
		}
		return $blockInfo->getType().' not implemented';
	}
	
	protected function drawTextBox($txt,$blockInfo)
	{
		if ( !($txt=trim($txt)) )
			return;
	}
	protected function drawImage($blockInfo)
	{
		if ( $blockInfo->getPOpt() == 'media_static' )
		{
			$helper = Mage::helper('auit_publicationbasic/filemanager');
			$src = $this->_getJobQueueVar($blockInfo,'src');
			$file = $helper->convertIdToPath($src,false);
		}else {
			$file ='';
			if ( trim($blockInfo->getPOpt()) )
				$file = Mage::getBaseDir('media') . DS.'catalog'.DS.'product'.DS.$this->getAttribute(trim($blockInfo->getPOpt()));
		}
		$this->drawImageFile($blockInfo,$file);
	}
	protected function drawImageFile($blockInfo,$file)
	{
		
	}
	protected function calcImagePosition($blockInfo,$file,$pdfDpi=72)
	{
		$helper = Mage::helper('auit_publicationbasic/filemanager');
		$imgDpi = $helper->getResolution($file);
	
		$x=$this->xPOS($blockInfo); 
		$y=$this->yPOS($blockInfo);
		$w=$blockInfo->getW(); 
		$h=$blockInfo->getH();
		
		$xoff = $blockInfo->getXoff();
		$yoff = $blockInfo->getYoff();
		$imsize = false;
		if ( strtolower(pathinfo($file, PATHINFO_EXTENSION)) == 'svg')
		{
			if (extension_loaded('imagick')) { // ImageMagick extension
				$img = new Imagick($file);
				$imsize = array();
				$imsize[] = $img->getimagewidth();
				$imsize[] = $img->getImageHeight();
			}
		}
		else
			$imsize = @getimagesize($file);
		if (  $imsize !== false)
		{
			$iw=0;	    $ih=0;
			$resize=false;
			$scale = $blockInfo->getScale()?$blockInfo->getScale():100;
			$scale /= 100;
				
			$helper = Mage::helper('auit_publicationbasic/filemanager');
			$imgDpi =72;
			if ( !$this->_mainData || ((int)$this->_mainData['version']) < 2)
				$imgDpi = $helper->getResolution($file);
			
			$f = $pdfDpi / ($imgDpi?$imgDpi:$pdfDpi);
		
			list($pixw, $pixh) = $imsize;
			
			if ( $blockInfo->getPOpt2() == 'fit2box' || $blockInfo->getPOpt2() == 'fill2box' )
				$scale =1;
			if ( $blockInfo->getType() == 'p_bc')
			{
				$imageMM = ($pixw*$f*25.4) / $pdfDpi;
				$scale = floatval($w) / floatval($imageMM);
			}
			$iw=$pixw * $scale;
			$ih=$pixh * $scale;
			$iw= $iw * $f;
			$ih= $ih * $f;
		
			$iw= ($iw*25.4) / $pdfDpi;
			$ih= ($ih*25.4) / $pdfDpi;
			
				
			if ( $blockInfo->getPOpt2() == 'fit2box' || $blockInfo->getPOpt2() == 'fill2box' )
			{
				$boxW = $w;
				$boxH = $h;
				$iRatio=1;
				if ($iw > $boxW || $ih > $boxH)
				{
					$iRatio = $iw / $ih;
					$newRatio = $boxW / $boxH;
					$resizeRatio=0;
					if ($iRatio > $newRatio)
					{
						$resizeRatio = $boxW / $iw;
						$newWidth = $iw * $resizeRatio;
						$newHeight = $newWidth / $iRatio;
					}
					else
					{
						$resizeRatio = $boxH / $ih;
						$newHeight = $ih * $resizeRatio;
						$newWidth = $newHeight * $iRatio;
					}
				}
				else
				{
					$newWidth = $iw;
					$newHeight = $ih;
				}
				$l = ($boxW-$newWidth)/2;
				$t = ($boxH-$newHeight)/2;
				if ( $blockInfo->getPOpt2() == 'fill2box' )
				{
					if ( $l > 0 )
					{
						$newWidth = $boxW;
						$l=0;
						$newHeight = $boxW / $iRatio;
						$t = ($boxH-$newHeight)/2;
					}else {
						$l=0;$t=0;
						$newHeight = $boxH;
						$newWidth = $newHeight * $iRatio;
						$newWidth = $newWidth;
						$l = ($boxW-$newWidth)/2;
					}
				}
				$xoff=$l;
				$yoff=$t;
				$iw=$newWidth;
				$ih=$newHeight;
			}
			return array('x'=> $xoff,
						'y'=>$yoff,
						'w'=>$iw,
						'h'=>$ih);
		}
		return false;
	}
	protected function getBarcodeImg($blockInfo)
	{
		
		$logo = trim($blockInfo->getSrc());
		if ( $logo ){
			$helper = Mage::helper('auit_publicationbasic/filemanager');
			$logo = $helper->convertIdToPath($logo,false);
		}
		$lbc  = $blockInfo->getPOpt();
		$opt = 'bc_url_website';
		if ( $blockInfo->getPOpt2() )
			$opt = $blockInfo->getPOpt2();
			
		if ( $blockInfo->getPOpt2() == 'bc_free_text' )
			$code = $blockInfo->getPOpt3();
		else  
			$code = $this->getAttribute($blockInfo->getPOpt2());
		
		$class = $blockInfo->getClass();
		$color = 'black';
		if ( $this->_Style ){
			$color = $this->_Style->getComputedStyle($class,'colour',$color);
		//$color = Mage::helper('auit_publicationbasic/style')->getComputedStyle($styleid,$class,'colour','black');
		}
		
		$key = 	$code.':'.$lbc.':'.$logo.':'.$color;
		
		if ( isset($this->_barcodes[$key]) )
		{
			return $this->_barcodes[$key];
		}
		require_once 'snm3/auit/barcode.php';
			
		$b1d=array('EAN13','C128','C128A','C128B','C128C','C39');
		
		if ( in_array($lbc,$b1d )  )
		{
			$barcode = new AuIt_Barcode1D($code, $lbc);
			$w=$blockInfo->getW();
			$h=$blockInfo->getH();
			$w=AuIt_PublicationBasic_Block_Frame::MM2Px($w);
			$h=AuIt_PublicationBasic_Block_Frame::MM2Px($h);
			$fileStream = $barcode->getBarcodeAsPng($logo,$w, $h, $color);
		}else {
			$barcode = new AuIt_Barcode($code, $lbc);
			$fileStream = $barcode->getBarcodeWithLogo($logo,10, 10, $color);
		}
		if ( $fileStream !== false )
		{
			$file = tempnam(Mage::getConfig()->getOptions()->getTmpDir(), 'img_');
			$fp = fopen($file.'.png', 'w');
			fwrite($fp, $fileStream);
			fclose($fp);
			unset($fileStream);
			//$this->drawImageFile($box,'test.png');
			//$this->drawImageFile($blockInfo,$file.'.png');
			@unlink($file);
			//			@unlink($file.'.png');
			$this->_barcodes[$key]=$file.'.png';
			return $file.'.png';
		}
		return false;
		
	}
	protected function drawBarcode($blockInfo)
	{
		if ( ($fn=$this->getBarcodeImg($blockInfo)) )
			$this->drawImageFile($blockInfo,$fn);
	}
}