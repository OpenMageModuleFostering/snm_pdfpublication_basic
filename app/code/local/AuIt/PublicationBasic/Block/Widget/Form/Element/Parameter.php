<?php
class AuIt_PublicationBasic_Block_Widget_Form_Element_Parameter extends Varien_Data_Form_Element_Abstract
{
    public function getElementHtml()
    {
        $content = Mage::getSingleton('core/layout')
            ->createBlock('auit_publicationbasic/widget_form_renderer_parameter');
        $content->setId($this->getHtmlId() . '_content')
            ->setElement($this);
        return $content->toHtml();
    }
}

