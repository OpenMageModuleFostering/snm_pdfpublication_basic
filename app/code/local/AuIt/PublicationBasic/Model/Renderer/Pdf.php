<?php
class AuIt_PublicationBasic_Model_Renderer_Pdf extends AuIt_PublicationBasic_Model_Renderer_Abstract
{
	protected $_printMode;
	protected $_bShowCropMarks;
	protected $_tcpdf;
	protected $_useBleed=false;
	protected $_streams=array();
	protected $_defaultCss=null;
	protected function getCropSpace()
	{
		return 10;
	}
	protected function getBleedSpace()
	{
		return 2;
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
	
		$orientation= (isset($data['orientation']) && $data['orientation'] == 'p')?'P':'L';
		$spreadW = $data['width'] * ($spread?count($spread['pages']):1);
		$spreadH = $data['height'];
		$format = array('MediaBox'=>array('llx'=>0,'lly'=>0,'urx'=>$spreadW,'ury'=>$spreadH));
		
		$this->_OriginX=0; $this->_OriginY=0;
		if ( $this->_useBleed )
		{
			$bo = $this->getBleedSpace();
			$format['BleedBox']=array('llx'=>$bo,'lly'=>$bo,
					'urx'=>$data['width']-$bo,
					'ury'=>$data['height']-$bo);
			$format['TrimBox']=array('llx'=>$bo,'lly'=>$bo,
					'urx'=>$data['width']-$bo,
					'ury'=>$data['height']-$bo);
			$this->_OriginX=$this->getBleedSpace();
			$this->_OriginY=$this->getBleedSpace();
		}
		$this->_tcpdf->AddPage($orientation,$format);
	}
	
	protected function endSpread($data,$spread){
		
	}
	public function renderData($data,$jobQueue=null)
	{
		if  ( $jobQueue )
		{
			$this->_useBleed = $jobQueue->getUseBleed();
			if ($jobQueue->getUseBleed() )
			{
				$data['width']+= ($this->getBleedSpace()*2);
				$data['height']+=($this->getBleedSpace()*2);
			}
		}
		return parent::renderData($data,$jobQueue);
	}	
	
	protected function drawDataMain($data,$jobQueue)
	{
		$this->_mainData = $data;
		if ( $jobQueue && $jobQueue->getUseDocSize() == 2 ) // Eigenes Format
		{
			$this->drawDocMode2($jobQueue,$data);
			return;
		}
		
			parent::drawDataMain($data,$jobQueue);
	}
	
	public function runJob($jobQueue)
	{
		$this->_printMode='jobqueue';
		if ( $jobQueue->getVersion() >= 2 )
		{
			$this->renderJob2($jobQueue);
		}else {
			$skus = $jobQueue->getCatalogSkus();
			$tid = $jobQueue->getTemplate();
			$this->render($tid,$jobQueue);
		}		
		$filename = $jobQueue->getJobFilePath().DS.$jobQueue->getFilename();
		if ( $this->_tcpdf)
			$this->_tcpdf->Output($filename,'F');
		$this->_tcpdf=null;
	}

