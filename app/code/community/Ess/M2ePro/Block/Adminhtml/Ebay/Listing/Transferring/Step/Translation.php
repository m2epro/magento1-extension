<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Listing_Transferring_Step_Translation extends Mage_Adminhtml_Block_Widget
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('ebayListingTransferringStepTranslation');
        // ---------------------------------------

        $this->setTemplate('M2ePro/ebay/listing/transferring/step/translation.phtml');
    }

    //########################################

    public function isAllowedStep()
    {
        return (bool)$this->getData('is_allowed');
    }

    //########################################

    protected function _beforeToHtml()
    {
        parent::_beforeToHtml();

        // ---------------------------------------
        $data = array(
            'id'      => 'back_button_translation',
            'class'   => 'back back_button',
            'label'   => Mage::helper('M2ePro')->__('Back'),
            'onclick' => 'EbayListingTransferringHandlerObj.back();',
        );
        $buttonBlock = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);
        $this->setChild('back_button', $buttonBlock);
        // ---------------------------------------

        // ---------------------------------------
        $data = array(
            'id'      => 'continue_button_translation',
            'class'   => 'next continue_button',
            'label'   => Mage::helper('M2ePro')->__('Continue'),
        );
        $buttonBlock = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);
        $this->setChild('continue_button', $buttonBlock);
        // ---------------------------------------

        // ---------------------------------------
        $data = array(
            'id'      => 'confirm_button_translation',
            'class'   => 'confirm_button',
            'label'   => Mage::helper('M2ePro')->__('Confirm'),
            'onclick' => 'EbayListingTransferringHandlerObj.confirm();',
            'style'   => 'display: none;'
        );
        $buttonBlock = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);
        $this->setChild('confirm_button', $buttonBlock);
        // ---------------------------------------

        // ---------------------------------------
        $data = array(
            'id'      => 'create_account_button_translation',
            'class'   => 'confirm_button',
            'label'   => Mage::helper('M2ePro')->__('Create Account'),
            'onclick' => 'EbayListingTransferringHandlerObj.createTranslationAccount();'
        );
        $buttonBlock = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);
        $this->setChild('create_account_button', $buttonBlock);
        // ---------------------------------------

        // ---------------------------------------
        $defaultStoreId = Mage::helper('M2ePro/Magento_Store')->getDefaultStoreId();

        $countries = Mage::getModel('Adminhtml/System_Config_Source_Country')->toOptionArray();
        $this->setData('countries', $countries);
        $this->setData('country', Mage::getStoreConfig('general/country/default', $defaultStoreId));
        // ---------------------------------------

        // ---------------------------------------

        $accountId = (int)$this->getData('account_id');
        if ($accountId) {
            $info = array();

            $account = Mage::helper('M2ePro/Component_Ebay')
                ->getCollection('Account')
                ->addFieldToFilter('account_id', $accountId)
                ->getLastItem();

            if ($account) {
                $ebayInfo = json_decode($account->getEbayInfo(), true);
                $ebayInfo['Email']  && $info['email']        = $ebayInfo['Email'];
                $ebayInfo['UserID'] && $info['ebay_user_id'] = $ebayInfo['UserID'];

                $info['translation_hash'] = (bool)$account->getTranslationHash() ? '1' : '0';

                $translationInfo = json_decode($account->getTranslationInfo(), true);
                isset($translationInfo['currency']) && $info['translation_currency'] = $translationInfo['currency'];
                isset($translationInfo['credit']['prepaid']) &&
                    $info['translation_balance'] = $translationInfo['credit']['prepaid'];
                isset($translationInfo['credit']['translation']) && isset($translationInfo['credit']['used']) &&
                    $info['translation_total_credits'] =
                    $translationInfo['credit']['translation']- $translationInfo['credit']['used'];
            }

            $userId = Mage::getSingleton('admin/session')->getUser()->getId();
            $user = Mage::getModel('admin/user')->load($userId)->getData();

            $info['firstname'] = $user['firstname'];
            $info['lastname'] = $user['lastname'];

            $this->addData($info);
        }

        // ---------------------------------------
    }

    //########################################

    public function getCountryLabelByCode($code)
    {
        $countryLabel = '';

        foreach (Mage::getModel('Adminhtml/System_Config_Source_Country')->toOptionArray() as $country) {
            if ($country['value'] == $code) {
                $countryLabel = $country['label'];
                break;
            }
        }

        return $countryLabel;
    }

    //########################################

    public function getTranslationServices()
    {
        $translationServices = Mage::helper('M2ePro/Component_Ebay')->getTranslationServices();
        $config = Mage::helper('M2ePro/Module')->getConfig();

        foreach ($translationServices as $name => $title) {
            $avgCost = $config->getGroupValue("/ebay/translation_services/{$name}/", 'avg_cost');

            $translationServices[$name] = array(
                'name'       => $name,
                'title'    => $title,
                'avg_cost' => !is_null($avgCost) ? $avgCost : '0.00'
            );
        }

        return $translationServices;
    }

    //########################################
}