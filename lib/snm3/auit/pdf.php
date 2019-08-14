<?php

if (!defined('K_PATH_FONTS') ) {
	define ('K_PATH_MAIN',  Mage::getBaseDir('lib').'/snm3/tcpdf/' );
}
if (!defined('K_PATH_FONTS') ) {
	// Wenn kompiler aktiv ist !!
	define('K_PATH_FONTS', Mage::getBaseDir('lib').'/snm3/tcpdf/fonts/');
}

require_once(dirname(__FILE__).'/../tcpdf/config/lang/ger.php');
require_once(dirname(__FILE__).'/../tcpdf/tcpdf.php');
//require_once(dirname(__FILE__).'/../fpdi/fpdi.php');

//class AuIt_Pdf2  extends FPDI {
class AuIt_Pdf2  extends TCPDF {
	static protected function pt2mm($v)
    {
		return $v * 0.3528;
	}
    static protected function mm2pt($v)
    {
		$v=(double)$v;
		$pt = $v / 0.3528;
		return $pt;
    }
    protected $_pdfTemplate;
    protected $_tplIdx;
    protected $_globalCSS='';
	protected $_styleInfos=array();
	protected $_caller;
	protected $_boxOverflow;
	protected $_scaleFontFaktor=0;
	protected $_bottomY=0;
	//MAU
	public function getRSCPage() {
		return $this->alias_right_shift;
	}
	public function Error($msg) {
		// unset all class variables
		$this->_destroy(true);
		throw new Exception(Mage::helper('core')->__('PDF creation error %s',$msg));
	}
	public function __construct($caller)
	{
		global $l;
		$this->_caller = $caller;
		$this->current_filename='';
		$this->setLanguageArray($l);
		$this->setPrintFooter(false);
		$this->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
		parent::__construct(PDF_PAGE_ORIENTATION, PDF_UNIT);

		$this->SetMargins(0, 0);
		$this->setCellPaddings(0, 0, 0, 0);
		$this->setCellMargins(0, 0, 0, 0);
		
		$tagvs = array(
				'h1' => array(0 => array('h' => 0, 'n' => 0), 1 => array('h' => 0, 'n' => 0)),
				'h2' => array(0 => array('h' => 0, 'n' => 0), 1 => array('h' => 0, 'n' => 0)),
				'h3' => array(0 => array('h' => 0, 'n' => 0), 1 => array('h' => 0, 'n' => 0)),
				'h4' => array(0 => array('h' => 0, 'n' => 0), 1 => array('h' => 0, 'n' => 0)),
				'h5' => array(0 => array('h' => 0, 'n' => 0), 1 => array('h' => 0, 'n' => 0)),
				'h6' => array(0 => array('h' => 0, 'n' => 0), 1 => array('h' => 0, 'n' => 0)),
				'p' => array(0 => array('h' => 0, 'n' => 0), 1 => array('h' => 0, 'n' => 0)),
				//	'ul' => array(0 => array('h' => 0, 'n' => 0), 1 => array('h' => 2, 'n' => 2)),
				//	'li' => array(0 => array('h' => 0, 'n' => 0), 1 => array('h' => 0, 'n' => 0)),
				'div' => array(0 => array('h' => 0, 'n' => 0), 1 => array('h' => 0, 'n' => 0)),
		);
		$this->setHtmlVSpace($tagvs);
		$this->setListIndentWidth(6);
		
	}
	
