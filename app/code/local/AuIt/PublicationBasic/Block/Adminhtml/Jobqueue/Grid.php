<?php
class AuIt_PublicationBasic_Block_Adminhtml_Jobqueue_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('publicationTextGrid');
        $this->setDefaultSort('jobqueue_id');
        $this->setDefaultDir('DESC');
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('auit_publicationbasic/jobqueue')->getCollection();
        $this->setCollection($collection);
        parent::_prepareCollection();
        $collection->walk('afterLoad');
        return $this;
    }
    protected function _prepareLayout()
    {
    	$this->setChild('refresh_button',
    			$this->getLayout()->createBlock('adminhtml/widget_button')
    			->setData(array(
    					'label'     => Mage::helper('adminhtml')->__('Refresh'),
    					'onclick'   => $this->getJsObjectName().'.doExport()',
    					'class'   => 'task'
    			))
    	);
    	return parent::_prepareLayout();
    }
    public function getRefreshButtonHtml()
    {
    	return $this->getChildHtml('refresh_button');
    }
    public function getMainButtonsHtmlXXX()
    {
    	return $this->getRefreshButtonHtml().parent::getMainButtonsHtml();
    }
    protected function _prepareColumns()
    {
    	$url7zip = Mage::helper('adminhtml')->__('The archive can be uncompressed with <a href="%s">%s</a> on Windows systems', 'http://www.7-zip.org/', '7-Zip');
    	 
        $this->addColumn('jobqueue_id', array(
            'header'    => Mage::helper('auit_publicationbasic')->__('ID'),
            'width'     => '50px',
            'index'     => 'jobqueue_id',
            'type'  => 'number',
        ));

      /*  
        $this->addColumn('start_at', array(
        		'header'    =>  Mage::helper('newsletter')->__('Queue Start'),
        		'type'      =>	'datetime',
        		'index'     =>	'queue_start_at',
        		'gmtoffset' => true,
        		'default'	=> 	' ---- '
        		,'width'     => '100px'
        ));
        */
        $this->addColumn('finish_at', array(
        		'header'    =>  Mage::helper('newsletter')->__('Queue Finish'),
        		'type'      => 	'datetime',
        		'index'     =>	'queue_finish_at',
        		'gmtoffset' => true,
        		'default'	=> 	' ---- '
        		,'width'     => '100px'
        ));
        $this->addColumn('name', array(
        		'header'    => Mage::helper('auit_publicationbasic')->__('Name'),
        		'align'     => 'left',
        		'index'     => 'name'
        ));
        
        $this->addColumn('queue_status', array(
        		'header'    => Mage::helper('auit_publicationbasic')->__('Job Status'),
        		'align'     => 'left',
        		'index'     => 'queue_status'
        ));
        $this->addColumn('preview', array(
        		'header'    => Mage::helper('auit_publicationbasic')->__('Preview'),
        		'renderer'  => 'AuIt_PublicationBasic_Block_Adminhtml_Jobqueue_Preview',
        		'width'     => '110px',
        		'index'     => 'extension'
        ));
        
        // &nbsp; <small>('.$url7zip.')</small>
        $this->addColumn('extension', array(
        		'header'    => Mage::helper('backup')->__('Download'),
        		'format'    => '<a href="' . $this->getUrl('*/*/download', array('id' => '$jobqueue_id', 'finish' => '$finish_at'))
        		. '">$extension</a>',
        		'index'     => 'extension',
        		'sortable'  => false,
        		'filter'    => false
        ));
        
        /*
        $this->addColumn('type', array(
        		'header'    => Mage::helper('auit_publicationbasic')->__('Type'),
        		'index'     => 'type',
        		'type'      => 'options',
        		'options'   => Mage::helper('auit_publicationbasic')->getJobQueueTypeOptions(true),
        		'width'     => '150',
        ));
        */
        $this->addColumn('prio', array(
        		'header'    => Mage::helper('auit_publicationbasic')->__('Priority'),
        		'index'     => 'prio',
        		'type'      => 'options',
        		'options'   => Mage::helper('auit_publicationbasic')->getJobQueuePriorityOptions(true), 
        		'width'     => '150',
        ));
        
        $this->addColumn('update_time', array(
        		'header'    => Mage::helper('auit_publicationbasic')->__('Last Update'),
        		'width'     => '150px',
        		'type'      => 'datetime',
        		'index'     => 'update_time'
        ));
        $this->addColumn('update_from', array(
        		'header'    => Mage::helper('auit_publicationbasic')->__('From'),
        		'align'     => 'left',
        		'width'     => '150px',
        		'index'     => 'update_from'
        ));
        $this->addColumn('status', array(
        		'header'    => Mage::helper('auit_publicationbasic')->__('Status'),
        		'index'     => 'status',
        		'type'      => 'options',
        		'options'   => Mage::helper('auit_publicationbasic')->getJobQueueStatusOptions(true),
        		'width'     => '150'
        ));
        return parent::_prepareColumns();
    }
    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('jobqueue_id' => $row->getId()));
    }
    protected function _prepareMassaction()
    {
    	$this->setMassactionIdField('jobqueue_id');
    	$this->getMassactionBlock()->setFormFieldName('jobqueue_ids');
    	$this->getMassactionBlock()->addItem('action_reset', array(
    			'label'    => Mage::helper('customer')->__('Rerun Job'),
    			'url'      => $this->getUrl('*/*/massReset'),
    			'confirm' => Mage::helper('catalog')->__('Are you sure?')
    	));
    	
    	$this->getMassactionBlock()->addItem('action_delete', array(
    			'label'    => Mage::helper('customer')->__('Delete'),
    			'url'      => $this->getUrl('*/*/massDelete'),
    			'confirm' => Mage::helper('catalog')->__('Are you sure?')
    	));
    	$this->getMassactionBlock()->addItem('action_cancel', array(
    			'label'    => Mage::helper('customer')->__('Cancel'),
    			'url'      => $this->getUrl('*/*/massCancel'),
    			'confirm' => Mage::helper('catalog')->__('Are you sure?')
    	));
    	$this->getMassactionBlock()->addItem('action_start', array(
    			'label'    => Mage::helper('customer')->__('Start'),
    			'url'      => $this->getUrl('*/*/massStart'),
    			//'confirm' => Mage::helper('catalog')->__('Are you sure?')
    	));
    	$this->getMassactionBlock()->addItem('action_hold', array(
    			'label'    => Mage::helper('customer')->__('Hold'),
    			'url'      => $this->getUrl('*/*/massHold'),
    			'confirm' => Mage::helper('catalog')->__('Are you sure?')
    	));
    	return $this;
    }

}
