<?php
/**
 * AuIt 
 *
 * @category   AuIt
 * @author     M Augsten
 * @copyright  Copyright (c) 2010 Ingenieurbüro (IT) Dipl.-Ing. Augsten (http://www.au-it.de)
 */
class AuIt_PublicationBasic_Model_Adminhtml_Idtemplate extends AuIt_PublicationBasic_Model_Adminhtml_Arraytemplate
{
    protected function getNextId($value)
    {
    	$id = 50;
        foreach ( $value as &$item )
        {
        	if ( isset($item['id']) && ($item['id']>0) && $item['id'] > $id )
        	{
        		$id = $item['id'];
        	} 
        }
        $id++;
        return $id;
    }
	protected function _beforeSave()
    {
        $value = $this->getValue();
        if (is_array($value)) {
        	unset($value['__empty']);
        	foreach ( $value as &$item )
        	{
        		if ( !isset($item['id']) || !($item['id']>0) )
        		{
        			$item['id'] = $this->getNextId($value);
        		} 
        	}
        }
        if (is_array($value)) {
	        $this->setValue('base64:'.base64_encode(serialize($value))); 
        }
       	//parent::_beforeSave();
    }
}