	public function Close() {
		if ($this->state == 3) {
			return;
		}
		if ($this->page == 0) {
			$this->AddPage();
		}
		$this->endLayer();
		if ($this->tcpdflink) {
			// save current graphic settings
			$gvars = $this->getGraphicVars();
			$this->setEqualColumns();
			$this->lastpage(true);
			$this->SetAutoPageBreak(false);
			$this->x = 0;
			$this->y = $this->h - (1 / $this->k);
			$this->lMargin = 0;
			// restore graphic settings
			$this->setGraphicVars($gvars);
		}
		// close page
		$this->endPage();
		// close document
		$this->_enddoc();
		// unset all class variables (except critical ones)
		$this->_destroy(false);
	}
	public function AddPage($orientation='', $format='', $keepmargins=false, $tocpage=false) {
		if ( $this->current_filename )
		{
			$tplidx = $this->importPage(1);
			$size = $this->getTemplateSize($tplidx, 0, 0);
            $format = array($size['w'], $size['h']);
            $orientation = $format[0] > $format[1] ? 'L' : 'P';
		}
		parent::AddPage($orientation, $format, $keepmargins, $tocpage);
	}
	public function setAutoPB($b) {
		$this->AutoPageBreak=$b;
	}
	public function setTemplatePDF($template) {
		if ( $template )
		{
			$this->setSourceFile($template);
		}
	}
	public function showTemplatePage($page) {
		if ( $this->current_filename )
		{
			$tplIdx = $this->importPage($page);
	        if ( $tplIdx )
	        	$this->useTemplate($tplIdx);
		}
	}
	public function Header() {
		if ( $this->_caller && method_exists($this->_caller,'PDFshowHeader'))
			$this->_caller->PDFshowHeader($this);
	}
	public function setGlobalCSS($cssdata)
	{
		$css = array();
		$css = array_merge($css, TCPDF_STATIC::extractCSSproperties($cssdata));
		$csstagarray = '<cssarray>'.htmlentities(serialize($css)).'</cssarray>';
		$this->_globalCSS=$csstagarray;
	}
	public function getGlobalCSS()
	{
		return $this->_globalCSS;
	}
	public function writeHTML($html, $ln=true, $fill=false, $reseth=false, $cell=false, $align='') {
		return parent::writeHTML($html, $ln, $fill, $reseth, $cell, $align);
	}
	public function setPageOrientation($orientation, $autopagebreak='', $bottommargin='') {
		if ( $this->_caller && method_exists($this->_caller,'getMargin'))
			$this->bMargin = $this->_caller->getMargin($this->page,'bottom');
		$autopagebreak = false;
		return parent::setPageOrientation($orientation, $autopagebreak, $bottommargin);
	}
	public function SetFontSize($size, $out=true) {
		$f = $size/100 * $this->_scaleFontFaktor;
		$size -=  $f; 
		parent::SetFontSize($size, $out);
	}
	public function SetAutoPageBreak($auto, $margin=0) {
		if ( $this->_caller && method_exists($this->_caller,'getMargin'))
		{
			$margin = $this->_caller->getMargin($this->page,'bottom');
		}
		$margin = 0;
		$auto = false;
		return parent::SetAutoPageBreak($auto, $margin);
	}
	public function Write($h, $txt, $link='', $fill=false, $align='', $ln=false, $stretch=0, $firstline=false, $firstblock=false, $maxh=0, $wadj=0, $margin='')
	{
		if ( $this->maxBoxHeight && ($this->y+$h) >= ($this->maxBoxHeight+$this->FontDescent))
		{
			$this->_boxOverflow=true;
			return '';
		}
		if ( ($this->y+$h+$this->FontDescent) > $this->_bottomY)
			$this->_bottomY = $this->y+$h+$this->FontDescent;
		
		return parent::Write($h, $txt, $link, $fill, $align, $ln, $stretch, $firstline, $firstblock, $maxh, $wadj, $margin);
	}
	public function cleanHTML($txt)
	{
		$txt = str_replace('<br>', "<br/>", $txt);
		$txt = preg_replace('/>([\s]+)</', '><', $txt); // replace multiple spaces
		$txt = str_replace(
				array('<h1','<h2','<h3','<h4','<h5','<h6',
						'</h1','</h2','</h3','</h4','</h5','</h6'),
				array('<div','<div','<div','<div','<div','<div',
						'</div','</div','</div','</div','</div','</div'),$txt);
		return $txt;
		
	}
	public function MultiCellStart($w, $h, $txt, $border=0, $align='J', $fill=false, $ln=1, $x='', $y='', $reseth=true, $stretch=0, $ishtml=false, $autopadding=true, $maxh=0, 
				$valign='T', $fitcell=false,
				$boxtextOption='') {
		
		$txt=$this->_globalCSS.$txt;
		
		$border=0;
		$oldFontFaktor = $this->_scaleFontFaktor;
		$this->_boxOverflow=false;
		$oldBreak = $this->getAutoPageBreak();
		$this->maxBoxHeight = $y + $h;
		$offsetY=0;
		$this->SetAutoPageBreak(false);
		if ( $valign == 'B' || $valign == 'M' )
		{
			$start_y = $y;
			$this->startTransaction();

			$this->_boxOverflow=false;
			$this->_bottomY=0;
			@parent::MultiCell($w, 0, $txt, $border, $align, $fill, 1, $x, $y, $reseth, $stretch, $ishtml, $autopadding, $maxh, $valign, $fitcell);
			if ( !$this->_boxOverflow ) {
				$bloxkH = $this->GetY() - $start_y;
				if ( $bloxkH > 0 && $bloxkH < $h )
				{
					if ( $valign == 'B'  ){
						$offsetY = $h - $bloxkH;
					}
					else if ( $valign == 'M' ) {
						$offsetY = ($h - $bloxkH) / 2;
					}
				}
				if ( $offsetY )
				{
					$y += $offsetY;
				}
			}			
			$this->rollbackTransaction(true);
		}
		// Output html.
		$this->_boxOverflow=false;
		$this->_scaleFontFaktor=0;
		$start_y=$y;
		do {
			$y=$start_y;
			$this->startTransaction();
			$nbreak=true;
			$this->_boxOverflow=false;
		//	Mage::log("call Multi : $y : sf: ".$this->_scaleFontFaktor);
			$result = @parent::MultiCell($w, $h, $txt, $border, $align, $fill, $ln, $x, $y, $reseth, $stretch, $ishtml, $autopadding, $maxh, $valign, $fitcell);
			if ( $this->_boxOverflow ) {
				if ( $boxtextOption == 'fittextbox')
					$nbreak=false;
			}
			if ( $nbreak )
			{
				$this->commitTransaction();
			}
			else {
				$this->rollbackTransaction(true);
				if ( $this->_scaleFontFaktor >= 100)
					$nbreak=true;
				else
					$this->_scaleFontFaktor += 5;
			}
			
		}while (!$nbreak);
		$this->SetAutoPageBreak($oldBreak);
		$this->_scaleFontFaktor=$oldFontFaktor;
		return $result;
	}
	public function Image($file, $x='', $y='', $w=0, $h=0, $type='', $link='', $align='', $resize=false, $dpi=300, $palign='', $ismask=false, $imgmask=false, $border=0, $fitbox=false, $hidden=false, $fitonpage=false, $alt=false, $altimgs=array()) {
		return parent::Image($file, $x, $y, $w, $h, $type, $link, $align, $resize, $dpi, $palign, $ismask, $imgmask, $border, $fitbox, $hidden, $fitonpage, $alt, $altimgs);
	}
	
