<?php
class AuIt_PublicationBasic_Block_Widget_Form_Element_Satzeditor extends Varien_Data_Form_Element_Abstract
{
    public function getElementHtml()
    {
        $content = Mage::getSingleton('core/layout')
            ->createBlock('auit_publicationbasic/adminhtml_satzeditor')
            ->setElement($this)
            ->setId($this->getHtmlId() . '_content')
            ->setTemplate('auit/publicationbasic/satzeditor.phtml');
        $html = $content->toHtml();
        $content = Mage::getSingleton('core/layout')
        ->createBlock('core/template')
        ->setTemplate('auit/publicationbasic/filemanager.phtml');
        $content->setId($this->getHtmlId() . '_content2')
        ->setElement($this);
        $html .= $content->toHtml();
        return $html;
    }
}

