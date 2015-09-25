<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

final class Ess_M2ePro_Model_Ebay_Synchronization_Defaults_RemoveUnusedTemplates
    extends Ess_M2ePro_Model_Ebay_Synchronization_Defaults_Abstract
{
    const SAFE_CREATE_DATE_INTERVAL = 86400;

    //####################################

    protected function getNick()
    {
        return '/remove_unused_templates/';
    }

    protected function getTitle()
    {
        return 'Remove Unused Policies';
    }

    // -----------------------------------

    protected function getPercentsStart()
    {
        return 10;
    }

    protected function getPercentsEnd()
    {
        return 20;
    }

    // -----------------------------------

    protected function intervalIsEnabled()
    {
        return true;
    }

    //####################################

    protected function performActions()
    {
        $this->removeUnusedTemplates(Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_SYNCHRONIZATION);
        $this->removeUnusedTemplates(Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_SELLING_FORMAT);
        $this->removeUnusedTemplates(Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_DESCRIPTION);
        $this->removeUnusedTemplates(Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_PAYMENT);
        $this->removeUnusedTemplates(Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_SHIPPING);
        $this->removeUnusedTemplates(Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_RETURN);

        $this->removeCategoriesTemplates();
        $this->removeOtherCategoriesTemplates();
    }

    //####################################

    private function removeUnusedTemplates($templateNick)
    {
        $this->getActualOperationHistory()->addTimePoint(__METHOD__.$templateNick,
                                                         'Remove Unused "'.$templateNick.'" Policies');

        /** @var Ess_M2ePro_Model_Ebay_Template_Manager $templateManager */
        $templateManager = Mage::getModel('M2ePro/Ebay_Template_Manager')->setTemplate($templateNick);

        /** @var $connRead Varien_Db_Adapter_Pdo_Mysql */
        $connRead = Mage::getSingleton('core/resource')->getConnection('core_read');

        $listingTable = Mage::getResourceModel('M2ePro/Ebay_Listing')->getMainTable();
        $listingProductTable = Mage::getResourceModel('M2ePro/Ebay_Listing_Product')->getMainTable();

        $unionSelectListingTemplate = $connRead->select()
                    ->from($listingTable,array('result_field'=>$templateManager->getTemplateIdColumnName()))
                    ->where($templateManager->getTemplateIdColumnName().' IS NOT NULL');
        $unionSelectListingCustom = $connRead->select()
                     ->from($listingTable,array('result_field'=>$templateManager->getCustomIdColumnName()))
                     ->where($templateManager->getCustomIdColumnName().' IS NOT NULL');
        $unionSelectListingProductTemplate = $connRead->select()
                     ->from($listingProductTable,array('result_field'=>$templateManager->getTemplateIdColumnName()))
                     ->where($templateManager->getTemplateIdColumnName().' IS NOT NULL');
        $unionSelectListingProductCustom = $connRead->select()
                     ->from($listingProductTable,array('result_field'=>$templateManager->getCustomIdColumnName()))
                     ->where($templateManager->getCustomIdColumnName().' IS NOT NULL');

        $unionSelect = $connRead->select()->union(array(
            $unionSelectListingTemplate,
            $unionSelectListingCustom,
            $unionSelectListingProductTemplate,
            $unionSelectListingProductCustom
        ));

        $minCreateDate = Mage::helper('M2ePro')->getCurrentGmtDate(true) - self::SAFE_CREATE_DATE_INTERVAL;
        $minCreateDate = Mage::helper('M2ePro')->getDate($minCreateDate);

        $collection = $templateManager->getTemplateCollection();
        $collection->getSelect()->where('`id` NOT IN ('.$unionSelect->__toString().')');
        $collection->getSelect()->where('`is_custom_template` = 1');
        $collection->getSelect()->where('`create_date` < ?',$minCreateDate);

        $unusedTemplates = $collection->getItems();
        foreach ($unusedTemplates as $unusedTemplate) {
            $unusedTemplate->deleteInstance();
        }

        $this->getActualOperationHistory()->saveTimePoint(__METHOD__.$templateNick);
    }

    // -----------------------------------

    private function removeCategoriesTemplates()
    {
        $this->getActualOperationHistory()->addTimePoint(__METHOD__,'Remove Unused "Category" Policies');

        /** @var $connRead Varien_Db_Adapter_Pdo_Mysql */
        $connRead = Mage::getSingleton('core/resource')->getConnection('core_read');

        $listingTable = Mage::getResourceModel('M2ePro/Ebay_Listing')->getMainTable();
        $listingProductTable = Mage::getResourceModel('M2ePro/Ebay_Listing_Product')->getMainTable();
        $listingAutoCategoryGroupTable = Mage::getResourceModel('M2ePro/Ebay_Listing_Auto_Category_Group')
                                            ->getMainTable();

        $minCreateDate = Mage::helper('M2ePro')->getCurrentGmtDate(true) - self::SAFE_CREATE_DATE_INTERVAL;
        $minCreateDate = Mage::helper('M2ePro')->getDate($minCreateDate);

        $unionListingAutoGlobalSelect = $connRead->select()
                    ->from($listingTable,array('result_field'=>'auto_global_adding_template_category_id'))
                    ->where('auto_global_adding_template_category_id IS NOT NULL');
        $unionListingAutoWebsiteSelect = $connRead->select()
                    ->from($listingTable,array('result_field'=>'auto_website_adding_template_category_id'))
                    ->where('auto_website_adding_template_category_id IS NOT NULL');
        $unionListingAutoCategorySelect = $connRead->select()
                    ->from($listingAutoCategoryGroupTable,array('result_field'=>'adding_template_category_id'))
                    ->where('adding_template_category_id IS NOT NULL');
        $unionSelectListingProductTemplate = $connRead->select()
                    ->from($listingProductTable,array('result_field'=>'template_category_id'))
                    ->where('template_category_id IS NOT NULL');

        $unionSelect = $connRead->select()->union(array(
            $unionListingAutoGlobalSelect,
            $unionListingAutoWebsiteSelect,
            $unionListingAutoCategorySelect,
            $unionSelectListingProductTemplate
        ));

        $collection = Mage::getModel('M2ePro/Ebay_Template_Category')->getCollection();
        $collection->getSelect()->where('`id` NOT IN ('.$unionSelect->__toString().')');
        $collection->getSelect()->where('`create_date` < ?',$minCreateDate);

        $unusedTemplates = $collection->getItems();
        foreach ($unusedTemplates as $unusedTemplate) {
            $unusedTemplate->deleteInstance();
        }

        $this->getActualOperationHistory()->saveTimePoint(__METHOD__);
    }

    private function removeOtherCategoriesTemplates()
    {
        $this->getActualOperationHistory()->addTimePoint(__METHOD__,'Remove Unused "Other Category" Policies');

        /** @var $connRead Varien_Db_Adapter_Pdo_Mysql */
        $connRead = Mage::getSingleton('core/resource')->getConnection('core_read');

        $listingTable = Mage::getResourceModel('M2ePro/Ebay_Listing')->getMainTable();
        $listingProductTable = Mage::getResourceModel('M2ePro/Ebay_Listing_Product')->getMainTable();
        $listingAutoCategoryGroupTable = Mage::getResourceModel('M2ePro/Ebay_Listing_Auto_Category_Group')
                                            ->getMainTable();

        $minCreateDate = Mage::helper('M2ePro')->getCurrentGmtDate(true) - self::SAFE_CREATE_DATE_INTERVAL;
        $minCreateDate = Mage::helper('M2ePro')->getDate($minCreateDate);

        $unionListingAutoGlobalSelect = $connRead->select()
                    ->from($listingTable,array('result_field'=>'auto_global_adding_template_other_category_id'))
                    ->where('auto_global_adding_template_other_category_id IS NOT NULL');
        $unionListingAutoWebsiteSelect = $connRead->select()
                    ->from($listingTable,array('result_field'=>'auto_website_adding_template_other_category_id'))
                    ->where('auto_website_adding_template_other_category_id IS NOT NULL');
        $unionListingAutoCategorySelect = $connRead->select()
                    ->from($listingAutoCategoryGroupTable,array('result_field'=>'adding_template_other_category_id'))
                    ->where('adding_template_other_category_id IS NOT NULL');
        $unionSelectListingProductTemplate = $connRead->select()
                    ->from($listingProductTable,array('result_field'=>'template_other_category_id'))
                    ->where('template_other_category_id IS NOT NULL');

        $unionSelect = $connRead->select()->union(array(
            $unionListingAutoGlobalSelect,
            $unionListingAutoWebsiteSelect,
            $unionListingAutoCategorySelect,
            $unionSelectListingProductTemplate
        ));

        $collection = Mage::getModel('M2ePro/Ebay_Template_OtherCategory')->getCollection();
        $collection->getSelect()->where('`id` NOT IN ('.$unionSelect->__toString().')');
        $collection->getSelect()->where('`create_date` < ?',$minCreateDate);

        $unusedTemplates = $collection->getItems();
        foreach ($unusedTemplates as $unusedTemplate) {
            $unusedTemplate->deleteInstance();
        }

        $this->getActualOperationHistory()->saveTimePoint(__METHOD__);
    }

    //####################################
}