	protected function getFontsList() {
		$fontsdir = opendir(TCPDF_FONTS::_getfontpath());
		while (($file = readdir($fontsdir)) !== false) {
			if (substr($file, -4) == '.php') {
				array_push($this->fontlist, strtolower(basename($file, '.php')));
			}
		}
		closedir($fontsdir);
	}
	public function addTTFfont($fontfile, $fonttype='', $enc='', $flags=32, $outpath='', $platid=3, $encid=1, $addcbbox=false) {
		if (!file_exists($fontfile)) {
			$this->Error('Could not find file: '.$fontfile.'');
		}
		// build new font name for TCPDF compatibility
		$font_path_parts = pathinfo($fontfile);
		if (!isset($font_path_parts['filename'])) {
			$font_path_parts['filename'] = substr($font_path_parts['basename'], 0, -(strlen($font_path_parts['extension']) + 1));
		}
		$font_name = strtolower($font_path_parts['filename']);
		$font_name = preg_replace('/[^a-z0-9_]/', '', $font_name);
		$search  = array('bold', 'oblique', 'italic', 'regular');
		$replace = array('b', 'i', 'i', '');
		$font_name = str_replace($search, $replace, $font_name);
		if (empty($font_name)) {
			// set generic name
			$font_name = 'tcpdffont';
		}
		// set output path
		if (empty($outpath)) {
			$outpath = TCPDF_FONTS::_getfontpath();
		}
		// check if this font already exist
		if (file_exists($outpath.$font_name.'.php')) {
			// this font already exist (delete it from fonts folder to rebuild it)
			return $font_name;
		}
		
		$font_name = parent::addTTFfont($fontfile, $fonttype, $enc, $flags, $outpath, $platid, $encid, $addcbbox);
		$this->fontlist=array();
		$this->getFontsList();
		
		return $font_name; 
	}
	
