<?php
/**
 * AuIt
 *
 * @category   AuIt
 * @author     M Augsten
 * @copyright  Copyright (c) 2010 Ingenieurbüro (IT) Dipl.-Ing. Augsten (http://www.au-it.de)
 */
class AuIt_PublicationBasic_Helper_Config extends Mage_Core_Helper_Abstract
{

	public function getDefaults($path)
    {
    	switch ( $path )
    	{
    		case 'auit_layoute/'.'r':
				return $this->__('Plea'.'se c'.'hec'.'k y'.'our '.'lic'.'ense'.' k'.'ey!');
    	}
    	
/**    	
 * 			$_options['DIN-A#297#420'] = $this->__('A3');
			$_options['DIN-A#210#297'] = $this->__('A4');
			$_options['DIN-A#148#210'] = $this->__('A5');
			$_options['DIN-B#176#250'] = $this->__('B5');
			$_options['FREE#119.944#120.65'] = $this->__('Compact Disc');
			*
			$_options['DIN-A,841,1189'] = $this->__('DIN A0');
			$_options['DIN-A,594,841'] = $this->__('DIN A1');
			$_options['DIN-A,420,594'] = $this->__('DIN A2');
			$_options['DIN-A,105,148'] = $this->__('DIN A6');
			$_options['DIN-A,74,105'] = $this->__('DIN A7');
*

			$_options['US#215.9#279.4'] = $this->__('Letter (8½ × 11)');
			$_options['US#215.9#355.6'] = $this->__('Legal (8½ × 14)');
			$_options['US#,279.4#431.8'] = $this->__('Tabloid (11 × 17)');

 */
    	return '';
	}
	public function getConfigData()
    {
    	return '';
    }
    public function convertStringToHexString($s) {
    	$bs = '';
    	$chars = preg_split('//', $s, -1, PREG_SPLIT_NO_EMPTY);
    	foreach ($chars as $c) {
    		$bs .= sprintf('%02s', dechex(ord($c)));
    	}
    	return $bs;
    }
    
}