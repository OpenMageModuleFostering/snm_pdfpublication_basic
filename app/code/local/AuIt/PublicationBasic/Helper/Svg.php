<?php
class AuIt_PublicationBasic_Helper_Svg extends Mage_Core_Helper_Abstract
{
	
	public function getClipGroups()
	{
		$result=array();
		$file   = Mage::getModuleDir('data', 'AuIt_PublicationBasic') . DS . 'shapelib'.DS.'categories.json';
		if ( file_exists($file) ) {
			$categories=json_decode ( file_get_contents($file),true );
			foreach ( $categories['categories'] as $base => $name )
			{
				$file   = Mage::getModuleDir('data', 'AuIt_PublicationBasic') . DS . 'shapelib'.DS.$base.'.json';
				if ( file_exists($file) ) {
					$group=json_decode ( file_get_contents($file),true );
					$childs = array();
					foreach ( $group['data'] as $svg => $path)
					{
						$childs[]=array('id'=>"{$base}:{$svg}");
					}
					if ( count($childs) )
						$result[]=array('id'=>$base,'name'=>$name,'childs'=>$childs);					
				}

			}
		}
		return $result;
	}
	protected function _getPath($group,$fileName)
	{
		$file   = Mage::getModuleDir('data', 'AuIt_PublicationBasic') . DS . 'shapelib'.DS.$group.'.json';
		if ( file_exists($file) )
		{
			$json = json_decode ( file_get_contents($file),true );
			if ( isset($json['data']) && isset($json['data'][$fileName]) )
			{
				
				return array(
					'size' => isset($json['size'])?$json['size']:300,
					'path' => $json['data'][$fileName]
				);
			}
		}
		return array(
			'size' => 300,
			'default'=>1,
			'path' => 'm1,75.5l298,0l-149,74.5l149,74.5l-298,0l149,-74.5l-149,-74.5z'
		);
	}
	public function getPath($fileName)
	{	
		$fileName =explode(':',$fileName);
		$group = $fileName[0];
		$fileName = $fileName[1];
		$pathInfo = $this->_getPath($group,$fileName);
		
		return $pathInfo;
	}
	public function getDynSvg($mode,$group,$fileName)
	{
		if ( !$group )
		{
			$fileName =explode(':',$fileName);
			$group = $fileName[0];
			$fileName = $fileName[1];
			
		}	
			
		$pathInfo = $this->_getPath($group,$fileName);
		$size = $pathInfo['size'];
		$off 	= $size*0.05;
		$off=0;
		$vb  = join(' ',array(-$off, -$off, $size + $off*2, $size + $off*2));
		//$vb  = join(' ',array(0, 0, $size + $off*2, $size + $off*2));
		$svg = '<?xml version="1.0" encoding="UTF-8" standalone="no"?>';
		switch ( $mode )
		{
			case 'mask':
				$svg .= '<svg xmlns="http://www.w3.org/2000/svg" version="1.1">';
				$svg .= '<svg viewBox="'.$vb.'">';
				
			//	$svg .= '<path id="svgMask" fill="white" d="'.$pathInfo['path'].'"  />';
				
				$svg .= '<defs>';
				//maskUnits="objectBoundingBox" maskContentUnits="objectBoundingBox"
				//$svg .= '<mask viewBox="'.$vb.'" id="svgMask" maskUnitsX="objectBoundingBox" x="0" y="0" width="'.$size.'" height="'.$size.'" maskContentUnitsx="objectBoundingBox" >';
				$svg .= '<mask id="svgMask" >';
				$svg .= '<text x="10" y="20" fill="white" color="black">For clipping preview use PDF, Chrome or Safari</text>';
			//	$svg .= '<circle cx="0.5" cy="0.5" r="0.5" fill="white"/>';
				//				$svg .= '<path fill="white" d="'.$pathInfo['path'].'"  />';
				$svg .= '</mask>';
				$svg .= '</defs>';
				
				$svg .= '<g ><path id="svgMask" fill="white" d="'.$pathInfo['path'].'"  /></g>';
				$svg .= '</svg>';
				$svg .= '</svg>';
				break;
			case 'clip':
				$svg .= '<clipPath id="svgClip">';
				$svg .= '<path id="svgPath" d="'.$pathInfo['path'].'"  />';
				$svg .= '</clipPath>';
				break;
			case 'icon':
			default:
				$svg .= '<svg xmlns="http://www.w3.org/2000/svg" version="1.1"   viewBox="'.$vb.'">';
				$svg .= '<g><path  d="'.$pathInfo['path'].'"  fill="#333" /></g>';
				$svg .= '</svg>';
				break;
		}
		return $svg;
	}
	
	 
	
	
}