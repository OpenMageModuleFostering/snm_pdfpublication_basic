<?php
class AuIt_PublicationBasic_Block_Adminhtml_Versioninfo
    extends Mage_Adminhtml_Block_Abstract
    implements Varien_Data_Form_Element_Renderer_Interface
{
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $info = '<fieldset class="config">'.
        	Mage::helper('auit_publicationbasic')->__('pdfPUBLICATION Basic version: %s', Mage::getConfig()->getNode('modules/AuIt_PublicationBasic/version')).
//            '&#160;&#160;'.Mage::helper('auit_publicationbasic')->__('<a target="_blank" href="%s">Documentation english</a>', 'http://www.snm-portal.com/media/content/images/pdf/pdfPRINT_en.pdf').
  //      	'&#160;&#160;'.Mage::helper('auit_publicationbasic')->__('<a target="_blank" href="%s">Dokumentation deutsch</a>', 'http://www.snm-portal.com/media/content/images/pdf/pdfPRINT_de.pdf').
            '&#160;&#160;'.Mage::helper('auit_publicationbasic')->__('<a target="_blank" href="%s">More Infos - FAQ</a>', 'http://snm-portal.com/magento-pdf-publication-templates.html').
        	'</fieldset>';
        return $info;
    }
}
