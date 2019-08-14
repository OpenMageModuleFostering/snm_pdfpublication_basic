<?php
class AuIt_PublicationBasic_Block_Adminhtml_Projects_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('publicationTextGrid');
        $this->setDefaultSort('text_identifier');
        $this->setDefaultDir('ASC');
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('auit_publicationbasic/project')->getCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('project_id', array(
            'header'    => Mage::helper('auit_publicationbasic')->__('ID'),
            'width'     => '50px',
            'index'     => 'project_id',
            'type'  => 'number',
        ));

        $this->addColumn('name', array(
            'header'    => Mage::helper('auit_publicationbasic')->__('Name'),
            'align'     => 'left',
            'index'     => 'name'
        ));
        $this->addColumn('status', array(
        		'header'    => Mage::helper('auit_publicationbasic')->__('Status'),
        		'index'     => 'status',
        		'type'      => 'options',
        		'options'   => array(
        				'1' => Mage::helper('auit_publicationbasic')->__('Enabled'),
        				'0' => Mage::helper('auit_publicationbasic')->__('Disabled'),
        		),
        		'width'     => '150',
        ));
        return parent::_prepareColumns();
    }
    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('project_id' => $row->getId()));
    }
    protected function _prepareMassaction()
    {
    	$this->setMassactionIdField('project_id');
    	$this->getMassactionBlock()->setFormFieldName('project_ids');
    	$this->getMassactionBlock()->addItem('preview', array(
    			'label'    => Mage::helper('customer')->__('Delete'),
    			'url'      => $this->getUrl('*/*/massDelete'),
    			'confirm' => Mage::helper('catalog')->__('Are you sure?')
    	));
    	return $this;
    }

}