	protected function renderJob2($jobQueue)
	{
		foreach ( $jobQueue->getTemplates() as $template )
		{
			$template = new Varien_Object($template);
			$this->_currentJobTemplate=$template;
			$model = Mage::getModel('auit_publicationbasic/template')->load($template->getTemplate());
			if ( $model->getId() )
			{
				$data = Mage::helper('core')->jsonDecode($model->getData('data'));
				$data['preview_sku']=$template->getSkus();
				$this->renderData($data,$jobQueue);
			}
		}
		
		$this->_currentJobTemplate=null;
	}
	public function getPdfStream()
	{
		return $this->_tcpdf->Output('', 'S');
	}
	public function getPdfFromJobData($printMode,$jobQueue)
	{	
		$zendPdf = new Zend_Pdf();
		try {
			$this->_printMode=$printMode;
			$spread =0;
			$this->renderJob2($jobQueue);
			$this->_streams = array();
			if ( $this->_tcpdf )
			$this->_streams[]=$this->_tcpdf->Output('', 'S');
			$this->_tcpdf=null;
			$factory = Zend_Pdf_ElementFactory::createFactory(1);
			foreach ( $this->_streams as $stream )
			{
				$pdf = Zend_Pdf::parse($stream);
				foreach ($pdf->pages as $idx => $page)
				{
					$processed = array();
					if ( $spread > 0 && $spread <= count($pdf->pages) )
					{
		
						if ( $spread == ($idx+1) )
							$zendPdf->pages[] = $page->clonePage($factory, $processed);
					}
					else
						$zendPdf->pages[] = $page->clonePage($factory, $processed);
				}
			}
			$this->_streams=array();
		}
		catch ( Exception $e )
		{
			throw $e;
		}
		return $zendPdf;	
	}
	/**
	 * Direct Preview
	 * @param unknown $printMode
	 * @param unknown $jsonData
	 * @param number $storeId
	 * @throws Exception
	 * @return Zend_Pdf
	 */
	public function getPdfFromData($printMode,$jsonData,$storeId=0,$spread=0)
	{
		$zendPdf = new Zend_Pdf();
		try {
			$this->_printMode=$printMode;
			if ( is_array($jsonData))
				$data = $jsonData;
			else
				$data = Mage::helper('core')->jsonDecode($jsonData);
			$this->renderData($data,null);
			
			$this->_streams = array();
			$this->_streams[]=$this->_tcpdf->Output('', 'S');
			$this->_tcpdf=null;
			$factory = Zend_Pdf_ElementFactory::createFactory(1);
			
			
			foreach ( $this->_streams as $stream )
			{
				$pdf = Zend_Pdf::parse($stream);
				
				foreach ($pdf->pages as $idx => $page)
				{
					$processed = array();
					if ( $spread > 0 && $spread <= count($pdf->pages) )
					{
						
						if ( $spread == ($idx+1) )
							$zendPdf->pages[] = $page->clonePage($factory, $processed);
					}
					else 
						$zendPdf->pages[] = $page->clonePage($factory, $processed);
					//$zendPdf->pages[] = clone ($page);//new Zend_Pdf_Page();
					//$zendPdf->
					//$zendPdf->pages[] = new Zend_Pdf_Page($page);
				}
			}
			$this->_streams=array();
		}
		catch ( Exception $e )
		{
			//$this->_afterGetPdf();
			throw $e;
		}
		return $zendPdf;
	}
	protected function startRender($data)
	{
		require_once 'snm3/auit/pdf.php';
		Mage::getSingleton('core/translate')->setTranslateInline(false);
		if ( is_null($this->_tcpdf))
		{
			$this->_tcpdf = new AuIt_Pdf2($this);
			$this->_tcpdf->setFontSubsetting(false);
		}
	}
	protected function endRender($data)
	{
		Mage::getSingleton('core/translate')->setTranslateInline(true);
		
	}
	protected function startRenderTemplate($blockInfo)
	{
		$this->_tcpdf->StartTransform();
		$this->_tcpdf->Rect($this->xPOS($blockInfo),$this->yPOS($blockInfo), $blockInfo->getW(), $blockInfo->getH(), 'CNZ');
	}
	protected function endRenderTemplate($blockInfo)
	{
		$this->_tcpdf->StopTransform();
	}
	protected function addRotate($blockInfo)
	{
		if ( floatval($blockInfo->getR())  )
		{
			$r = floatval($blockInfo->getR())*-1;
			$this->_tcpdf->Rotate($r,$this->xPOS($blockInfo)+$blockInfo->getW()/2,$this->yPOS($blockInfo)+$blockInfo->getH()/2);
		}
		if ( $blockInfo->getPClipuse() && $blockInfo->getPClip() )
		{
			$path = Mage::helper('auit_publicationbasic/svg')->getPath($blockInfo->getPClip());
			if ( $path && $path['path'] && !isset($path['default']))
			{
				$r = min(array($blockInfo->getW()/6,$blockInfo->getH()/6));
				$sf = 1;
				$size = $size1 =$path['size'];
				$off = $size*0.05;
				$off =0;
				$size += $off*2;
				$bs = min(array((float)$blockInfo->getW(),(float)$blockInfo->getH()));
				$bs  	= $this->_tcpdf->getHTMLUnitToUnits($bs,1, 'mm');
				$size  = $this->_tcpdf->getHTMLUnitToUnits($size,1, 'px');
				$sf = 1 / ( $size/$bs );
				$x = $this->xPOS($blockInfo);
				$y = $this->yPOS($blockInfo);
				$offX=$offY=0;
				if ( (float)$blockInfo->getW() < (float)$blockInfo->getH() )
				{
					$offY = ($blockInfo->getH()- ($size*$sf))/2;
				}
				else {
					$offX = ($blockInfo->getW()- ($size*$sf))/2;
				}
				$pxUnit  = $this->_tcpdf->getHTMLUnitToUnits(1,1, 'px');
				$off = $sf * $off  * $pxUnit;
				//Mage::log("W:{$bs} S:{$size} F:{$sf} X:{$x} Y:{$y} offX:{$offX} offY:{$offY} mmUNit:{$mmUNit} pxUnit:{$pxUnit} off:{$off}");
				$x+=$offX + $off;$y+=$offY + $off;
				$this->_tcpdf->SVGPath2($sf,$x,$y,$path['path'], 'CNZ');
			} 
		}
	}
	protected function drawBackgroundBox($blockInfo,$bgcolor)
	{
		$pdf = $this->_tcpdf;
		$bgcolor = $this->_Style->ColorToArray($bgcolor);
		$pdf->StartTransform();
		if ( isset($bgcolor[3]) )
			$pdf->SetAlpha($bgcolor[3]);
		$pdf->SetFillColor($bgcolor[0], $bgcolor[1], $bgcolor[2]);
		//$pdf->Rect($this->xPOS($blockInfo), $y=$blockInfo->getY(), $blockInfo->getW(), $blockInfo->getH(), 'DF');
		$this->addRotate($blockInfo);
		$pdf->Rect($this->xPOS($blockInfo),$this->yPOS($blockInfo), $blockInfo->getW(), $blockInfo->getH(),'F');
		$pdf->StopTransform();
	}
	protected function resetDefaultCSS()
	{
		$this->_defaultCss ='';
	}
	protected function drawTextBox($txt,$blockInfo)
	{
		if ( !($txt=trim($txt)) )
			return;
		if ( $blockInfo->getTexttransform() )
		{
			switch ($blockInfo->getTexttransform() )
			{
				case 'uppercase':
					$txt = strtoupper($txt);
					break;
				case 'lowercase':
					$txt = strtolower($txt);
					break;
			}
		}
		
		$pdf = $this->_tcpdf;
		//static $defaultCss ='';
		if (!$this->_defaultCss )
		{
			$file   = Mage::getModuleDir('data', 'AuIt_PublicationBasic') . DS . 'css'.DS.'default.css';
			if ( file_exists($file) )
				$this->_defaultCss=file_get_contents($file);
			$this->_defaultCss = str_replace('#auit-layout-frame','',$this->_defaultCss);
	
			$xx = $this->_Style->getCSS($pdf);
			$xx = str_replace('.auit-layout-text-frame','',$xx);
	
			$pdf->setGlobalCSS($xx."\n".$this->_defaultCss);
			
		}
		$cssInfo = $this->_Style->getBoxStyle($pdf,$blockInfo);
		$cssObj=$cssInfo['cssObj'];
		//$pdf->setGlobalCSS($cssInfo['css']."\n".$this->_defaultCss);
		$html='';
		$class = $blockInfo->getClass() ? $blockInfo->getClass() : 'bl-'.$blockInfo->getUid();
		$html .= '<style>';
		$html .= str_replace('.auit-layout-text-frame','',$cssInfo['css']);
		$html .= '</style>';
		
		//style="background-color:transparent"
		$html .= '<div class="auit-layout-pdf-box auit-layout-text-frame '.$class.'"  >';
		$html .= $pdf->cleanHTML($txt);
		$html .='</div>';
			
		//$pdf->setCellPaddings('10mm', 20, '10mm', '10mm');
		$pl = ( isset($cssObj['left_indent']) && $cssObj['left_indent'] )?$cssObj['left_indent']:0;
		$pr = ( isset($cssObj['right_indent']) && $cssObj['right_indent'] )?$cssObj['right_indent']:0;
		$pt = ( isset($cssObj['space_before']) && $cssObj['space_before'] )?$cssObj['space_before']:0;
		$pb = ( isset($cssObj['space_after']) && $cssObj['space_after'] )?$cssObj['space_after']:0;
		$pdf->setCellPaddings(0,0, 0, 0);
		switch ( $blockInfo->getHalign() )
		{
			case 'left':
				$align='L';
				break;
			case 'right':
				$align='R';
				break;
			case 'center':
				$align='C';
				break;
			case 'justify':
				$align='J';
				break;
			default:
				$align='L';
				break;
		}
		$valign=$blockInfo->getValign();
		//	$pdf->setListIndentWidth($width);
			
		//$pdf->setListIndentWidth($pdf->GetStringWidth('00'));
		/*
		 $tagvs = array(
		 		'p' => array(0 => array('h' => 0, 'n' => 0), 1 => array('h' => 0, 'n' => 0)),
		 		'div' => array(0 => array('h' => 0, 'n' => 0), 1 => array('h' => 0, 'n' => 0))
		 );
		$pdf->setHtmlVSpace($tagvs);
		*/
		$pdf->StartTransform();
		
		$this->addRotate($blockInfo);
		
		$pdf->MultiCellStart($blockInfo->getW() -($pl+$pr), $blockInfo->getH() - ($pt+ $pb),
				$html,
				$border=0,
				$align,
				$fill=false,
				$ln=0,
				$x=$this->xPOS($blockInfo) + $pl,
				$y=$this->yPOS($blockInfo) + $pt,
				$reseth=true,
				$stretch=0,
				$ishtml=true,
				$autopadding=false,
				$blockInfo->getH()- ($pt+ $pb),
				$valign,
				$fitcell=false,
				$blockInfo->getBoxtextoption());
		$pdf->StopTransform();
		// 	$pdf->resetColumns();
		//$this->SetAutoPageBreak($page_break_mode, $page_break_margin);
		//CEO
		//return $this->MultiCell($w, $h, $html, $border, $align, $fill, $ln, $x, $y, $reseth, 0, true, $autopadding, 0, 'T', false);
		//$pdf->writeHTMLCell($blockInfo->getW(), $blockInfo->getH(), $this->xPOS($blockInfo), $this->yPOS($blockInfo), $html);
		//public function writeHTMLCell($w, $h, $x, $y, $html='', $border=0, $ln=0, $fill=false, $reseth=true, $align='', $autopadding=true) {
	}
	
	
	protected function drawImageFile($blockInfo,$file)
	{
		$pdf = $this->_tcpdf;
		$x=$this->xPOS($blockInfo) ; $y=$this->yPOS($blockInfo);
		$w=$blockInfo->getW(); $h=$blockInfo->getH();
	
		$oldScale = $pdf->getImageScale();
	
		$xoff = $blockInfo->getXoff();
		$yoff = $blockInfo->getYoff();
		if ( is_file($file) && is_readable($file) )
		{
			//Mage::log("drawFile: $file : ".pathinfo($file, PATHINFO_EXTENSION));
			$oldBreak = $pdf->getAutoPageBreak();
			$pdf->SetAutoPageBreak(false);
	
			$pdf->StartTransform();
			$this->addRotate($blockInfo);
			// Draw clipping rectangle to match html cell.
			$pdf->Rect($x, $y, $w, $h, 'CNZ');
			$iw=0;	    $ih=0;
			$resize=false;
			$scale = $blockInfo->getScale()?$blockInfo->getScale():100;
			$scale /= 100;
	
			//$pdf->setImageScale($scale);
			if ( 0 && strtolower(pathinfo($file, PATHINFO_EXTENSION)) == 'svg')
			{
				if ( $blockInfo->getPOpt2() == 'fit2box' || $blockInfo->getPOpt2() == 'fill2box' )
					$scale =1;
				$imsize = $pdf->getimagesizeSVG($file);
				
				$scale = -1;
				$pdf->ImageSVG($file,
						$x + $xoff,
						$y + $yoff,
						$w,$h,
						$link='',
						$align='',
						$palign='',
						$border=0,
						$fitonpage=true,
						$mainscale=$scale
				);
	
			}else {
				if ( strtolower(pathinfo($file, PATHINFO_EXTENSION)) == 'svg')
					$imsize = $pdf->getimagesizeSVG($file);
				else
					$imsize = @getimagesize($file);
				if (  $imsize !== false)
				{
					$helper = Mage::helper('auit_publicationbasic/filemanager');
					$imgDpi =72;
					if ( !$this->_mainData || ((int)$this->_mainData['version']) < 2)
						$imgDpi = $helper->getResolution($file);
					
					$pdfDpi = 72;
					if ( strtolower(pathinfo($file, PATHINFO_EXTENSION)) == 'svg')
					{
						if ( $blockInfo->getPOpt2() == 'fit2box' || $blockInfo->getPOpt2() == 'fill2box' )
							$pdfDpi = 96;
						else
							$pdfDpi = 153;
					}
					$f = $pdfDpi / ($imgDpi?$imgDpi:$pdfDpi);
	
	
					list($pixw, $pixh) = $imsize;
					if ( $blockInfo->getPOpt2() == 'fit2box' || $blockInfo->getPOpt2() == 'fill2box' )
						$scale =1;
					if ( $blockInfo->getType() == 'p_bc')
					{
						$imageMM = $pdf->pixelsToUnits($pixw*$f);
						$scale = floatval($w) / floatval($imageMM);
					}
					$iw=$pixw * $scale;
					$ih=$pixh * $scale;
					$iw= $iw * $f;
					$ih= $ih * $f;
	
					$iw=$pdf->pixelsToUnits($iw);
					$ih=$pdf->pixelsToUnits($ih);
	
					if ( 1 )
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
							//$ih=0;
						//	$iw=0;
							$ih=$newHeight;
						}
				}
				if ( strtolower(pathinfo($file, PATHINFO_EXTENSION)) == 'svg')
				{
					$pdf->ImageSVG($file,
				
							$x + $xoff,
							$y + $yoff,
							$iw,$ih,
							$link='',
							$align='',
							$palign='',
							$border=0,
							$fitonpage=false
					);
					//Mage::log("$x + $xoff,$y + $yoff,$iw,$ih");
					/* 
					$pdf->ImageSVG($file,
							0,
							0,
							$w,$h,
							$link='',
							$align='',
							$palign='',
							$border=0,
							$fitonpage=false
					);
					*/
				}		
				else
				$pdf->Image($file,
						$x + $xoff,
						$y + $yoff,
						$iw,$ih,
						$type='',
						$link='',
						$align='',
						$resize,
						$dpi=300,
						$palign='',
						$ismask=false,
						$imgmask=false,
						$border=0,
						$fitbox=false,
						$hidden=false,
						$fitonpage=false,
						$alt=false,
						$altimgs=array());
				$pdf->SetAutoPageBreak($oldBreak);
			}
	
			//$pdf->setImageScale($oldScale);
	
			$pdf->StopTransform();
		}
		//   $pdf->Rect($x, $y, $w, $h);
	
	}
	
	/**
	 * Speacial groid meherer auf eine Seite
	 * @param unknown $pageObjs
	 * @param unknown $data
	 * @param unknown $PlaceM
	 * @param unknown $x
	 * @param unknown $y
	 * @param unknown $startX
	 * @param unknown $startY
	 * @param unknown $OW
	 * @param unknown $OH
	 * @param unknown $PH
	 * @param unknown $PW
	 */
	
	protected function drawNextSpreads($pageObjs,$data,$PlaceM,$x,$y,$startX,$startY,$OW,$OH,$PH,$PW)
	{
		$cols = $this->_maxCols?$this->_maxCols:1;
		$max = count($pageObjs);
		$res=array();
		for ( $i=0; $i < $max ; $i += $cols )
		{
			for ( $j= ($i+$cols-1); $j >= $i; $j-- )
			{
				if ( $j < $max)
				{
					$res[]=$pageObjs[$j];
				}else
					$res[]='d';
			}
		}
		foreach ( $res as $objData )
		{
			if ( is_array($objData))
			{
				$this->_ProductData=$objData;
				$spread = $data['spreads'][1];
				foreach ( $spread['pages'] as $page )
				{
					if ( $this->_bShowCropMarks )
						$this->showCropMarks($x,$y,$OW,$OH);
					$this->_tcpdf->StartTransform();
					$this->_tcpdf->Rect($x,$y, $OW, $OH, 'CNZ');
					$this->_OriginX=$x;
					$this->_OriginY=$y;
					$this->drawBoxes($spread['boxes']);
					$this->_tcpdf->StopTransform();
	
					break;
				}
			}
			$newPage = $this->calcNewPage($PlaceM,$x,$y,$startX,$startY,$OW,$OH,$PH,$PW);
		}
		$this->_ProductData=null;
	}
	
	protected function drawDocMode2($jobQueue,$data)
	{
		// Original Document auf gösseres Dokument
		$ds = explode('#',$jobQueue->getUserPageSize());
		$data['width']=$ds[1];
		$data['height']=$ds[2];
		$data['orientation']=$jobQueue->getUserPageOrientation();
			
		$this->_bShowCropMarks=true;
		$type = (int)@$data['object']['type'];
			
		$PlaceM = $jobQueue->getPlacmentMethod();
		$OW = $data['original_width'];
		$OH = $data['original_height'];
		$PW = $data['width'];
		$PH = $data['height'];
		if ( $jobQueue->getUserPageOrientation() == 'l' )
		{
			$PW = $data['height'];
			$PH = $data['width'];
		}
			
		$startX=$startY=0;
		if ( $this->_bShowCropMarks )
			$startX=$startY= $this->getCropSpace();
			
		$newPage=true;
		$x=0;$y=0;
		$pageObjs=array();
		$objIdx = 0;
			
			
			
		$x=0;$y=0;$startX=0;$startY=0;
		if ( $this->_bShowCropMarks ){
			$startX=$startY=$x=$y=$this->getCropSpace();
		}
		$this->_maxX=$this->_maxY=0;
		$this->_maxCols=0;
		$this->_cols=0;
		while (  1 )
		{
			$newPage = $this->calcNewPage($PlaceM,$x,$y,$startX,$startY,$OW,$OH,$PH,$PW);
			if ( $newPage )
				break;
		}
		if ( !$this->_maxX )
			$this->_maxX=$OW+$startX*2;
		if ( !$this->_maxY )
			$this->_maxY=$OH+$startY*2;
		$startX +=($PW - $this->_maxX)/2;
		$startY +=($PH - $this->_maxY)/2;
	
	
		$objectDatas = Mage::helper('auit_publicationbasic')->getObjectData($data['preview_sku'],$type,Mage::app()->getStore()->getId(),true,true);
		$max = count($objectDatas);
		//$max=20;
		
		while (  $objIdx < $max )
		{
		//	Mage::log("drawDocMode2: $objIdx");
			$objData = $objectDatas[$objIdx];
			$this->_ProductData=null;
			foreach ( $data['spreads'] as $spread )
			{
				if ( $newPage )
				{
					$this->startSpread($data,$spread);
					//					$this->addPageDocMode($data,false,count($spread['pages']));
						
					$this->_OriginX=$x=$startX ; $this->_OriginY=$y=$startY ;
					if ( 1 )
						if ( count($pageObjs) > 0 && count($data['spreads']) > 1 ){
						$this->drawNextSpreads($pageObjs,$data,$PlaceM,$x,$y,$startX,$startY,$OW,$OH,$PH,$PW);
						$pageObjs=array();
						$this->startSpread($data,$spread);
						//$this->addPageDocMode($data,false,count($spread['pages']));
						$this->_OriginX=$x=$startX; $this->_OriginY=$y=$startY;
					}
				}
				$this->_ProductData=$objData;
				foreach ( $spread['pages'] as $page )
				{
					if ( $this->_bShowCropMarks )
						$this->showCropMarks($x,$y,$OW,$OH);
	
					$this->_tcpdf->StartTransform();
					$this->_tcpdf->Rect($x,$y, $OW, $OH, 'CNZ');
					$this->_OriginX=$x;
					$this->_OriginY=$y;
					$this->drawBoxes($spread['boxes']);
					$this->_tcpdf->StopTransform();
					//$x+=$data['original_width'];
					break;
				}
				break;
			}
			$newPage = $this->calcNewPage($PlaceM,$x,$y,$startX,$startY,$OW,$OH,$PH,$PW);
			$pageObjs[]=$objData;
			$objIdx++;
		}
		if ( 1 )
			if ( count($pageObjs) > 0 && count($data['spreads']) > 1 ){
			//$this->addPageDocMode($data,false,count($spread['pages']));
			$this->startSpread($data,$spread);
			$this->_OriginX=$x=$startX; $this->_OriginY=$y=$startY;
			$this->drawNextSpreads($pageObjs,$data,$PlaceM,$x,$y,$startX,$startY,$OW,$OH,$PH,$PW);
			$pageObjs=array();
		}
		//Mage::log("drawDocMode2 end");
		
	}
	
	protected function showCropMarks($x,$y,$OW,$OH)
	{
		$space = $this->getCropSpace();
		$startX4=$space/4;
		$startY4=$space/4;
		$startOX=$space/10;
		$startOY=$space/10;
	
		// Crop Marken
		$cropStyle = array('width' => 0.001,'color' => array(0, 0, 0));
		$this->_tcpdf->Line($x - $startX4 , $y, $x - $startX4-$startX4+$startOX, $y,$cropStyle);
		$this->_tcpdf->Line($x,  $y-$startY4, $x, $y-$startY4-$startY4+$startOY,$cropStyle);
	
		$this->_tcpdf->Line($x + $OW + $startX4, $y, $x+$OW + $startX4+$startX4-$startOX, $y,$cropStyle);
		$this->_tcpdf->Line($x + $OW,  $y-$startY4,  $x+$OW, $y-$startY4-$startY4+$startOY,$cropStyle);
	
		$this->_tcpdf->Line($x + $OW + $startX4, $y+ $OH, $x+$OW + $startX4+$startX4-$startOX, $y+ $OH,$cropStyle);
		$this->_tcpdf->Line($x + $OW,  $y+$OH+$startY4,  $x+$OW, $y+$OH+$startY4+$startY4-$startOY,$cropStyle);
	
		$this->_tcpdf->Line($x - $startX4 , $y+ $OH, $x - $startX4-$startX4+$startOX, $y+ $OH,$cropStyle);
		$this->_tcpdf->Line($x,  $y+$OH+$startY4,  $x, $y+$OH+$startY4+$startY4-$startOY,$cropStyle);
	}
	protected function calcNewPage($PlaceM,&$x,&$y,$startX,$startY,$OW,$OH,$PH,$PW)
	{
		$space =0;
		if ( $this->_bShowCropMarks )
			$space = $this->getCropSpace();
	
		$newPage = false;
		switch ( $PlaceM )
		{
			case 'grid':
				$x += $space + $OW;
				$this->_cols++;
				if ( $this->_cols > $this->_maxCols)
					$this->_maxCols=$this->_cols;
				if ( $x > $this->_maxX)
					$this->_maxX=$x;
				if ( $x >= ($PW - ($space + $OW)) )
				{
					$this->_cols=0;
					$x=$startX;
					$y += $OH +$space;
					if ( $y > $this->_maxY)
						$this->_maxY=$y;
					if ( $y >= ($PH - ($space + $OH)) )
					{
						$newPage=true;
					}
				}
				break;
			default:
				$this->_cols=1;
				$this->_maxCols=1;
				$y += $OH +$space;
				if ( $y > $this->_maxY)
					$this->_maxY=$y;
				if ( $y >= ($PH - ($space + $OH)) )
					$newPage=true;
				break;
		}
		return $newPage;
	}
}