	public function getimagesizeSVG($file,$w=0,$h=0) {
		if ($file[0] === '@') { // image from string
			$this->svgdir = '';
			$svgdata = substr($file, 1);
		} else { // SVG file
			$this->svgdir = dirname($file);
			$svgdata = TCPDF_STATIC::fileGetContents($file);
		}
		if ($svgdata === FALSE) {
			return false;
		}

		$ox = 0;
		$oy = 0;
		$aspect_ratio_align = 'xMidYMid';
		$aspect_ratio_ms = 'meet';
		$regs = array();
		// get original image width and height
		preg_match('/<svg([^\>]*)>/si', $svgdata, $regs);
		if (isset($regs[1]) AND !empty($regs[1])) {
			$tmp = array();
			if (preg_match('/[\s]+x[\s]*=[\s]*"([^"]*)"/si', $regs[1], $tmp)) {
				$ox = $this->getHTMLUnitToUnits($tmp[1], 0, $this->svgunit, false);
			}
			$tmp = array();
			if (preg_match('/[\s]+y[\s]*=[\s]*"([^"]*)"/si', $regs[1], $tmp)) {
				$oy = $this->getHTMLUnitToUnits($tmp[1], 0, $this->svgunit, false);
			}
			$tmp = array();
			if (preg_match('/[\s]+width[\s]*=[\s]*"([^"]*)"/si', $regs[1], $tmp)) {
				$ow = $this->getHTMLUnitToUnits($tmp[1], 1, $this->svgunit, false);
			}
			$tmp = array();
			if (preg_match('/[\s]+height[\s]*=[\s]*"([^"]*)"/si', $regs[1], $tmp)) {
				$oh = $this->getHTMLUnitToUnits($tmp[1], 1, $this->svgunit, false);
			}
			$tmp = array();
			$view_box = array();
			if (preg_match('/[\s]+viewBox[\s]*=[\s]*"[\s]*([0-9\.\-]+)[\s]+([0-9\.\-]+)[\s]+([0-9\.]+)[\s]+([0-9\.]+)[\s]*"/si', $regs[1], $tmp)) {
				if (count($tmp) == 5) {
					array_shift($tmp);
					foreach ($tmp as $key => $val) {
						$view_box[$key] = $this->getHTMLUnitToUnits($val, 0, $this->svgunit, false);
					}
					$ox = $view_box[0];
					$oy = $view_box[1];
				}
				// get aspect ratio
				$tmp = array();
				if (preg_match('/[\s]+preserveAspectRatio[\s]*=[\s]*"([^"]*)"/si', $regs[1], $tmp)) {
					$aspect_ratio = preg_split('/[\s]+/si', $tmp[1]);
					switch (count($aspect_ratio)) {
						case 3: {
							$aspect_ratio_align = $aspect_ratio[1];
							$aspect_ratio_ms = $aspect_ratio[2];
							break;
						}
						case 2: {
							$aspect_ratio_align = $aspect_ratio[0];
							$aspect_ratio_ms = $aspect_ratio[1];
							break;
						}
						case 1: {
							$aspect_ratio_align = $aspect_ratio[0];
							$aspect_ratio_ms = 'meet';
							break;
						}
					}
				}
			}
		}
		if ($ow <= 0) {
			$ow = 1;
		}
		if ($oh <= 0) {
			$oh = 1;
		}
		// calculate image width and height on document
		if (($w <= 0) AND ($h <= 0)) {
			// convert image size to document unit
			$w = $ow;
			$h = $oh;
		} elseif ($w <= 0) {
			$w = $h * $ow / $oh;
		} elseif ($h <= 0) {
			$h = $w * $oh / $ow;
		}
		return array($w, $h);
		// fit the image on available space
//		list($w, $h, $x, $y) = $this->fitBlock($w, $h, $x, $y, $fitonpage);
	}
	public function ImageSVG($file, $x='', $y='', $w=0, $h=0, $link='', $align='', $palign='', $border=0, $fitonpage=false,$mainscale=-1) {
		
		return @parent::ImageSVG($file, $x, $y, $w, $h, $link, $align, $palign, $border, $fitonpage,$mainscale);
	}
	public function SVGPath2($sf,$x,$y,$d, $style='') {
		if ($this->state != 2) {
			return;
		}
		// set fill/stroke style
		$op = TCPDF_STATIC::getPathPaintOperator($style, '');
		if (empty($op)) {
			return;
		}
		//Mage::log($this->k);
		$paths = array();
		$d = preg_replace('/([0-9ACHLMQSTVZ])([\-\+])/si', '\\1 \\2', $d);
		preg_match_all('/([ACHLMQSTVZ])[\s]*([^ACHLMQSTVZ\"]*)/si', $d, $paths, PREG_SET_ORDER);
	//	$x = 0;
	//	$y = 0;
		
		$x1 = 0;
		$y1 = 0;
		$x2 = 0;
		$y2 = 0;
		$xmin = 2147483647;
		$xmax = 0;
		$ymin = 2147483647;
		$ymax = 0;
		$relcoord = false;
		$minlen = (0.01 / $this->k); // minimum acceptable length (3 point)
		$firstcmd = true; // used to print first point
//	$kx = $this->k;
		
		// draw curve pieces
		foreach ($paths as $key => $val) {
			// get curve type
			$cmd = trim($val[1]);
			if (strtolower($cmd) == $cmd) {
				// use relative coordinated instead of absolute
				$relcoord = true;
				$xoffset = $x;
				$yoffset = $y;
			} else {
				$relcoord = false;
				$xoffset = 0;
				$yoffset = 0;
			}
			$params = array();
			if (isset($val[2])) {
				// get curve parameters
				$rawparams = preg_split('/([\,\s]+)/si', trim($val[2]));
				$params = array();
				foreach ($rawparams as $ck => $cp) {
					$params[$ck] = $this->getHTMLUnitToUnits($cp, 0, $this->svgunit, false) * $sf;
					if (abs($params[$ck]) < $minlen) {
						// aproximate little values to zero
						$params[$ck] = 0;
					}
				}
			}
			// store current origin point
			$x0 = $x;
			$y0 = $y;
			switch (strtoupper($cmd)) {
				case 'M': { // moveto
					foreach ($params as $ck => $cp) {
						if (($ck % 2) == 0) {
							$x = $cp + $xoffset;
						} else {
							$y = $cp + $yoffset;
							if ($firstcmd OR (abs($x0 - $x) >= $minlen) OR (abs($y0 - $y) >= $minlen)) {
								if ($ck == 1) {
									$this->_outPoint($x, $y);
									$firstcmd = false;
								} else {
									$this->_outLine($x, $y);
								}
								$x0 = $x;
								$y0 = $y;
							}
							$xmin = min($xmin, $x);
							$ymin = min($ymin, $y);
							$xmax = max($xmax, $x);
							$ymax = max($ymax, $y);
							if ($relcoord) {
								$xoffset = $x;
								$yoffset = $y;
							}
						}
					}
					break;
				}
				case 'L': { // lineto
					foreach ($params as $ck => $cp) {
						if (($ck % 2) == 0) {
							$x = $cp + $xoffset;
						} else {
							$y = $cp + $yoffset;
							if ((abs($x0 - $x) >= $minlen) OR (abs($y0 - $y) >= $minlen)) {
								$this->_outLine($x, $y);
								$x0 = $x;
								$y0 = $y;
							}
							$xmin = min($xmin, $x);
							$ymin = min($ymin, $y);
							$xmax = max($xmax, $x);
							$ymax = max($ymax, $y);
							if ($relcoord) {
								$xoffset = $x;
								$yoffset = $y;
							}
						}
					}
					break;
				}
				case 'H': { // horizontal lineto
					foreach ($params as $ck => $cp) {
						$x = $cp + $xoffset;
						if ((abs($x0 - $x) >= $minlen) OR (abs($y0 - $y) >= $minlen)) {
							$this->_outLine($x, $y);
							$x0 = $x;
							$y0 = $y;
						}
						$xmin = min($xmin, $x);
						$xmax = max($xmax, $x);
						if ($relcoord) {
							$xoffset = $x;
						}
					}
					break;
				}
				case 'V': { // vertical lineto
					foreach ($params as $ck => $cp) {
						$y = $cp + $yoffset;
						if ((abs($x0 - $x) >= $minlen) OR (abs($y0 - $y) >= $minlen)) {
							$this->_outLine($x, $y);
							$x0 = $x;
							$y0 = $y;
						}
						$ymin = min($ymin, $y);
						$ymax = max($ymax, $y);
						if ($relcoord) {
							$yoffset = $y;
						}
					}
					break;
				}
				case 'C': { // curveto
					foreach ($params as $ck => $cp) {
						$params[$ck] = $cp;
						if ((($ck + 1) % 6) == 0) {
							$x1 = $params[($ck - 5)] + $xoffset;
							$y1 = $params[($ck - 4)] + $yoffset;
							$x2 = $params[($ck - 3)] + $xoffset;
							$y2 = $params[($ck - 2)] + $yoffset;
							$x = $params[($ck - 1)] + $xoffset;
							$y = $params[($ck)] + $yoffset;
							$this->_outCurve($x1, $y1, $x2, $y2, $x, $y);
							$xmin = min($xmin, $x, $x1, $x2);
							$ymin = min($ymin, $y, $y1, $y2);
							$xmax = max($xmax, $x, $x1, $x2);
							$ymax = max($ymax, $y, $y1, $y2);
							if ($relcoord) {
								$xoffset = $x;
								$yoffset = $y;
							}
						}
					}
					break;
				}
				case 'S': { // shorthand/smooth curveto
					foreach ($params as $ck => $cp) {
						$params[$ck] = $cp;
						if ((($ck + 1) % 4) == 0) {
							if (($key > 0) AND ((strtoupper($paths[($key - 1)][1]) == 'C') OR (strtoupper($paths[($key - 1)][1]) == 'S'))) {
								$x1 = (2 * $x) - $x2;
								$y1 = (2 * $y) - $y2;
							} else {
								$x1 = $x;
								$y1 = $y;
							}
							$x2 = $params[($ck - 3)] + $xoffset;
							$y2 = $params[($ck - 2)] + $yoffset;
							$x = $params[($ck - 1)] + $xoffset;
							$y = $params[($ck)] + $yoffset;
							$this->_outCurve($x1, $y1, $x2, $y2, $x, $y);
							$xmin = min($xmin, $x, $x1, $x2);
							$ymin = min($ymin, $y, $y1, $y2);
							$xmax = max($xmax, $x, $x1, $x2);
							$ymax = max($ymax, $y, $y1, $y2);
							if ($relcoord) {
								$xoffset = $x;
								$yoffset = $y;
							}
						}
					}
					break;
				}
				case 'Q': { // quadratic B�zier curveto
					foreach ($params as $ck => $cp) {
						$params[$ck] = $cp;
						if ((($ck + 1) % 4) == 0) {
							// convert quadratic points to cubic points
							$x1 = $params[($ck - 3)] + $xoffset;
							$y1 = $params[($ck - 2)] + $yoffset;
							$xa = ($x + (2 * $x1)) / 3;
							$ya = ($y + (2 * $y1)) / 3;
							$x = $params[($ck - 1)] + $xoffset;
							$y = $params[($ck)] + $yoffset;
							$xb = ($x + (2 * $x1)) / 3;
							$yb = ($y + (2 * $y1)) / 3;
							$this->_outCurve($xa, $ya, $xb, $yb, $x, $y);
							$xmin = min($xmin, $x, $xa, $xb);
							$ymin = min($ymin, $y, $ya, $yb);
							$xmax = max($xmax, $x, $xa, $xb);
							$ymax = max($ymax, $y, $ya, $yb);
							if ($relcoord) {
								$xoffset = $x;
								$yoffset = $y;
							}
						}
					}
					break;
				}
				case 'T': { // shorthand/smooth quadratic B�zier curveto
					foreach ($params as $ck => $cp) {
						$params[$ck] = $cp;
						if (($ck % 2) != 0) {
							if (($key > 0) AND ((strtoupper($paths[($key - 1)][1]) == 'Q') OR (strtoupper($paths[($key - 1)][1]) == 'T'))) {
								$x1 = (2 * $x) - $x1;
								$y1 = (2 * $y) - $y1;
							} else {
								$x1 = $x;
								$y1 = $y;
							}
							// convert quadratic points to cubic points
							$xa = ($x + (2 * $x1)) / 3;
							$ya = ($y + (2 * $y1)) / 3;
							$x = $params[($ck - 1)] + $xoffset;
							$y = $params[($ck)] + $yoffset;
							$xb = ($x + (2 * $x1)) / 3;
							$yb = ($y + (2 * $y1)) / 3;
							$this->_outCurve($xa, $ya, $xb, $yb, $x, $y);
							$xmin = min($xmin, $x, $xa, $xb);
							$ymin = min($ymin, $y, $ya, $yb);
							$xmax = max($xmax, $x, $xa, $xb);
							$ymax = max($ymax, $y, $ya, $yb);
							if ($relcoord) {
								$xoffset = $x;
								$yoffset = $y;
							}
						}
					}
					break;
				}
				case 'A': { // elliptical arc
					foreach ($params as $ck => $cp) {
						$params[$ck] = $cp;
						if ((($ck + 1) % 7) == 0) {
							$x0 = $x;
							$y0 = $y;
							$rx = abs($params[($ck - 6)]);
							$ry = abs($params[($ck - 5)]);
							$ang = -$rawparams[($ck - 4)];
							$angle = deg2rad($ang);
							$fa = $rawparams[($ck - 3)]; // large-arc-flag
							$fs = $rawparams[($ck - 2)]; // sweep-flag
							$x = $params[($ck - 1)] + $xoffset;
							$y = $params[$ck] + $yoffset;
							if ((abs($x0 - $x) < $minlen) AND (abs($y0 - $y) < $minlen)) {
								// endpoints are almost identical
								$xmin = min($xmin, $x);
								$ymin = min($ymin, $y);
								$xmax = max($xmax, $x);
								$ymax = max($ymax, $y);
							} else {
								$cos_ang = cos($angle);
								$sin_ang = sin($angle);
								$a = (($x0 - $x) / 2);
								$b = (($y0 - $y) / 2);
								$xa = ($a * $cos_ang) - ($b * $sin_ang);
								$ya = ($a * $sin_ang) + ($b * $cos_ang);
								$rx2 = $rx * $rx;
								$ry2 = $ry * $ry;
								$xa2 = $xa * $xa;
								$ya2 = $ya * $ya;
								$delta = ($xa2 / $rx2) + ($ya2 / $ry2);
								if ($delta > 1) {
									$rx *= sqrt($delta);
									$ry *= sqrt($delta);
									$rx2 = $rx * $rx;
									$ry2 = $ry * $ry;
								}
								$numerator = (($rx2 * $ry2) - ($rx2 * $ya2) - ($ry2 * $xa2));
								if ($numerator < 0) {
									$root = 0;
								} else {
									$root = sqrt($numerator / (($rx2 * $ya2) + ($ry2 * $xa2)));
								}
								if ($fa == $fs){
									$root *= -1;
								}
								$cax = $root * (($rx * $ya) / $ry);
								$cay = -$root * (($ry * $xa) / $rx);
								// coordinates of ellipse center
								$cx = ($cax * $cos_ang) - ($cay * $sin_ang) + (($x0 + $x) / 2);
								$cy = ($cax * $sin_ang) + ($cay * $cos_ang) + (($y0 + $y) / 2);
								// get angles
								$angs = $this->getVectorsAngle(1, 0, (($xa - $cax) / $rx), (($cay - $ya) / $ry));
								$dang = $this->getVectorsAngle((($xa - $cax) / $rx), (($ya - $cay) / $ry), ((-$xa - $cax) / $rx), ((-$ya - $cay) / $ry));
								if (($fs == 0) AND ($dang > 0)) {
									$dang -= (2 * M_PI);
								} elseif (($fs == 1) AND ($dang < 0)) {
									$dang += (2 * M_PI);
								}
								$angf = $angs - $dang;
								if ((($fs == 0) AND ($angs > $angf)) OR (($fs == 1) AND ($angs < $angf))) {
									// reverse angles
									$tmp = $angs;
									$angs = $angf;
									$angf = $tmp;
								}
								$angs = round(rad2deg($angs), 6);
								$angf = round(rad2deg($angf), 6);
								// covent angles to positive values
								if (($angs < 0) AND ($angf < 0)) {
									$angs += 360;
									$angf += 360;
								}
								$pie = false;
								if (($key == 0) AND (isset($paths[($key + 1)][1])) AND (trim($paths[($key + 1)][1]) == 'z')) {
									$pie = true;
								}
								list($axmin, $aymin, $axmax, $aymax) = $this->_outellipticalarc($cx, $cy, $rx, $ry, $ang, $angs, $angf, $pie, 2, false, ($fs == 0), true);
								$xmin = min($xmin, $x, $axmin);
								$ymin = min($ymin, $y, $aymin);
								$xmax = max($xmax, $x, $axmax);
								$ymax = max($ymax, $y, $aymax);
							}
							if ($relcoord) {
								$xoffset = $x;
								$yoffset = $y;
							}
						}
					}
					break;
				}
				case 'Z': {
					$this->_out('h');
					break;
				}
				default:
					Mage::log("svg2 path unknown:".$cmd);
					break;
			}
			$firstcmd = false;
		} // end foreach
		if (!empty($op)) {
			$this->_out($op);
		}
//		$this->k = $kx;
		return array($xmin, $ymin, ($xmax - $xmin), ($ymax - $ymin));
	}
	
}