<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Magento_Renderer_JsTranslator
    implements Ess_M2ePro_Block_Adminhtml_Magento_Renderer_InterfaceRenderer
{
    protected $_jsTranslations = array();

    //########################################

    public function add($translation, $alias = null)
    {
        if ($alias === null) {
            $alias = $translation;
        }

        $this->_jsTranslations[$alias] =  Mage::helper('M2ePro')->__($translation);
        return $this;
    }

    public function addTranslations(array $translations)
    {
        foreach ($translations as $translationAlias => $translation) {
            is_int($translationAlias) && $translationAlias = null;
            $this->add($translation, $translationAlias);
        }

        return $this;
    }

    //########################################

    public function render()
    {
        if (empty($this->_jsTranslations)) {
            return '';
        }

        $translations = Mage::helper('M2ePro')->jsonEncode($this->_jsTranslations);
        $result = "M2ePro.translator.add({$translations});";

        $this->_jsTranslations = array();
        return $result;
    }

    //########################################
}