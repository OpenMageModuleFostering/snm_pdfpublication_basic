<?php
/**
 * AuIt
 *
 * @category   AuIt
 * @author     M Augsten
 * @copyright  Copyright (c) 2010 Ingenieurbüro (IT) Dipl.-Ing. Augsten (http://www.au-it.de)
 */
class AuIt_PublicationBasic_Helper_Font extends Mage_Core_Helper_Abstract
{
	const PATH_FONTS='snm-portal/fonts';
	const URL_FONTS='snm-portal/fonts';
	public function getFontPath()
    {
		return  Mage::getBaseDir('media').DS.self::PATH_FONTS;
    }
	public function getFontUrl($font)
    {
		return $path = Mage::getBaseUrl('media').self::URL_FONTS.DS.$font;
    }
	public function getFontDir($font)
    {
		return $path = $this->getFontPath().DS.$font;
    }
    
    public function getFonts($ball = false)
    {
    	$path = $this->getFontPath();
    	$r = array();
    	$r[]=array(
    			'name' => $this->__('Default'),
    			'uid' => ':',
    			'styles' => Array
    			(
    					Array(
    							'uid' => ':',
    							'name' => $this->__('Default'),
    							'style' => $this->__('Default'),
    					)
    			),
    			'visible' => 1
    	);
		$result = $this->_loadFonts($path);
    	foreach ( $result as $i ){
    		$i['visible']=1;
    		$r[]=$i;
    	}
    	if ( $ball )
    	{
	    	$result = $this->_loadFonts($path.DS.'hidden');
	    	foreach ( $result as $i ){
	    		$i['visible']=0;
	    		$r[]=$i;
	    	}
    	}
    	return $r;
    }
    public function checkFont($filename,$v)
    {
    	if ( $v == 1){
	    	$from = $this->getFontPath().DS.'hidden'.DS.$filename;
	    	$to = $this->getFontPath().DS.$filename;
    	}
    	else
    	{
    		$to = $this->getFontPath().DS.'hidden'.DS.$filename;
    		$from = $this->getFontPath().DS.$filename;
    	}
    	if ( file_exists($from) )
    	{
    		@mkdir(dirname($to));
    		@rename($from, $to);
    	}

    }
    public function loadGoogleFont($fn)
    {
    	$url = 'http://fonts.googleapis.com/css?family='.$fn;
    	
    	$data = file_get_contents($url);
    	preg_match_all('|src:([^;]*);|usiU',$data,$founds);
        if ( is_array($founds) && count($founds) == 2 )
    	{
    		foreach ( $founds[1] as $font )
    		{
    			$ln='';
    			$url='';
    			preg_match('|local\(([^\(]*)\)|usiU',$font,$local);
    			if ( is_array($local) && count($local) == 2 )
    			{
    				$ln=trim($local[1]," '");
    			}
    			preg_match('|url\(([^\(]*)\)|usiU',$font,$local);
    			if ( is_array($local) && count($local) == 2 )
    			{
    				$url=trim($local[1]);
    			}
    			$this->_loadFile($url,$this->getFontPath().DS.$ln.'.ttf');
    		}
    	}else {
    		Mage::log("AuIt_PublicationBasic_Helper_Font::loadGoogleFont can't load font: $fn");
    	}
    }
    protected function _loadFile($url,$to)
    {
    	if (!ini_get('allow_url_fopen')	)
    	{
    		$ch = curl_init();
    		curl_setopt($ch, CURLOPT_URL, $url);
    		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    		curl_setopt($ch, CURLOPT_HEADER, 0);
    		$data = curl_exec($ch);
    		curl_close($ch);
    	}else {
    		$data = @file_get_contents($url);
    	}
    	if ( $data && $to )
    	{
    		@file_put_contents($to, $data);
    	}
    }

    /*
    public function getFontsG($fullPath=false)
    {
    	$file   = Mage::getModuleDir('data', 'AuIt_PublicationBasic') . DS . 'gfont.ser';
    	if ( !file_exists($file) )
    	{
    		//<link href='http://fonts.googleapis.com/css?family=Archivo+Narrow:400,700' rel='stylesheet' type='text/css'>
    		$gf = json_decode(file_get_contents('https://www.googleapis.com/webfonts/v1/webfonts'));
    		if ( $gf && is_array($gf->items) )
    		{

    			$fonts = array();
				foreach ($gf->items as $item )
				{
					$name = $item->family;
					$styles=array();
					$baseuid= '';
					foreach ( $item->variants as $variant )
					{
						$styles[]=array(
								'name'=>$name,
								'uid'=>$name.':'.$variant,
								'style'=>$variant
								);
						if ( !$baseuid ) $baseuid=$name.':'.$variant;
						if ( $variant == 'regular' ) $baseuid=$name.':'.$variant;
					}
					$font = array(
							'name'=>$name,
							'uid'=>$baseuid,
							'styles'=>$styles
					);
					$fonts[]=$font;
				}
				file_put_contents($file,serialize($fonts));
    		}
    	}else
    		$fonts = unserialize(file_get_contents($file));


    	return $fonts;
    }
*/
    protected function _loadFonts($path)
    {
    	$result = array();
    	$dir = @opendir($path);
    	if ($dir) {
    		while ($entry = @readdir($dir)) {

    			if (substr($entry, 0, 1) == '.' || !is_file($path . DS . $entry)){
    				continue;
    			}
    			$FN = $entry;
    			$fullpath = $path . DS . $entry;

    			$lentry = strtolower($entry);
    			if ( pathinfo($lentry, PATHINFO_EXTENSION) != 'ttf' ) continue;
    			try {
    				$fontInfo = Mage::getModel('auit_publicationbasic/font');
    				if ( $fontInfo->setFile($fullpath) )
    				{
    					//Mage::log($fontInfo);
    					$idx = $fontInfo->getFontFamily();
    					
    					/*
    					 *     							' ID:'.$fontInfo->getFontIdentifier().
    					' FN:'.$fontInfo->getFontName().
    					' PN:'.$fontInfo->getPostscriptName()
    					//':'.$fontInfo->getCopyright()

    					*/
    					$uid = $FN.':'.$fontInfo->getPostscriptName();//$fontInfo->getFontName().':'.$fontInfo->getFontSubFamily();
    					$info = array(
    							'uid'=>	$uid,
    							'name'=>$fontInfo->getFontFamily(),
    							'style'=>$fontInfo->getFontSubFamily()
    					);
    					if ( !isset($result[$idx]) )
    						$result[$idx] = array('name'=>$idx,'uid'=>$idx.':','styles'=>array());
    					$result[$idx]['styles'][]=$info;
    				}
    			}catch (Exception $e){
    			}
    		}
    		unset($entry);
    		closedir($dir);
    	}
    	ksort($result);
    	return $result;
    }

}