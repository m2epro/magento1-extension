<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

use Ess_M2ePro_Helper_Component_Ebay_Category as EbayCategory;

class Ess_M2ePro_Model_Cron_Task_Ebay_Template_RemoveUnused extends Ess_M2ePro_Model_Cron_Task_Abstract
{
    const NICK = 'ebay/template/remove_unused';

    /**
     * @var int (in seconds)
     */
    protected $_interval = 3600;

    const SAFE_CREATE_DATE_INTERVAL = 86400;

    //########################################

    protected function performActions()
    {
        return true;

        $this->removeUnusedTemplates(Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_SYNCHRONIZATION);
        $this->removeUnusedTemplates(Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_SELLING_FORMAT);
        $this->removeUnusedTemplates(Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_DESCRIPTION);
        $this->removeUnusedTemplates(Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_PAYMENT);
        $this->removeUnusedTemplates(Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_SHIPPING);
        $this->removeUnusedTemplates(Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_RETURN_POLICY);

        $this->removeCategoriesTemplates();
        $this->removeStoreCategoriesTemplates();
    }

    //########################################

    protected function removeUnusedTemplates($templateNick)
    {
        $this->getOperationHistory()->addTimePoint(
            __METHOD__.$templateNick,
            'Remove Unused "'.$templateNick.'" Policies'
        );

        /** @var Ess_M2ePro_Model_Ebay_Template_Manager $templateManager */
        $templateManager = Mage::getModel('M2ePro/Ebay_Template_Manager')->setTemplate($templateNick);

        /** @var $connRead Varien_Db_Adapter_Pdo_Mysql */
        $connRead = Mage::getSingleton('core/resource')->getConnection('core_read');

        $unionSelectListingTemplate = $connRead->select()
            ->from(
                Mage::getResourceModel('M2ePro/Ebay_Listing')->getMainTable(),
                array('result_field' => $templateManager->getTemplateIdColumnName())
            )
            ->where($templateManager->getTemplateIdColumnName().' IS NOT NULL');

        $unionSelectListingProductTemplate = $connRead->select()
            ->from(
                Mage::getResourceModel('M2ePro/Ebay_Listing_Product')->getMainTable(),
                array('result_field' => $templateManager->getTemplateIdColumnName())
            )
            ->where($templateManager->getTemplateIdColumnName().' IS NOT NULL');

        $unionSelect = $connRead->select()->union(
            array(
                $unionSelectListingTemplate,
                $unionSelectListingProductTemplate
            )
        );

        $minCreateDate = Mage::helper('M2ePro')->getCurrentGmtDate(true) - self::SAFE_CREATE_DATE_INTERVAL;
        $minCreateDate = Mage::helper('M2ePro')->getDate($minCreateDate);

        $collection = $templateManager->getTemplateCollection();
        $collection->getSelect()->where('`id` NOT IN ('.$unionSelect->__toString().')');
        $collection->getSelect()->where('`is_custom_template` = 1');
        $collection->getSelect()->where('`create_date` < ?', $minCreateDate);

        $unusedTemplates = $collection->getItems();
        foreach ($unusedTemplates as $unusedTemplate) {
            $unusedTemplate->deleteInstance();
        }

        $this->getOperationHistory()->saveTimePoint(__METHOD__.$templateNick);
    }

    // ---------------------------------------

    protected function removeCategoriesTemplates()
    {
        $this->getOperationHistory()->addTimePoint(__METHOD__, 'Remove Unused "Category" Policies');

        /** @var $connRead Varien_Db_Adapter_Pdo_Mysql */
        $connRead = Mage::getSingleton('core/resource')->getConnection('core_read');

        $listingAutoGlobal = $connRead->select()
            ->from(
                Mage::getResourceModel('M2ePro/Ebay_Listing')->getMainTable(),
                array(
                    'result_field' => new Zend_Db_Expr(
                        'IF (
                            auto_global_adding_template_category_id,
                            auto_global_adding_template_category_id,
                            auto_global_adding_template_category_secondary_id
                        )'
                    )
                )
            )
            ->where('auto_global_adding_template_category_id IS NOT NULL')
            ->orWhere('auto_global_adding_template_category_secondary_id IS NOT NULL');

