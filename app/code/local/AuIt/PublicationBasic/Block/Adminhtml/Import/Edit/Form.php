<?php
class AuIt_PublicationBasic_Block_Adminhtml_Import_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * Add fieldset
     *
     * @return Mage_ImportExport_Block_Adminhtml_Import_Edit_Form
     */
    protected function _prepareForm()
    {
        $helper = Mage::helper('auit_publicationbasic');
        $form = new Varien_Data_Form(array(
            'id'      => 'edit_form',
            'action'  => $this->getUrl('*/*/validate'),
            'method'  => 'post',
            'enctype' => 'multipart/form-data'
        ));
        $fieldset = $form->addFieldset('base_fieldset', array('legend' => $helper->__('Template Import Settings')));
        /*
        $fieldset->addField('behavior', 'select', array(
            'name'     => 'behavior',
            'title'    => $helper->__('Import Behavior'),
            'label'    => $helper->__('Import Behavior'),
            'required' => true,
            'values'   => Mage::getModel('importexport/source_import_behavior')->toOptionArray()
        ));
        */
        $fieldset->addField('publication_import_file', 'file', array(
            'name'     => 'publication_import_file',
            'label'    => $helper->__('Select File to Import').' (pdf-publication*.tgz)',
            'title'    => $helper->__('Select File to Import').' (pdf-publication*.tgz)',
            'required' => true
        ));
        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }
}
