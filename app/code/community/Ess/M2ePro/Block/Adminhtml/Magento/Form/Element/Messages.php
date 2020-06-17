<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Magento_Form_Element_Messages extends Varien_Data_Form_Element_Abstract
{
    //########################################

    public function __construct($attributes = array())
    {
        parent::__construct($attributes);
        $this->setType('hidden');
    }

    //########################################

    public function getElementHtml()
    {
        $messages = $this->getData('messages');

        if (empty($messages)) {
            return '';
        }

        /** @var Mage_Adminhtml_Block_Messages $block */
        $block = Mage::app()->getLayout()->createBlock('adminhtml/messages');

        foreach ($messages as $message) {
            switch ($message['type']) {
                case Mage_Core_Model_Message::ERROR:
                    $block->addError($message['content']);
                    break;
                case Mage_Core_Model_Message::NOTICE:
                    $block->addNotice($message['content']);
                    break;
                case Mage_Core_Model_Message::SUCCESS:
                    $block->addSuccess($message['content']);
                    break;
                case Mage_Core_Model_Message::WARNING:
                    $block->addWarning($message['content']);
                    break;
            }
        }

        return <<<HTML
<div id="{$this->getHtmlId()}" style="{$this->getStyle()}" class="{$this->getClass()}">
    {$block->toHtml()}
</div>
HTML;
    }

    //########################################
}
