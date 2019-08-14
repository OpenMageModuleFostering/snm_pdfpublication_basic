<?php

class AuIt_PublicationBasic_Model_Styles extends Mage_Core_Model_Abstract
{
    const CLPREFIX  = 'bl-';
	const CACHE_TAG     = 'auit_publicationbasic_styles';
    protected $_cacheTag= 'auit_publicationbasic_styles';
    protected $_jsonData=null;
    protected $_styles=null;
    protected $_fontfaces=array();
    protected $_fontfiles=array();
    protected function _construct()
    {
        $this->_init('auit_publicationbasic/styles');
    }
    public function getFontFiles()
    {
    	return $this->_fontfiles;
    }
    public function initialBasisData()
    {
    	if ( $this->getId() ) return;
    	$file   = Mage::getModuleDir('data', 'AuIt_Publication') . DS . 'initial'.DS.'style'.DS.'type_'.$this->getType().'.json';
    	if ( file_exists($file) )
    	{
    		$this->setData('data',file_get_contents($file));
    	}
    }
    public function buildCssObject($style,$stack=array())
    {
    	if ( isset($stack[$style['uid']]) )
    		return array();
    	$stack[$style['uid']]=1;
    	$obj=array();
    	if ( isset($style['based_on'])  && $style['based_on'] )
    	{
    		foreach ( $this->_styles as $substyle )
    		{
    			if ( $substyle['uid'] == $style['based_on'] )
    			{
    				$obj = $this->buildCssObject($substyle,$stack);
    				break;
    			}
    		}
    	}
    	if ( isset($obj['font_style']) && $obj['font_style']==':')
    		unset($obj['font_style']);
    	if ( isset($obj['font_family']) && $obj['font_family']==':')
    		unset($obj['font_family']);
    	
    	if ( isset($style['font_style']) && $style['font_style']==':')
    		unset($style['font_style']);
    	if ( isset($style['font_family']) && $style['font_family']==':')
    		unset($style['font_family']);
    	 
    	if ( isset($obj['alias']))
    		unset($obj['alias']);
    	foreach ( $style as $k => $v)
    	{
    		if ( $v )
    			$obj[$k]=$v;
    	}
    	if ( !isset($obj['path']))
    		$obj['path']=$style['name'];
    	else
    		$obj['path'] = $style['name'] . ' based on: '.$obj['path'];
   		return $obj;
    }
    protected function _calcSize($size)
    {
    	switch  ( $this->getMode()  )
    	{
    		case 'epub3':
    			$epub = $this->getRenderer();
    			$size = $epub->toPx($size,'px','pt');
    			break;
    		default:
    			break;
    	}
    	return $size; 
    }
    protected function toCss($cssObj,$tcpdf=null,$asStyle=false,$addBg=true)
    {
    	$cr="\n";
    	$css=array();
    	$fontfamily='';
    	$fontStyle='';
    	$fontWeight='';
    	if ( isset($cssObj['path']) && !$asStyle)
    		$css[]= '/* '.$cssObj['path'].' */';
    	
    	
    	if ( isset($cssObj['font_family']) && $cssObj['font_family'] )
    	{
    		$ff = explode(':',$cssObj['font_family']);
    		if ( isset($cssObj['font_style']) && $cssObj['font_style'])
    		{
    			$s = $cssObj['font_style'];
    			$ff = explode(':',$s);
    			if ( trim($ff[0]) && trim($ff[1]) )
    			{
	    			$s = strtolower($s);
	    			if ( strpos($s,'bold') !== false )
	    				$fontWeight='bold';
	    			if ( strpos($s,'italic') !== false )
	    				$fontWeight='italic';
    			}
    		}
    		if ( trim($ff[0]) && trim($ff[1]))
    		{
    			if ( !$fontStyle )
    				$fontStyle='normal';
    			if ( !$fontWeight )
    				$fontWeight='normal';
    			$fontfamily = 'bl-'.$ff[1];
	    		$fontface='';
	    		$fontface.=$cr.'@font-face {';
	    		$fontface.=$cr.'font-family: "'.$fontfamily.'";';
	    		if ( $this->getMode() == 'epub3' )
	    		{
	    			$fontface.=$cr.'src:url("../fonts/'.str_replace(' ','_',$ff[0]).'");';
	    		}else {
	    			$fontface.=$cr.'src:url("'.Mage::helper('auit_publicationbasic/font')->getFontUrl($ff[0]).'");';
	    		}
	    		$fontface.=$cr.'font-style: '.$fontStyle.';';
	    		$fontface.=$cr.'font-weight: '.$fontWeight.';';
	    		$fontface.=$cr.'}';
	    		if ( !isset($this->_fontfaces[$fontface]) ) {
	    			
	    			if ( $tcpdf )
	    			{
	    				$fontfamily = $tcpdf->addTTFfont(Mage::helper('auit_publicationbasic/font')->getFontDir($ff[0]));
	    			} else {
	    				$css[]=$fontface;
	    			}
	    			//$fontfamily='vollkorn';
	    			$this->_fontfaces[$fontface]=$fontfamily;
	    			$this->_fontfiles[]=$ff[0];
	    		}else if ( $tcpdf )
	    			$fontfamily =$this->_fontfaces[$fontface];
    		}
    	}
    	
    	$cssChar=array();
    	
    	if ( $fontfamily )
    		$cssChar[]="font-family:'$fontfamily';";
    	if (   !$tcpdf ) {
	    	if ( $fontStyle )
	    		$cssChar[]="font-style:$fontStyle;";
	    	if ( $fontWeight )
	    		$cssChar[]="font-weight:$fontWeight;";
    	}
    	if ( isset($cssObj['font_size']) && $cssObj['font_size'] )
			$cssChar[]='font-size:'.$this->_calcSize($cssObj['font_size']).';';
    		
    	
    	if ( isset($cssObj['colour']) && $cssObj['colour'] )
    	{
    		if ( $tcpdf )
    			$cssChar[]='color:'.$this->toPDFColor($cssObj['colour']).';';
    		else
    			$cssChar[]='color:'.$cssObj['colour'].($tcpdf?';':' ;');//!important
    	}
    	 
    	$cssBox=array();
    	if ( isset($cssObj['leading']) && $cssObj['leading'] )
    	{
    		
			if (!$tcpdf) {
    			$cssBox[]='line-height:'.$cssObj['leading'].';';
			}else {
				$cssBox[]='line-height:'.$cssObj['leading'].';';
			}
		}
		
		if ( !$tcpdf )
		{
	    	if ( isset($cssObj['left_indent']) && $cssObj['left_indent'] )
	    		$cssBox[]='padding-left:'.$cssObj['left_indent'].';';
	    	if ( isset($cssObj['right_indent']) && $cssObj['right_indent'] )
	    		$cssBox[]='padding-right:'.$cssObj['right_indent'].';';
	    	if ( isset($cssObj['space_before']) && $cssObj['space_before'] )
	    		$cssBox[]='padding-top:'.$cssObj['space_before'].';';
	    	if ( isset($cssObj['space_after']) && $cssObj['space_after'] )
	    		$cssBox[]='padding-bottom:'.$cssObj['space_after'].';';
		}
	    	
    	//if ( !$tcpdf && isset($cssObj['colour_background']) && $cssObj['colour_background'] )
    	if ( $addBg && isset($cssObj['colour_background']) && $cssObj['colour_background'] )
    	{
    		if ( $tcpdf )
    			$cssBox[]='background-color:'.$this->toPDFColor($cssObj['colour_background']).($tcpdf?';':' ;'); //!important
    		else
	   			$cssBox[]='background-color:'.$cssObj['colour_background'].($tcpdf?';':' ;'); //!important
    	}
    	if ( !$asStyle )
    	{
    		$className = '';
    		if (isset($cssObj['alias']) && $cssObj['alias'])
    		{
    			if ( !$tcpdf )
    				$className .= '#auit-layout-frame .'.self::CLPREFIX.$cssObj['alias'].',';
    			else
    				$className .= '.auit-layout-text-frame.'.self::CLPREFIX.$cssObj['alias'].',';
    				
//    			$className .= '.'.self::CLPREFIX.$cssObj['alias'].',';
    		}
    		/***
    		if ( !$tcpdf )
    			$className .= '#auit-layout-frame .'.self::CLPREFIX.$cssObj['uid'].',';
    		else
    			$className .= '.auit-layout-text-frame.'.self::CLPREFIX.$cssObj['uid'].',';
    		$className .= '.'.self::CLPREFIX.$cssObj['uid'];
    		**/
    		if ( isset($cssObj['uid']) )
    		{
	    		if ( !$tcpdf )
	    			$className .= '#auit-layout-frame .'.self::CLPREFIX.$cssObj['uid'].'';
	    		else
	    			$className .= '.auit-layout-text-frame.'.self::CLPREFIX.$cssObj['uid'].'';
    		}
    		
    		$css[]= $className.'{';
    	}
    	if ( count($cssBox) )
    		$css[]=implode($asStyle?'':"\n",$cssBox);
    	if ( count($cssChar) )
    		$css[]=implode($asStyle?'':"\n",$cssChar);
    	if ( !$asStyle )
    	{
	    	$css[]='}';
	    	/*
	    	if ( 0 )
			if (  count($cssChar) )
			{
	    		$className = str_replace(',',' *,',$className);
	    		$tcpa = $className; 
	    		$className .= ' *';
	    		if ( $tcpdf ){
	    			$className .= ', '.$tcpa.' a'; 
	    		}

				$css[]= $className.'{';

				if ( $tcpdf )
					$css[]=implode($asStyle?'':"\n",$cssChar);
				else
					$css[]=implode($asStyle?'':"\n",$cssChar);
				
				$css[]='}';
			}
				*/    			
    	}
    	$css[]='';
		return implode($asStyle?'':"\n",$css);
	}
    public function getComputedStyle($class,$att=null,$default=null)
    {
    	$class = str_replace('bl-','',$class);
    	$data = $this->getStyleData();
    	$this->_styles=array();
    	if ( isset($data['styles']) )
    		$this->_styles = $data['styles'];
    	$this->_fontfaces = array();
    	if ( isset($data['styles']) )
		foreach ( $data['styles'] as $item )
    	{
    		if ( (isset($item['uid']) && $item['uid'] == $class)
    			 || (isset($item['alias']) && $item['alias'] == $class) )
    		{
    			$data = $this->buildCssObject($item);
    			if ( is_null($att) )
    				return $data;
    			if ( isset($data[$att]) )
    			{
    				return $data[$att];
    			}
    			break;
    		}
    	}
		return $default;
    	
    }
    public function getCSS($tcpdf=null,$class=false)
    {
    	if ( $class )
    		$class = str_replace('bl-','',$class);
    	$data = $this->getStyleData();
    	$this->_styles=array();
    	if ( isset($data['styles']) )
    		$this->_styles = $data['styles'];
    	$this->_fontfaces = array();
    	$css='';
    	if ( isset($data['styles']) )
	   	foreach ( $data['styles'] as $style )
    	{
    		if ( !$class || (isset($style['uid']) && $style['uid'] == $class)
    				|| (isset($style['alias']) && $style['alias'] == $class) )
    		$css.= "\n".$this->toCss($this->buildCssObject($style),$tcpdf);
    	}
    	unset($this->_styles);
    	$this->_styles = null;
    	$this->_fontfaces = array();
    	//if ( $tcpdf ) 
    		//Mage::log($css);
    	return $css;
    }
    public function getBoxStyle($tcpdf,$blockInfo,$bonlybox=false)
    {
    	$cssObject = array();
    	$cssObject['uid']=$blockInfo->getUid();
    	if ( !$bonlybox && $blockInfo->getClass() )
    		$cssObject = $this->getComputedStyle($blockInfo->getClass(),null,array());
    	
    	if ( $blockInfo->getStyleColour() )
    		$cssObject['colour']=$blockInfo->getStyleColour();
    	if ( $blockInfo->getStyleLeading() )
    		$cssObject['leading']=$blockInfo->getStyleLeading();
    	if ( $blockInfo->getStyleFontSize() )
    		$cssObject['font_size']=$this->_calcSize($blockInfo->getStyleFontSize());
    	if ( $blockInfo->getStyleFontFamily() && $blockInfo->getStyleFontFamily()!=':')
    	{
    		$cssObject['font_family']=$blockInfo->getStyleFontFamily();
    		$cssObject['font_style']=$blockInfo->getStyleFontStyle();
    	}
    	
    	 
    	$style='';
		if ( count($cssObject)>0 )
		{
	    	$style = $this->toCss($cssObject,$tcpdf,false,false);
		}
	
//		Mage::log('----------------');
	//	Mage::log($style);
    	return array('css'=>$style,'cssObj'=>$cssObject);
    }
    public function getClassNames()
    {
    	$data = $this->getStyleData();
    	$options = array();
    	if ( isset($data['styles']) )
    	{
    		foreach ( $data['styles'] as $item )
    		{
    			if ( isset($item['alias']) )
    				$options[self::CLPREFIX.$item['alias']]=$item ['name'];
    			else
    				$options[self::CLPREFIX.$item['uid']]=$item ['name'];
    		}
    	}
    	return $options;
    }
    public function getStyleData()
    {
    	if ( !$this->_jsonData )
    	{
    		$this->_jsonData = array();
    		try {
    			if ( trim($this->getData('data')) )
    				$this->_jsonData = Mage::helper('core')->jsonDecode($this->getData('data'));
    			
    		}
    		catch (Exception $e)
    		{
    			Mage::log($this->getData());
    			Mage::logException($e);
    		}
    	}
    	return $this->_jsonData;
    }
    
