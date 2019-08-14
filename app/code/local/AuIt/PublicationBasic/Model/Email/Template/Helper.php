<?php
/**
 * AuIt
 *
 * @category   AuIt
 * @author     M Augsten
 * @copyright  Copyright (c) 2010 Ingenieurbüro (IT) Dipl.-Ing. Augsten (http://www.au-it.de)
 */
class AuIt_PublicationBasic_Model_Email_Template_Helper  extends Varien_Object
{
	function getValue($a)
	{
		if ( !is_null($a) && $this->getProcessor())
		{
			$r = $this->getProcessor()->filter('{{var '.$a.'}}');
			if ( $r === 'Object' )
			{
				$vars = $this->getProcessor()->getVariables();
				if ( isset($vars[$a]) ) {
					$r=$vars[$a];
				}
			}
			if ( is_object($r) || $r == '')
			{
				if ( $r instanceof Varien_Object )
				{
					$r = implode(',', $r->debug());
				}
			}
			if ( !$r ) $r=$a;
			return $r;
		}
		return $a;
	}
	function eq($a=null,$b=null)
	{

		$a = $this->getValue($a);
		$b = $this->getValue($b);
		return ( !is_null($a) && !is_null($b)) ? ($a == $b): false;
	}
	function neq($a=null,$b=null)
	{
		$a = $this->getValue($a);
		$b = $this->getValue($b);
		return ( !is_null($a) && !is_null($b)) ? ($a != $b): false;
	}
	function lt($a=null,$b=null)
	{
		$a = $this->getValue($a);
		$b = $this->getValue($b);
		return ( !is_null($a) && !is_null($b)) ? ($a < $b): false;
	}
	function lteq($a=null,$b=null)
	{
		$a = $this->getValue($a);
		$b = $this->getValue($b);
		return ( !is_null($a) && !is_null($b)) ? ($a <= $b): false;
	}
	function gt($a=null,$b=null)
	{
		$a = $this->getValue($a);
		$b = $this->getValue($b);
		return ( !is_null($a) && !is_null($b)) ? ($a > $b): false;
	}
	function gteq($a=null,$b=null)
	{
		$a = $this->getValue($a);
		$b = $this->getValue($b);
		return ( !is_null($a) && !is_null($b)) ? ($a >= $b): false;
	}
	function nl2br($a=null)
	{
		return nl2br(trim($this->getValue($a)));
	}

	function country($a=null)
	{
		$a = $this->getValue($a);
		if ( $a )
			return Mage::app()->getLocale()->getCountryTranslation($a);
		return '';
	}
	function date($a=null,$b=0,$c='medium',$showtime=0)
	{
		if ( !is_numeric($b)  )
		{
			$b = (int)$this->getProcessor()->auitVariable($b);
		}
    	$a = $this->getProcessor()->auitVariable($a);
    	$result = 'not a date';
    	if ($a instanceof Zend_Date) {
    		$result = Mage::helper('core')->formatDate($a.("$b days"), $c, $showtime?true:false);
    	}
		return $result;
	}
	function hasGiftMessage()
	{
		$order = $this->getProcessor()->auitVariable('order');
		if ( $order && is_object($order) && $order->getGiftMessageId() )
			return true;
		return false;
	}
	function hasComments()
	{
		$entity = $this->getProcessor()->auitVariable('entity');
		$_collection = null;
		if ( $entity && $entity instanceof Mage_Sales_Model_Order )
			$_collection = $entity->getStatusHistoryCollection();
		else if ( $entity )
			$_collection = $entity->getCommentsCollection();
		if ( $_collection && count($_collection) )
			return true;
		return false;
	}
	function hasVisibleComments()
	{
		$entity = $this->getProcessor()->auitVariable('entity');
		$_collection = null;
		if ( $entity && $entity instanceof Mage_Sales_Model_Order )
			$_collection = $entity->getStatusHistoryCollection();
		else if ( $entity )
			$_collection = $entity->getCommentsCollection();
		if ( $_collection && count($_collection) )
		{
			foreach ($_collection as $_comment)
				if ( $_comment->getIsVisibleOnFront() )
					return true;
		}
		return false;
	}
	function isCountryInEU($countryCode)
	{
		$countryCode = $this->getValue($countryCode);
		$helper = Mage::helper('core');
		if ( method_exists($helper,'isCountryInEU') )
		{
			$order = $this->getProcessor()->auitVariable('order');
			$storeId = null;
			if ( $order ) {
				$storeId = $order->getStore()->getId();
			}
			return 	$helper->isCountryInEU($countryCode,$storeId);
		}
		return false;
	}
	function getCustomerGroupName($customer_group_id)
	{
		$customer_group_id = $this->getValue($customer_group_id);
		return Mage::getModel('customer/group')
		->load($customer_group_id)
		->getCustomerGroupCode();
	}
	function roundPrice($price)
	{
		$price = $this->getValue($price);
		return Mage::app()->getStore()->roundPrice($price);
	}
	function round($price,$anzahl=2)
	{
		$price = $this->getValue($price);
		$anzahl= $this->getValue($anzahl);
		return round($price, $anzahl);
	}
	function formatPrice($price,$addBrackets=0)
	{
		$price = $this->getValue($price);
		$addBrackets = $this->getValue($addBrackets);
		$order = $this->getProcessor()->auitVariable('order');
		return $order->formatPrice($price, $addBrackets);
	}

}
