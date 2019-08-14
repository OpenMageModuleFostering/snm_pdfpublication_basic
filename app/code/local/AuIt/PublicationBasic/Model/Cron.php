<?php
/**
 * AuIt
 *
 * @category   AuIt
 * @author     M Augsten
 * @copyright  Copyright (c) 2010 Ingenieurbüro (IT) Dipl.-Ing. Augsten (http://www.au-it.de)
 */
class  AuIt_PublicationBasic_Model_Cron extends Mage_Core_Model_Abstract
{
    const XML_PATH_EMAIL_TEMPLATE     = 'auit_publicationbasic/crontab/error_email_template';
    const XML_PATH_EMAIL_IDENTITY     = 'auit_publicationbasic/crontab/error_email_identity';
    const XML_PATH_EMAIL_RECIPIENT    = 'auit_publicationbasic/crontab/error_email';
    const XML_PATH_ENABLED            = 'auit_publicationbasic/crontab/enabled';
    

    protected $_minutes = 5;

	/**
	 *
	 * @param AuIt_Cronjob_Model_Convert_Profile $profile
	 * @return AuIt_Cronjob_Model_Cron
	 */
    protected function _sendEmail($profile)
    {
        if (!Mage::getStoreConfig(self::XML_PATH_EMAIL_RECIPIENT)) {
            return $this;
        }
        $translate = Mage::getSingleton('core/translate');
        /* @var $translate Mage_Core_Model_Translate */
        $translate->setTranslateInline(false);

        $emailTemplate = Mage::getModel('core/email_template');

        /* @var $emailTemplate Mage_Core_Model_Email_Template */
        $emailTemplate->setDesignConfig(array('area' => 'backend'))
            ->sendTransactional(
                Mage::getStoreConfig(self::XML_PATH_EMAIL_TEMPLATE),
                Mage::getStoreConfig(self::XML_PATH_EMAIL_IDENTITY),
                'root',//Mage::getStoreConfig(self::XML_PATH_EMAIL_RECIPIENT),
                null,
                array('message' => $profile->getMessages())
            );

        $translate->setTranslateInline(true);
        return $this;
    }
    public function defaultHandler($observer)
    {
    	$collection = Mage::getResourceModel('auit_publicationbasic/jobqueue_collection')->
    	addFieldToFilter('status',array('in' => array(AuIt_PublicationBasic_Model_Jobqueue::STATE_START_NOW)));
    	$collection->walk('afterLoad');
    	foreach ($collection as $item )
    	{
    		$this->runJob($item);
    	}
    }
    protected function _checkTemplateThumnails()
    {
    	
    }
    /**
	 *
	 * @param unknown_type $option
	 * @return AuIt_Cronjob_Model_Convert_Profile
	 */
    public function runJob($jobItem)
    {
    	Mage::helper('auit_publicationbasic')->log(Mage::helper('auit_publicationbasic')->__('Start Job : %s',$jobItem->getId()));
		try{
			$jobItem->runJob();
		} catch (Exception $e)
		{
			Mage::helper('auit_publicationbasic')->log(Mage::helper('auit_publicationbasic')->__('Job`(%s) Exception : %s',$jobItem->getId(),$e->getMessage()));
		}
    	Mage::helper('auit_publicationbasic')->log(Mage::helper('auit_publicationbasic')->__('End Job : %s',$jobItem->getId()));

    }
    public function runScheduled($observer,$ids='')
    {
    	if(!Mage::getStoreConfigFlag(self::XML_PATH_ENABLED))
    		return $this;
    	
    	$collection = Mage::getResourceModel('auit_publicationbasic/jobqueue_collection')->
    	addFieldToFilter('status',array('in' => array(AuIt_PublicationBasic_Model_Jobqueue::STATE_START_NOW)));
    	$collection->walk('afterLoad');
    	foreach ($collection as $item )
    	{
    		$this->runJob($item);
    	}
    	 
    	
   		$collection = Mage::getResourceModel('auit_publicationbasic/jobqueue_collection')->
   		addFieldToFilter('status',
   				array('in' => array(AuIt_PublicationBasic_Model_Jobqueue::STATE_WAIT)));
   		$collection->addFieldToFilter('prio',array('neq'=>'0'))
   		->setOrder('prio', Varien_Data_Collection::SORT_ORDER_DESC)
   		->setCurPage(1)
   		->setPageSize(1);
   		$collection->walk('afterLoad');
   		foreach ($collection as $item )
   		{
   			$this->runJob($item);
   		}
    	return $this;
    }
}
