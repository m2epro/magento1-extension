<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Amazon_Account_Edit_Tabs_Order extends Mage_Adminhtml_Block_Widget
{
    protected $_possibleMagentoStatuses;

    //########################################

    public function __construct()
    {
        parent::__construct();

        $this->setId('amazonAccountEditTabsOrder');
        $this->setTemplate('M2ePro/amazon/account/tabs/order.phtml');
    }

    protected function _beforeToHtml()
    {
        /** @var Ess_M2ePro_Model_Account $account */
        $account = Mage::helper('M2ePro/Data_Global')->getValue('model_account');

        $magentoOrdersSettings = !empty($account['magento_orders_settings'])
                                        ? Mage::helper('M2ePro')->jsonDecode($account['magento_orders_settings'])
                                        : array();

        // ---------------------------------------
        $temp = Mage::getModel('core/website')->getCollection()->setOrder('sort_order', 'ASC')->toArray();
        $this->websites = $temp['items'];
        // ---------------------------------------

        // ---------------------------------------
        $temp = Mage::getModel('customer/group')->getCollection()->toArray();
        $this->groups = $temp['items'];
        // ---------------------------------------

        // ---------------------------------------
        $selectedStore = !empty($magentoOrdersSettings['listing']['store_id'])
                                ? $magentoOrdersSettings['listing']['store_id'] : '';
        $blockStoreSwitcher = $this->getLayout()->createBlock(
            'M2ePro/adminhtml_storeSwitcher', '',
            array(
                'id'       => 'magento_orders_listings_store_id',
                'name'     => 'magento_orders_settings[listing][store_id]',
                'selected' => $selectedStore,
                'required_option' => true
            )
        );
        $blockStoreSwitcher->hasDefaultOption(false);
        $this->setChild('magento_orders_listings_store_id', $blockStoreSwitcher);
        // ---------------------------------------

        // ---------------------------------------
        $selectedStore = !empty($magentoOrdersSettings['listing_other']['store_id'])
                            ? $magentoOrdersSettings['listing_other']['store_id']
                            : Mage::helper('M2ePro/Magento_Store')->getDefaultStoreId();
        $blockStoreSwitcher = $this->getLayout()->createBlock(
            'M2ePro/adminhtml_storeSwitcher', '',
            array(
                'id'       => 'magento_orders_listings_other_store_id',
                'name'     => 'magento_orders_settings[listing_other][store_id]',
                'selected' => $selectedStore,
                'required_option' => true
            )
        );
        $blockStoreSwitcher->hasDefaultOption(false);
        $this->setChild('magento_orders_listings_other_store_id', $blockStoreSwitcher);
        // ---------------------------------------

        // ---------------------------------------
        $selectedStore = !empty($magentoOrdersSettings['fba']['store_id'])
            ? $magentoOrdersSettings['fba']['store_id'] : '';
        $blockStoreSwitcher = $this->getLayout()->createBlock(
            'M2ePro/adminhtml_storeSwitcher', '',
            array(
                'id'       => 'magento_orders_fba_store_id',
                'name'     => 'magento_orders_settings[fba][store_id]',
                'selected' => $selectedStore,
                'required_option' => true
            )
        );
        $blockStoreSwitcher->hasDefaultOption(false);
        $this->setChild('magento_orders_fba_store_id', $blockStoreSwitcher);
        // ---------------------------------------

        // ---------------------------------------
        $productTaxClasses = Mage::getModel('tax/class')->getCollection()
            ->addFieldToFilter('class_type', Mage_Tax_Model_Class::TAX_CLASS_TYPE_PRODUCT)
            ->toOptionArray();

        $none = array('value' => Ess_M2ePro_Model_Magento_Product::TAX_CLASS_ID_NONE, 'label' => 'None');
        array_unshift($productTaxClasses, $none);

        $this->productTaxClasses = $productTaxClasses;
        // ---------------------------------------

        return parent::_beforeToHtml();
    }

    public function getMagentoOrderStatusList()
    {
        if ($this->_possibleMagentoStatuses === null) {
            $this->_possibleMagentoStatuses = Mage::getSingleton('sales/order_config')->getStatuses();
        }

        return $this->_possibleMagentoStatuses;
    }

    //########################################
}
