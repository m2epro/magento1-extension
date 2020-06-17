<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Template_Messages extends Mage_Adminhtml_Block_Widget
{
    protected $_template = 'M2ePro/template/messages.phtml';

    protected $_templateNick;
    protected $_componentMode;

    //########################################

    public function getResultBlock($templateNick, $componentMode)
    {
        $block = $this;

        if ($templateNick == Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_SHIPPING &&
            $componentMode == Ess_M2ePro_Helper_Component_Ebay::NICK
        ) {
            $block = $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_template_shipping_messages');
        }

        if ($templateNick == Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_SELLING_FORMAT) {
            if ($componentMode == Ess_M2ePro_Helper_Component_Ebay::NICK) {
                $block = $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_template_sellingFormat_messages');
            } else {
                $block = $this->getLayout()->createBlock('M2ePro/adminhtml_template_sellingFormat_messages');
            }
        }

        $block->setComponentMode($componentMode);
        $block->setTemplateNick($templateNick);

        return $block;
    }

    //########################################

    public function getMessages()
    {
        return array();
    }

    //########################################

    public function getMessagesHtml(array $messages = array())
    {
        if (empty($messages)) {
            $messages = $this->getMessages();
        }

        if (empty($messages)) {
            return '';
        }

        $this->setData('items', $messages);

        return $this->toHtml();
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Marketplace|null
     */
    public function getMarketplace()
    {
        if (!isset($this->_data['marketplace_id'])) {
            return null;
        }

        return Mage::helper('M2ePro/Component')->getCachedComponentObject(
            $this->getComponentMode(), 'Marketplace', (int)$this->_data['marketplace_id']
        );
    }

    //########################################

    /**
     * @return Mage_Core_Model_Store|null
     */
    public function getStore()
    {
        if (!isset($this->_data['store_id'])) {
            return null;
        }

        return Mage::app()->getStore((int)$this->_data['store_id']);
    }

    //########################################

    public function setTemplateNick($templateNick)
    {
        $this->_templateNick = $templateNick;
        return $this;
    }

    public function getTemplateNick()
    {
        if ($this->_templateNick === null) {
            throw new Ess_M2ePro_Model_Exception_Logic('Policy nick is not set.');
        }

        return $this->_templateNick;
    }

    //########################################

    public function setComponentMode($componentMode)
    {
        $this->_componentMode = $componentMode;
        return $this;
    }

    public function getComponentMode()
    {
        if ($this->_componentMode === null) {
            throw new Ess_M2ePro_Model_Exception_Logic('Component Mode is not set.');
        }

        return $this->_componentMode;
    }

    //########################################

    protected function getTemplateData()
    {
        if (empty($this->_data['template_data']) || !is_array($this->_data['template_data'])) {
            throw new Ess_M2ePro_Model_Exception_Logic('Policy data is not set.');
        }

        return $this->_data['template_data'];
    }

    //########################################
}
