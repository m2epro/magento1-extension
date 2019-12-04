<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Template_Messages extends Mage_Adminhtml_Block_Widget
{
    const TYPE_ATTRIBUTES_AVAILABILITY = 'attributes_availability';

    protected $_template = 'M2ePro/template/messages.phtml';

    protected $_templateNick  = null;
    protected $_componentMode = null;

    //########################################

    public function getResultBlock($templateNick, $componentMode)
    {
        $block = $this;

        switch ($templateNick) {
            case Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_SHIPPING:

                $isPriceConvertEnabled = (int)Mage::helper('M2ePro/Module')->getConfig()->getGroupValue(
                    '/magento/attribute/', 'price_type_converting'
                );

                if ($isPriceConvertEnabled && $componentMode == Ess_M2ePro_Helper_Component_Ebay::NICK) {
                    $block = $this->getLayout()
                        ->createBlock('M2ePro/adminhtml_ebay_template_shipping_messages');
                }
                break;
            case Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_SELLING_FORMAT:
                if ($componentMode == Ess_M2ePro_Helper_Component_Ebay::NICK) {
                    $block = $this->getLayout()
                        ->createBlock('M2ePro/adminhtml_ebay_template_sellingFormat_messages');
                } else {
                    $block = $this->getLayout()
                        ->createBlock('M2ePro/adminhtml_template_sellingFormat_messages');
                }
                break;
        }

        $block->setComponentMode($componentMode);
        $block->setTemplateNick($templateNick);

        return $block;
    }

    //########################################

    public function getMessages()
    {
        $messages = array();

        // ---------------------------------------
        $message = $this->getAttributesAvailabilityMessage();
        if ($message !== null) {
            $messages[self::TYPE_ATTRIBUTES_AVAILABILITY] = $message;
        }

        // ---------------------------------------

        return $messages;
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

    public function getAttributesAvailabilityMessage()
    {
        if (!$this->canDisplayAttributesAvailabilityMessage()) {
            return null;
        }

        $productIds = Mage::getResourceModel('M2ePro/Listing_Product')
            ->getProductIds($this->getListingProductIds());
        $attributeSets = Mage::helper('M2ePro/Magento_Attribute')
            ->getSetsFromProductsWhichLacksAttributes($this->getUsedAttributes(), $productIds);

        if (empty($attributeSets)) {
            return null;
        }

        $attributeSetsNames = Mage::helper('M2ePro/Magento_AttributeSet')->getNames($attributeSets);

        return
            Mage::helper('M2ePro')->__(
                'Some Attributes which are used in this Policy were not found in Products Settings.'
                . ' Please, check if all of them are in [%set_name%] Attribute Set(s)'
                . ' as it can cause List, Revise or Relist issues.',
                implode('", "', $attributeSetsNames)
            );
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

    protected function getUsedAttributes()
    {
        return isset($this->_data['used_attributes']) ? $this->_data['used_attributes'] : array();
    }

    //########################################

    protected function getListingProductIds()
    {
        $listingProductIds = $this->getRequest()->getParam('listing_product_ids', '');
        $listingProductIds = explode(',', $listingProductIds);

        return $listingProductIds ? $listingProductIds : array();
    }

    //########################################

    protected function canDisplayAttributesAvailabilityMessage()
    {
        if (!$this->getRequest()->getParam('check_attributes_availability')) {
            return false;
        }

        if ($this->_componentMode === null || $this->_componentMode != Ess_M2ePro_Helper_Component_Ebay::NICK) {
            return false;
        }

        $listingProductIds = $this->getListingProductIds();

        if (empty($listingProductIds)) {
            return false;
        }

        return true;
    }

    //########################################
}
