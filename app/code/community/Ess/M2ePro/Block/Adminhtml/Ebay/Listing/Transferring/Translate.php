<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Listing_Transferring_Translate extends Mage_Adminhtml_Block_Widget
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('ebayListingTransferringTranslate');
        // ---------------------------------------

        $this->setTemplate('M2ePro/ebay/listing/transferring/translate.phtml');
    }

    //########################################

    protected function _beforeToHtml()
    {
        parent::_beforeToHtml();

        // ---------------------------------------
        $data = array(
            'id'      => 'confirm_button_translation',
            'class'   => 'confirm_button',
            'label'   => Mage::helper('M2ePro')->__('Confirm'),
            'onclick' => 'EbayListingTransferringTranslateHandlerObj.confirm();',
        );
        $buttonBlock = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);
        $this->setChild('confirm_button', $buttonBlock);
        // ---------------------------------------

        // ---------------------------------------
        $account = $this->getAccount();

        if ($account) {
            $ebayInfo = json_decode($account->getEbayInfo(), true);
            $ebayInfo['UserID'] && $info['ebay_user_id'] = $ebayInfo['UserID'];

            $translationInfo = json_decode($account->getTranslationInfo(), true);
            isset($translationInfo['currency']) && $info['translation_currency'] = $translationInfo['currency'];
            isset($translationInfo['credit'])   && isset($translationInfo['credit']['prepaid']) &&
                $info['translation_balance'] = $translationInfo['credit']['prepaid'];
            isset($translationInfo['credit']['translation']) && isset($translationInfo['credit']['used']) &&
            $info['translation_total_credits'] =
                $translationInfo['credit']['translation']- $translationInfo['credit']['used'];
        }

        $this->addData($info);

        // ---------------------------------------
    }

    //########################################

    public function getTranslationServices()
    {
        $translationServices = Mage::helper('M2ePro/Component_Ebay')->getTranslationServices();
        $config = Mage::helper('M2ePro/Module')->getConfig();

        foreach ($translationServices as $name => $title) {
            $avgCost = $config->getGroupValue("/ebay/translation_services/{$name}/", 'avg_cost');

            $translationServices[$name] = array(
                'name'     => $name,
                'title'    => $title,
                'avg_cost' => !is_null($avgCost) ? $avgCost : '0.00'
            );
        }

        $mixedServices = $this->_getMixedServices();
        if (count($mixedServices) > 1) {
            $translationServices = array_merge(array('default_mixed' => array(
                'name'     => 'default_mixed',
                'title'    => Mage::helper('M2ePro')->__("Use current Translation Plan for each Item"),
                'avg_cost' => $this->_getMixedAvgCost($mixedServices, $translationServices),
            )), $translationServices);
        }

        return $translationServices;
    }

    public function getDefaultTranslationService()
    {
        $mixedServices = $this->_getMixedServices();

        if (count($mixedServices) == 1) {
            return key($mixedServices);
        } elseif (count($mixedServices) > 1) {
            return  'default_mixed';
        }

        return Mage::helper('M2ePro/Component_Ebay')->getDefaultTranslationService();
    }

    //########################################

    public function getAccount()
    {
        return $this->_getEbayListing()->getAccount();
    }

    //########################################

    private function _getEbayListing()
    {
        if (!$listingId = $this->getData('listing_id')) {
            throw new Ess_M2ePro_Model_Exception('Listing is not defined');
        }

        return Mage::helper('M2ePro/Component_Ebay')->getCachedObject('Listing',(int)$listingId)->getChildObject();
    }

    //########################################

    private function _getMixedServices()
    {
        $productsIds = $this->getData('products_ids');
        $productsIds = explode(',', $productsIds);
        $productsIds = array_filter($productsIds);

        $collection = Mage::helper('M2ePro/Component_Ebay')->getCollection('Listing_Product')
            ->addFieldToFilter('id', array('in' => ($productsIds)));

        $mixedServices = array();
        foreach ($collection->getItems() as $listingProduct) {
            $tempService = $listingProduct->getTranslationService();

            if (!isset($mixedServices[$tempService])) {
                $mixedServices[$tempService] = 0;
            }

            $mixedServices[$tempService]++;
        }

        return $mixedServices;
    }

    private function _getMixedAvgCost($mixedServices, $translationServices)
    {
        $totalAvgCost = 0;
        $totalProducts = 0;

        foreach ($mixedServices as $serviceName => $countProducts) {
            if (!isset($translationServices[$serviceName])) {
                continue;
            }

            $totalAvgCost += $translationServices[$serviceName]['avg_cost'];
            $totalProducts += $countProducts;
        }

        return number_format((doubleval($totalAvgCost)/$totalProducts), 2);
    }

    //########################################
}