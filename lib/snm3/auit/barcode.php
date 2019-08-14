<?php
require_once 'snm3/tcpdf/tcpdf_barcodes_2d.php';
require_once 'snm3/tcpdf/tcpdf_barcodes_1d.php';
class AuIt_Barcode1D extends TCPDFBarcode
{
	function drawText($img,$x,$y,$w,$h, $chars='',$size=5)
	{
		$col = imagecolorallocate($img, 0, 0, 0);
		$cc = strlen($chars);
		$textw = imagefontwidth($size);
		$x = $w - ($textw*($cc+1));  
		imagestring($img,$size,$x,$y, $chars, $col);;
		return $img;
	}	
	public function getBarcodeAsPng($logo,$w=3, $h=3, $color) {
		// calculate image size
		$color=array(0,0,0);
		$w = $w/$this->barcode_array['maxw'];
		$width = ($this->barcode_array['maxw'] * $w);
		
		$textheight = imagefontheight(5);
		$textheight += $textheight/2;
		$height = $h;
		$h -= $textheight;
		if (function_exists('imagecreate')) {
			// GD library
			$png = imagecreate($width, $height);
			//imageSaveAlpha($png, true);
			//$trans_colour = imagecolorallocatealpha($png, 0, 0, 0, 127);
			//imagefill($png, 0, 0, $trans_colour);		
			$bgcol = imagecolorallocate($png, 255, 255, 255);
			imagecolortransparent($png, $bgcol);
			$fgcol = imagecolorallocate($png, $color[0], $color[1], $color[2]);
		}
		else {		
			return false;
		}
		// print bars
		$x = 0;
		foreach ($this->barcode_array['bcode'] as $k => $v) {
			$bw = round(($v['w'] * $w), 3);
			$bh = round(($v['h'] * $h / $this->barcode_array['maxh']), 3);
			if ($v['t']) {
				$y = round(($v['p'] * $h / $this->barcode_array['maxh']), 3);
				// draw a vertical bar
				imagefilledrectangle($png, $x, $y, ($x + $bw - 1), ($y + $bh - 1), $fgcol);
			}
			$x += $bw;
		}
		$text = $this->barcode_array['code'];
		$this->drawText($png,0,$h+$textheight/4,$width,$textheight, $text);
		ob_start();
		imagepng($png);
		$data =ob_get_clean();
		imagedestroy($png);
		return $data;
	}
}
class AuIt_Barcode extends TCPDF2DBarcode
{
	public function getBarcodeWithLogo($logo,$w=3, $h=3, $color=null) {
		// calculate image size
		$color = Mage::getSingleton('auit_publicationbasic/styles')->ColorToArray($color);
		
		
		$width = ($this->barcode_array['num_cols'] * $w);
		$height = ($this->barcode_array['num_rows'] * $h);
		/*
		if (!extension_loaded('imagick')) {
			Mage::log ('AuIt_Barcode::getBarcodeWithLogo: imagick not found' );
			$filename = Mage::getBaseDir().DS.'js'. DS.'auit'.DS.'publication'.DS.'images'.DS.'no-imagick.jpg';
			return file_get_contents($filename);
		}
		*/
		if (!($width>0)||!($height>0) ) {
			Mage::log ("AuIt_Barcode::getBarcodeWithLogo: width or height empty" );
			return false;
		}
		
		$img = imagecreatetruecolor($width,$height);
	//	imageAlphaBlending($img, false);
		imageSaveAlpha($img, true);
		
		$trans_colour = imagecolorallocatealpha($img, 0, 0, 0, 127);
		imagefill($img, 0, 0, $trans_colour);	
		if ( $color && is_array($color) )
			$bgc = ImageColorAllocate ($img, $color[0], $color[1], $color[2]);
		else
			$bgc = ImageColorAllocate ($img, 0, 0, 0);		
		$y = 0;
		for ($r = 0; $r < $this->barcode_array['num_rows']; ++$r) {
			$x = 0;
			for ($c = 0; $c < $this->barcode_array['num_cols']; ++$c) {
				if ($this->barcode_array['bcode'][$r][$c] == 1) {
					imagefilledrectangle ( $img , $x, $y, ($x + $w - 1), ($y + $h - 1), $bgc);
				}
				$x += $w;
			}
			$y += $h;
		}
		
		
		
		if (  $logo && is_file($logo) )
		{
			try {
				$logoImg = imagecreatefromstring ( file_get_contents($logo) );
				list($src_w, $src_h) = @getimagesize($logo);
				$maxWidth=$width/2;
				$maxHeight=$height/2;
				
				$srcRatio  = $src_w/$src_h;
				$destRatio = $maxWidth/$maxHeight;
				if ($destRatio > $srcRatio) {
					$dh = $maxHeight;
					$dw = $maxHeight*$srcRatio;
				}
				else {
					$dw = $maxWidth;
					$dh = $maxWidth/$srcRatio;
				}				
				$x = ($width-$dw)/2;
				$y = ($height-$dh)/2;
				imagecopyresized ( $img, $logoImg, $x , $y, 0 , 0 , $dw , $dh, $src_w , $src_h );
			}
			catch ( Exception $e )
			{
				Mage::log ("AuIt_Barcode::getBarcodeWithLogo: can't load logo - ".$e->getMessage() );
				Mage::logException($e);
			}
		
		}
		ob_start();
		imagepng($img);
		$data =ob_get_clean();
		imagedestroy($img);
		return $data; 
	}
}