    public function toPDFColor($hcolor)
    {
    	$color = $this->ColorToArray($hcolor);
    	
    	return 'rgb('.$color[0].','.$color[1].','.$color[2].')';
    }
    public function ColorToArray($hcolor)
    {
    	$color = preg_replace('/[\s]*/', '', $hcolor); // remove extra spaces
    	$color = strtolower($color);
        if (substr($color, 0, 4) == 'rgba') {
    		$codes = substr($color, 5);
    		$codes = str_replace(')', '', $codes);
    		$returncolor = explode(',', $codes);
    		$alpha=1;
    		if ( isset($returncolor[3]) )
    			$alpha=$returncolor[3];
    		foreach ($returncolor as $key => $val) {
    			if (strpos($val, '%') > 0) {
    				// percentage
    				$returncolor[$key] = (255 * intval($val) / 100);
    			} else {
    				$returncolor[$key] = intval($val);
    			}
    			// normalize value
    			$returncolor[$key] = max(0, min(255, $returncolor[$key]));
    		}
    		$returncolor[3]=$alpha;
    		return $returncolor;
		}
    	// RGB ARRAY
    	if (substr($color, 0, 3) == 'rgb') {
    		$codes = substr($color, 4);
    		$codes = str_replace(')', '', $codes);
    		$returncolor = explode(',', $codes);
    		foreach ($returncolor as $key => $val) {
    			if (strpos($val, '%') > 0) {
    				// percentage
    				$returncolor[$key] = (255 * intval($val) / 100);
    			} else {
    				$returncolor[$key] = intval($val);
    			}
    			// normalize value
    			$returncolor[$key] = max(0, min(255, $returncolor[$key]));
    		}
    		return $returncolor;
    	}
    	// CMYK ARRAY
    	if (substr($color, 0, 4) == 'cmyk') {
    		$codes = substr($color, 5);
    		$codes = str_replace(')', '', $codes);
    		$returncolor = explode(',', $codes);
    		foreach ($returncolor as $key => $val) {
    			if (strpos($val, '%') !== false) {
    				// percentage
    				$returncolor[$key] = (100 * intval($val) / 100);
    			} else {
    				$returncolor[$key] = intval($val);
    			}
    			// normalize value
    			$returncolor[$key] = max(0, min(100, $returncolor[$key]));
    		}
    		return $returncolor;
    	}
    	if ($color{0} != '#') {
    		// COLOR NAME
    		if (isset($this->webcolor[$color])) {
    			// web color
    			$color_code = $this->webcolor[$color];
    		} else {
    			// spot color
    			$returncolor = $this->getSpotColor($color);
    			if ($returncolor === false) {
    				$returncolor = $defcol;
    			}
    			return $returncolor;
    		}
    	} else {
    		$color_code = substr($color, 1);
    	}
    	// HEXADECIMAL REPRESENTATION
    	switch (strlen($color_code)) {
    		case 3: {
    			// 3-digit RGB hexadecimal representation
    			$r = substr($color_code, 0, 1);
    			$g = substr($color_code, 1, 1);
    			$b = substr($color_code, 2, 1);
    			$returncolor = array();
    			$returncolor['R'] = max(0, min(255, hexdec($r.$r)));
    			$returncolor['G'] = max(0, min(255, hexdec($g.$g)));
    			$returncolor['B'] = max(0, min(255, hexdec($b.$b)));
    			break;
    		}
    		case 6: {
    			// 6-digit RGB hexadecimal representation
    			$returncolor = array();
    			$returncolor['R'] = max(0, min(255, hexdec(substr($color_code, 0, 2))));
    			$returncolor['G'] = max(0, min(255, hexdec(substr($color_code, 2, 2))));
    			$returncolor['B'] = max(0, min(255, hexdec(substr($color_code, 4, 2))));
    			break;
    		}
    		case 8: {
    			// 8-digit CMYK hexadecimal representation
    			$returncolor = array();
    			$returncolor['C'] = max(0, min(100, round(hexdec(substr($color_code, 0, 2)) / 2.55)));
    			$returncolor['M'] = max(0, min(100, round(hexdec(substr($color_code, 2, 2)) / 2.55)));
    			$returncolor['Y'] = max(0, min(100, round(hexdec(substr($color_code, 4, 2)) / 2.55)));
    			$returncolor['K'] = max(0, min(100, round(hexdec(substr($color_code, 6, 2)) / 2.55)));
    			break;
    		}
    		default: {
    			$returncolor = $defcol;
    			break;
    		}
    	}
    	return $returncolor;
    }
    public function addDependence(&$dep)
    {
    	$data = $this->getStyleData();
    	if ( isset($data['styles']) )
    	{
    		foreach ( $data['styles'] as $item )
    		{
    			if ( isset($item['font_style']) && $item['font_style']  )
    			{
    				$tmp = explode(':',$item['font_style']);
    				$fn = trim($tmp[0]);
    				if ( $fn )
    					$dep['fonts'][$fn]=$fn;
    			}
    		}
    	}
    }
    public function exportTo(&$package,$rootDir,$templId)
    {
    	$Identifier = $this->getIdentifier();
    	if ( isset($package['styles'][$Identifier] ) )
    		 return;
    	$data = $this->getData();
    	$data['style_id']=null;
    	file_put_contents($rootDir.DS.'style_'.$Identifier.'.ser', serialize($data));
    	$package['styles'][$Identifier]=1;
    }
    public function importData(&$messages,$templateDir,$templdata,$bvalidate)
    {
    	if ( isset($templdata['style_id']) )
    		unset($templdata['style_id']);
    	$this->addData($templdata);
    }
}