<?php
require_once 'snm3/auit/pdf.php';
abstract class AuIt_PublicationBasic_Model_Pdf_Base extends Varien_Object //Mage_Sales_Model_Order_Pdf_Abstract
{

//	protected $_Product;
	protected $_ProductData;
	protected $_OriginX;
	protected $_OriginY;
	protected $_Style;
	protected $_backData;
	
	protected $_emulatedDesignConfig = false;

	protected $_invoice=null;
	protected $_processor;
	protected $_page=null;
	protected $_page_nr=0;
	protected $_page_max=0;

    protected $_tcpdf;
    protected $_margins;
    protected $_bsimulate;
    protected $_paymentInfo='';
    protected $_cfgPfad;
    protected $_streams;

    protected $_pdf;
    protected $_cols;
    protected $_maxCols;
    protected $_maxX;
    protected $_maxY;
    protected $_bShowCropMarks;
    
    protected function _construct()
    {

    }
    protected function _getProcessor()
    {
    	if ( !$this->_processor )
    	{
    		$processor = Mage::getModel('auit_publicationbasic/email_template_filter');
    
    		$data = array(
    				'helper'=> Mage::getModel('auit_publicationbasic/email_template_helper')->setProcessor($processor),
    				'page_current'=>$this->_tcpdf->getRSCPage().$this->_tcpdf->getAliasNumPage(),
    				'page_count'=>$this->_tcpdf->getAliasNbPages(),
    		);
    
    		$processor->setVariables($data);
    		$this->_processor=$processor;
    	}
    	return $this->_processor;
    }
    
    protected function _beforeGetPdf() {
    	$translate = Mage::getSingleton('core/translate');
    	/* @var $translate Mage_Core_Model_Translate */
    	$translate->setTranslateInline(false);
    }
    protected function _afterGetPdf() {
    	$translate = Mage::getSingleton('core/translate');
    	/* @var $translate Mage_Core_Model_Translate */
    	$translate->setTranslateInline(true);
    }
    public function PDFshowHeader(AuIt_Pdf2 $pdf)
    {
    	//bMargin
    	/*
    	$this->_page_nr++;
    	if ( !$this->_bsimulate )
    		$pdf->showTemplatePage($this->_page_nr<= 1?1:2);
    	$pdf->setAutoPB(false);
    	if ( !$this->_bsimulate )
    		$this->insertFreeItems($pdf);
    	$pdf->setAutoPB(true);
    	$this->setPageMargins($pdf,$this->_page_nr);
    	*/
    }
    protected function getCropSpace()
    {
    	return 10;
    }
    protected function getBleedSpace()
    {
    	return 2;
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
    
    protected function addPageDocMode($data,$baddBleed,$spreadPages)
    {
    	$orientation= (isset($data['orientation']) && $data['orientation'] == 'p')?'P':'L';
    	$spreadW = $data['width'] * $spreadPages;
    	$spreadH = $data['height'];
    	$format = array(
   			'MediaBox'=>array('llx'=>0,'lly'=>0,'urx'=>$spreadW,'ury'=>$spreadH)
    	);
    	if ($baddBleed)
    	{
    		$bo = $this->getBleedSpace();
    		$format['BleedBox']=array('llx'=>$bo,'lly'=>$bo,
    				'urx'=>$data['width']-$bo,
    				'ury'=>$data['height']-$bo);
    		$format['TrimBox']=array('llx'=>$bo,'lly'=>$bo,
    				'urx'=>$data['width']-$bo,
    				'ury'=>$data['height']-$bo);
    	}
    	$this->_tcpdf->AddPage($orientation,$format);
    }
    


    public function getPdfFromData($printMode,$jsonData,$storeId=0)
    {
    	try {
    		$this->_printMode=$printMode;
    		$this->_beforeGetPdf();
			if ( is_array($jsonData))
				$data = $jsonData;
			else
    			$data = Mage::helper('core')->jsonDecode($jsonData);
    		$sender = Mage::getModel('core/email_template');
    		if ( !$storeId )
    			$storeId = $data['preview_store'];
    		if ( !$storeId )
    			$storeId ='default';
    		try {
    			$sender->emulateDesign($storeId);
    		}
    		catch ( Exception $e )
    		{
    		}
    		
    		

    		$this->_pdf = new Zend_Pdf();
    		$this->_streams = array();

    		$this->beginDrawData($data);
    		foreach ( $this->_streams as $stream )
    		{
    			$pdf = Zend_Pdf::parse($stream);
    			foreach ($pdf->pages as $page)
    				$this->_pdf->pages[] = clone($page);
    		}
    		$this->_afterGetPdf();
    		$this->_streams=array();
    	}
    	catch ( Exception $e )
    	{
    		$this->_afterGetPdf();
    		throw $e;
    	}
    	return $this->_pdf;
    }
    public function newSpread($data,$spread=null)
    {
    	$orientation= (isset($data['orientation']) && $data['orientation'] == 'p')?'P':'L';
    	$spreadW = $data['width'] * ($spread?count($spread['pages']):1);
    	$spreadH = $data['height'];
    	$format = array(
    			'MediaBox'=>array(
    					'llx'=>0,
    					'lly'=>0,
    					'urx'=>$spreadW,
    					'ury'=>$spreadH
    					)
    			);
    	$this->_tcpdf->AddPage($orientation,$format);
    }
    
    protected function getAttribute($code)
    {
    	return  isset($this->_ProductData[$code])?$this->_ProductData[$code]:'not found:'.$code;
    }
    
    protected function xPOS($blockInfo)
    {
    	return $this->_OriginX + $blockInfo->getX();
    }
    protected function yPOS($blockInfo)
    {
    	return $this->_OriginY + $blockInfo->getY();
    }
    protected function drawBoxes($boxes)//,$rangeXFrom,$rangeXTo)
    {
    	foreach ( $boxes as $box )
    	{
    		$this->drawBox($box);
    	}
    }




}