<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Listing_Template_Switcher_DataLoader
{
    //########################################

    public function load($source, array $params = array())
    {
        $data = NULL;

        if ($source instanceof Ess_M2ePro_Helper_Data_Session) {
            $data = $this->getDataFromSession($source, $params);
        }
        if ($source instanceof Ess_M2ePro_Model_Listing) {
            $data = $this->getDataFromListing($source, $params);
        }
        if ($source instanceof Ess_M2ePro_Model_Mysql4_Listing_Product_Collection) {
            $data = $this->getDataFromListingProducts($source, $params);
        }
        if ($this->isTemplateInstance($source)) {
            $data = $this->getDataFromTemplate($source, $params);
        }
        if ($source instanceof Mage_Core_Controller_Request_Http) {
            $data = $this->getDataFromRequest($source, $params);
        }

        if (is_null($data)) {
            throw new InvalidArgumentException('Data source is invalid.');
        }

        // ---------------------------------------
        $account = NULL;
        if ($data['account_id']) {
            $account = Mage::helper('M2ePro/Component_Ebay')->getCachedObject('Account', $data['account_id']);
        }

        $marketplace = NULL;
        if ($data['marketplace_id']) {
            $marketplace = Mage::helper('M2ePro/Component_Ebay')->getCachedObject('Marketplace',
                                                                                  $data['marketplace_id']);
        }

        $storeId = (int)$data['store_id'];

        $attributeSets = $data['attribute_sets'];
        $attributes = Mage::helper('M2ePro/Magento_Attribute')->getGeneralFromAttributeSets($attributeSets);

        $displayUseDefaultOption = $data['display_use_default_option'];
        // ---------------------------------------

        $global = Mage::helper('M2ePro/Data_Global');

        // ---------------------------------------
        $global->setValue('ebay_account', $account);
        $global->setValue('ebay_marketplace', $marketplace);
        $global->setValue('ebay_store', Mage::app()->getStore($storeId));
        $global->setValue('ebay_attribute_sets', $attributeSets);
        $global->setValue('ebay_attributes', $attributes);
        $global->setValue('ebay_display_use_default_option', $displayUseDefaultOption);
        // ---------------------------------------

        foreach ($data['templates'] as $nick => $templateData) {
            $template = $manager = Mage::getSingleton('M2ePro/Ebay_Template_Manager')
                ->setTemplate($nick)
                ->getTemplateModel();

            if ($templateData['id']) {
                $template->load($templateData['id']);
            }

            $global->setValue("ebay_template_{$nick}", $template);
            $global->setValue("ebay_template_mode_{$nick}", $templateData['mode']);
            $global->setValue("ebay_template_force_parent_{$nick}", $templateData['force_parent']);
        }
    }

    //########################################

    private function getDataFromSession(Ess_M2ePro_Helper_Data_Session $source, array $params = array())
    {
        // ---------------------------------------
        if (!isset($params['session_key'])) {
            throw new Ess_M2ePro_Model_Exception_Logic('Session key is not defined.');
        }
        $sessionKey = $params['session_key'];
        $sessionData = $source->getValue($sessionKey);
        // ---------------------------------------

        // ---------------------------------------
        $accountId = isset($sessionData['account_id']) ? $sessionData['account_id'] : NULL;
        $marketplaceId = isset($sessionData['marketplace_id']) ? $sessionData['marketplace_id'] : NULL;
        $storeId = isset($sessionData['store_id']) ? $sessionData['store_id'] : NULL;
        $attributeSets = Mage::helper('M2ePro/Magento_AttributeSet')
            ->getAll(Ess_M2ePro_Helper_Magento_Abstract::RETURN_TYPE_IDS);
        // ---------------------------------------

        // ---------------------------------------
        $templates = array();

        foreach (Mage::getSingleton('M2ePro/Ebay_Template_Manager')->getAllTemplates() as $nick) {
            $templateId = isset($sessionData["template_id_{$nick}"]) ? $sessionData["template_id_{$nick}"] : NULL;
            $templateMode = isset($sessionData["template_id_{$nick}"]) ? $sessionData["template_mode_{$nick}"] : NULL;

            if (empty($templateMode)) {
                $templateMode = Ess_M2ePro_Model_Ebay_Template_Manager::MODE_CUSTOM;
            }

            $templates[$nick] = array(
                'id' => $templateId,
                'mode' => $templateMode,
                'force_parent' => false
            );
        }
        // ---------------------------------------

        return array(
            'account_id'                 => $accountId,
            'marketplace_id'             => $marketplaceId,
            'store_id'                   => $storeId,
            'attribute_sets'             => $attributeSets,
            'display_use_default_option' => false,
            'templates'                  => $templates
        );
    }

    //########################################

    private function getDataFromListing(Ess_M2ePro_Model_Listing $source, array $params = array())
    {
        // ---------------------------------------
        $accountId = $source->getAccountId();
        $marketplaceId = $source->getMarketplaceId();
        $storeId = $source->getStoreId();
        $attributeSets = Mage::helper('M2ePro/Magento_AttributeSet')
            ->getAll(Ess_M2ePro_Helper_Magento_Abstract::RETURN_TYPE_IDS);
        // ---------------------------------------

        // ---------------------------------------
        $templates = array();

        foreach (Mage::getSingleton('M2ePro/Ebay_Template_Manager')->getAllTemplates() as $nick) {
            $manager = Mage::getModel('M2ePro/Ebay_Template_Manager')
                ->setTemplate($nick)
                ->setOwnerObject($source->getChildObject());

            $templateId = $manager->getIdColumnValue();
            $templateMode = $manager->getModeValue();

            $templates[$nick] = array(
                'id' => $templateId,
                'mode' => $templateMode,
                'force_parent' => false
            );
        }
        // ---------------------------------------

        return array(
            'account_id'                 => $accountId,
            'marketplace_id'             => $marketplaceId,
            'store_id'                   => $storeId,
            'attribute_sets'             => $attributeSets,
            'display_use_default_option' => false,
            'templates'                  => $templates
        );
    }

    //########################################

    private function getDataFromListingProducts($source, array $params = array())
    {
        // ---------------------------------------
        /** @var Ess_M2ePro_Model_Listing_Product $listingProductFirst */
        $listingProductFirst = $source->getFirstItem();
        // ---------------------------------------

        // ---------------------------------------
        $productIds = array();
        foreach ($source as $listingProduct) {
            $productIds[] = $listingProduct->getData('product_id');
        }
        // ---------------------------------------

        // ---------------------------------------
        $accountId = $listingProductFirst->getListing()->getAccountId();
        $marketplaceId = $listingProductFirst->getListing()->getMarketplaceId();
        $storeId = $listingProductFirst->getListing()->getStoreId();
        $attributeSets = Mage::helper('M2ePro/Magento_AttributeSet')
            ->getFromProducts($productIds, Ess_M2ePro_Helper_Magento_Abstract::RETURN_TYPE_IDS);
        // ---------------------------------------

        // ---------------------------------------
        $templates = array();

        foreach (Mage::getSingleton('M2ePro/Ebay_Template_Manager')->getAllTemplates() as $nick) {
            $templateId = NULL;
            $templateMode = NULL;
            $forceParent = false;

            if ($source->count() <= 200) {
                foreach ($source as $listingProduct) {
                    $manager = Mage::getModel('M2ePro/Ebay_Template_Manager')
                        ->setTemplate($nick)
                        ->setOwnerObject($listingProduct->getChildObject());

                    $currentProductTemplateId = $manager->getIdColumnValue();
                    $currentProductTemplateMode = $manager->getModeValue();

                    if (is_null($templateId) && is_null($templateMode)) {
                        $templateId = $currentProductTemplateId;
                        $templateMode = $currentProductTemplateMode;
                        continue;
                    }

                    if ($templateId != $currentProductTemplateId || $templateMode != $currentProductTemplateMode) {
                        $templateId = NULL;
                        $templateMode = Ess_M2ePro_Model_Ebay_Template_Manager::MODE_PARENT;
                        $forceParent = true;
                        break;
                    }
                }
            } else {
                $forceParent = true;
            }

            if (is_null($templateMode)) {
                $templateMode = Ess_M2ePro_Model_Ebay_Template_Manager::MODE_PARENT;
            }

            $templates[$nick] = array(
                'id' => $templateId,
                'mode' => $templateMode,
                'force_parent' => $forceParent
            );
        }
        // ---------------------------------------

        return array(
            'account_id'                 => $accountId,
            'marketplace_id'             => $marketplaceId,
            'store_id'                   => $storeId,
            'attribute_sets'             => $attributeSets,
            'display_use_default_option' => true,
            'templates'                  => $templates
        );
    }

    //########################################

    private function isTemplateInstance($source)
    {
        if ($source instanceof Ess_M2ePro_Model_Ebay_Template_Payment
            || $source instanceof Ess_M2ePro_Model_Ebay_Template_Shipping
            || $source instanceof Ess_M2ePro_Model_Ebay_Template_Return
            || $source instanceof Ess_M2ePro_Model_Template_SellingFormat
            || $source instanceof Ess_M2ePro_Model_Template_Description
            || $source instanceof Ess_M2ePro_Model_Template_Synchronization
        ) {
            return true;
        }

        return false;
    }

    private function isHorizontalTemplate($source)
    {
        if ($source instanceof Ess_M2ePro_Model_Template_SellingFormat ||
            $source instanceof Ess_M2ePro_Model_Template_Synchronization ||
            $source instanceof Ess_M2ePro_Model_Template_Description) {

            return true;
        }

        return false;
    }

    // ---------------------------------------

    private function getTemplateNick($source)
    {
        if (!$this->isHorizontalTemplate($source)) {
            return $source->getNick();
        }

        $nick = null;

        if ($source instanceof Ess_M2ePro_Model_Template_SellingFormat) {
            $nick = Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_SELLING_FORMAT;
        } elseif ($source instanceof Ess_M2ePro_Model_Template_Synchronization) {
            $nick = Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_SYNCHRONIZATION;
        } elseif ($source instanceof Ess_M2ePro_Model_Template_Description) {
            $nick = Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_DESCRIPTION;
        }

        return $nick;
    }

    //########################################

    private function getDataFromTemplate($source, array $params = array())
    {
        $attributeSets = Mage::helper('M2ePro/Magento_AttributeSet')
            ->getAll(Ess_M2ePro_Helper_Magento_Abstract::RETURN_TYPE_IDS);

        $marketplaceId = NULL;
        if (isset($params['marketplace_id'])) {
            $marketplaceId = (int)$params['marketplace_id'];
        }

        $nick = $this->getTemplateNick($source);

        return array(
            'account_id'                 => NULL,
            'marketplace_id'             => $marketplaceId,
            'store_id'                   => Mage_Core_Model_App::ADMIN_STORE_ID,
            'attribute_sets'             => $attributeSets,
            'display_use_default_option' => true,
            'templates'                  => array(
                $nick => array(
                    'id' => $source->getId(),
                    'mode' => Ess_M2ePro_Model_Ebay_Template_Manager::MODE_TEMPLATE,
                    'force_parent' => false
                )
            )
        );
    }

    //########################################

    private function getDataFromRequest(Mage_Core_Controller_Request_Http $source, array $params = array())
    {
        $id   = $source->getParam('id');
        $nick = $source->getParam('nick');
        $mode = $source->getParam('mode', Ess_M2ePro_Model_Ebay_Template_Manager::MODE_CUSTOM);

        $attributeSets = $source->getParam('attribute_sets', '');
        $attributeSets = explode(',', $attributeSets);

        return array(
            'account_id'                 => $source->getParam('account_id'),
            'marketplace_id'             => $source->getParam('marketplace_id'),
            'store_id'                   => Mage_Core_Model_App::ADMIN_STORE_ID,
            'attribute_sets'             => $attributeSets,
            'display_use_default_option' => (bool)$source->getParam('display_use_default_option'),
            'templates'                  => array(
                $nick => array(
                    'id' => $id,
                    'mode' => $mode,
                    'force_parent' => false
                )
            )
        );
    }

    //########################################
}