        $listingAutoWebsite = $connRead->select()
            ->from(
                Mage::getResourceModel('M2ePro/Ebay_Listing')->getMainTable(),
                array(
                    'result_field' => new Zend_Db_Expr(
                        'IF (
                            auto_website_adding_template_category_id,
                            auto_website_adding_template_category_id,
                            auto_website_adding_template_category_secondary_id
                        )'
                    )
                )
            )
            ->where('auto_website_adding_template_category_id IS NOT NULL')
            ->orWhere('auto_website_adding_template_category_secondary_id IS NOT NULL');

        $listingAutoCategory = $connRead->select()
            ->from(
                Mage::getResourceModel('M2ePro/Ebay_Listing_Auto_Category_Group')->getMainTable(),
                array(
                    'result_field' => new Zend_Db_Expr(
                        'IF (
                            adding_template_category_id,
                            adding_template_category_id,
                            adding_template_category_secondary_id
                        )'
                    )
                )
            )
            ->where('adding_template_category_id IS NOT NULL')
            ->orWhere('adding_template_category_secondary_id IS NOT NULL');

        $listingProduct = $connRead->select()
            ->from(
                Mage::getResourceModel('M2ePro/Ebay_Listing_Product')->getMainTable(),
                array(
                    'result_field' => new Zend_Db_Expr(
                        'IF (
                            template_category_id,
                            template_category_id,
                            template_category_secondary_id
                        )'
                    )
                )
            )
            ->where('template_category_id IS NOT NULL')
            ->orWhere('template_category_secondary_id IS NOT NULL');

        $unionSelect = $connRead->select()->union(
            array(
                $listingAutoGlobal,
                $listingAutoWebsite,
                $listingAutoCategory,
                $listingProduct
            )
        );

        $minCreateDate = Mage::helper('M2ePro')->getCurrentGmtDate(true) - self::SAFE_CREATE_DATE_INTERVAL;
        $minCreateDate = Mage::helper('M2ePro')->getDate($minCreateDate);

        $collection = Mage::getModel('M2ePro/Ebay_Template_Category')->getCollection();
        $collection->getSelect()->where('id NOT IN ('.$unionSelect->__toString().')');
        $collection->getSelect()->where('is_custom_template = 1');
        $collection->getSelect()->where('create_date < ?', $minCreateDate);

        $rememberTemplateIds = array();

        $listingCollection = Mage::helper('M2ePro/Component_Ebay')->getCollection('Listing');
        foreach ($listingCollection->getItems() as $listing) {
            $additionalData = $listing->getSettings('additional_data');
            if (!isset($additionalData['mode_same_category_data'])) {
                continue;
            }

            $sameCategoryData = $additionalData['mode_same_category_data'];

            if (!empty($sameCategoryData[EbayCategory::TYPE_EBAY_MAIN])) {
                $rememberTemplateIds[] = $sameCategoryData[EbayCategory::TYPE_EBAY_MAIN]['template_id'];
            }

            if (!empty($sameCategoryData[EbayCategory::TYPE_EBAY_SECONDARY])) {
                $rememberTemplateIds[] = $sameCategoryData[EbayCategory::TYPE_EBAY_SECONDARY]['template_id'];
            }
        }

        if (!empty($rememberTemplateIds)) {
            $collection->getSelect()->where('id NOT IN (' . implode(',', $rememberTemplateIds) . ')');
        }

        $unusedTemplates = $collection->getItems();
        foreach ($unusedTemplates as $unusedTemplate) {
            /**@var Ess_M2ePro_Model_Ebay_Template_Category $unusedTemplate */
            $unusedTemplate->deleteInstance();
        }

        $this->getOperationHistory()->saveTimePoint(__METHOD__);
    }

    protected function removeStoreCategoriesTemplates()
    {
        $this->getOperationHistory()->addTimePoint(__METHOD__, 'Remove Unused "Store Category" Policies');

        /** @var $connRead Varien_Db_Adapter_Pdo_Mysql */
        $connRead = Mage::getSingleton('core/resource')->getConnection('core_read');

        $listingAutoGlobal = $connRead->select()
            ->from(
                Mage::getResourceModel('M2ePro/Ebay_Listing')->getMainTable(),
                array(
                    'result_field' => new Zend_Db_Expr(
                        'IF (
                            auto_global_adding_template_store_category_id,
                            auto_global_adding_template_store_category_id,
                            auto_global_adding_template_store_category_secondary_id
                        )'
                    )
                )
            )
            ->where('auto_global_adding_template_store_category_id IS NOT NULL')
            ->orWhere('auto_global_adding_template_store_category_secondary_id IS NOT NULL');

        $listingAutoWebsite = $connRead->select()
            ->from(
                Mage::getResourceModel('M2ePro/Ebay_Listing')->getMainTable(),
                array(
                    'result_field' => new Zend_Db_Expr(
                        'IF (
                            auto_website_adding_template_store_category_id,
                            auto_website_adding_template_store_category_id,
                            auto_website_adding_template_store_category_secondary_id
                        )'
                    )
                )
            )
            ->where('auto_website_adding_template_store_category_id IS NOT NULL')
            ->orWhere('auto_website_adding_template_store_category_secondary_id IS NOT NULL');

        $listingAutoCategory = $connRead->select()
            ->from(
                Mage::getResourceModel('M2ePro/Ebay_Listing_Auto_Category_Group')->getMainTable(),
                array(
                    'result_field' => new Zend_Db_Expr(
                        'IF (
                            adding_template_store_category_id,
                            adding_template_store_category_id,
                            adding_template_store_category_secondary_id
                        )'
                    )
                )
            )
            ->where('adding_template_store_category_id IS NOT NULL')
            ->orWhere('adding_template_store_category_secondary_id IS NOT NULL');

        $listingProduct = $connRead->select()
            ->from(
                Mage::getResourceModel('M2ePro/Ebay_Listing_Product')->getMainTable(),
                array(
                    'result_field' => new Zend_Db_Expr(
                        'IF (
                            template_store_category_id,
                            template_store_category_id,
                            template_store_category_secondary_id
                        )'
                    )
                )
            )
            ->where('template_store_category_id IS NOT NULL')
            ->orWhere('template_store_category_secondary_id IS NOT NULL');

        $unionSelect = $connRead->select()->union(
            array(
                $listingAutoGlobal,
                $listingAutoWebsite,
                $listingAutoCategory,
                $listingProduct
            )
        );

        $minCreateDate = Mage::helper('M2ePro')->getCurrentGmtDate(true) - self::SAFE_CREATE_DATE_INTERVAL;
        $minCreateDate = Mage::helper('M2ePro')->getDate($minCreateDate);

        $collection = Mage::getModel('M2ePro/Ebay_Template_StoreCategory')->getCollection();
        $collection->getSelect()->where('id NOT IN ('.$unionSelect->__toString().')');
        $collection->getSelect()->where('create_date < ?', $minCreateDate);

        $rememberTemplateIds = array();

        $listingCollection = Mage::helper('M2ePro/Component_Ebay')->getCollection('Listing');
        foreach ($listingCollection->getItems() as $listing) {
            $additionalData = $listing->getSettings('additional_data');
            if (!isset($additionalData['mode_same_category_data'])) {
                continue;
            }

            $sameCategoryData = $additionalData['mode_same_category_data'];

            if (!empty($sameCategoryData[EbayCategory::TYPE_STORE_MAIN])) {
                $rememberTemplateIds[] = $sameCategoryData[EbayCategory::TYPE_STORE_MAIN]['template_id'];
            }

            if (!empty($sameCategoryData[EbayCategory::TYPE_STORE_SECONDARY])) {
                $rememberTemplateIds[] = $sameCategoryData[EbayCategory::TYPE_STORE_SECONDARY]['template_id'];
            }
        }

        if (!empty($rememberTemplateIds)) {
            $collection->getSelect()->where('id NOT IN (' . implode(',', $rememberTemplateIds) . ')');
        }

        $unusedTemplates = $collection->getItems();
        foreach ($unusedTemplates as $unusedTemplate) {
            /**@var Ess_M2ePro_Model_Ebay_Template_StoreCategory $unusedTemplate */
            $unusedTemplate->deleteInstance();
        }

        $this->getOperationHistory()->saveTimePoint(__METHOD__);
    }

    //########################################
}
