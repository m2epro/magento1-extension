<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Configuration_Category_Edit_Primary_Tabs_Specific
    extends Mage_Adminhtml_Block_Widget
{
    // ########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('ebayConfigurationCategoryEditPrimaryTabsSpecific');
        $this->setTemplate('M2ePro/ebay/configuration/category/primary/specific.phtml');
        //------------------------------
    }

    // ########################################

    protected function _beforeToHtml()
    {
        $categoryData = Mage::helper('M2ePro/Data_Global')->getValue('chooser_data');
        $specificBlocks = array();
        $templates = array();
        $uniqueIdCounter = 0;

        if (!empty($categoryData['templates'])) {

            foreach ($categoryData['templates'] as $template) {
                $uniqueId = 'sb' . $uniqueIdCounter;

                $specificBlocks[] = array(
                    'create_date' => $template['date'],
                    'template_id' => $template['id'],
                    'unique_id' => $uniqueId,
                    'block' => $this->createSpecificsBlock(array(), $uniqueId),
                );

                $uniqueIdCounter++;
            }

        } else {

            $specificsSets = $this->getSpecificsSets();
            uasort($specificsSets, array($this, 'specificsSetsSortCallback'));

            foreach ($specificsSets as $templateId => $specificsSet) {
                $uniqueId = 'sb' . $uniqueIdCounter;

                $date = $specificsSet['create_date']->format('Y-m-d h:i');
                $templates[] = array(
                    'id' => $templateId,
                    'date' => $date,
                );

                $specificBlocks[] = array(
                    'create_date' => $date,
                    'template_id' => $templateId,
                    'unique_id' => $uniqueId,
                    'block' => $this->createSpecificsBlock($specificsSet['specifics'], $uniqueId),
                );

                $uniqueIdCounter++;
            }
        }

        $this->templates = $templates;
        $this->specificsBlocks = $specificBlocks;

        return parent::_beforeToHtml();
    }

    // ########################################

    private function createSpecificsBlock($specifics, $uniqueId)
    {
        $categoryData = Mage::helper('M2ePro/Data_Global')->getValue('chooser_data');

        $specificBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_listing_category_specific');
        $specificBlock->setMarketplaceId($categoryData['marketplace']);
        $specificBlock->setCategoryMode($categoryData['mode']);
        $specificBlock->setCategoryValue($categoryData['value']);
        $specificBlock->setUniqueId($uniqueId);
        $specificBlock->setCompactMode();

        if (!empty($specifics)) {
            $specificBlock->setSelectedSpecifics($specifics);
        }

        return $specificBlock;
    }

    // ------------------------------------------

    private function getSpecificsSets()
    {
        $categoryData = Mage::helper('M2ePro/Data_Global')->getValue('chooser_data');
        $tcTable = Mage::getModel('M2ePro/Ebay_Template_Category')->getResource()->getMainTable();

        /** @var $connRead Varien_Db_Adapter_Pdo_Mysql */
        $connRead = Mage::getSingleton('core/resource')->getConnection('core_read');

        $select = $connRead->select();
        $select->from(array('tc' => $tcTable), array('id', 'create_date'))
            ->where('tc.marketplace_id = ?', (int)$categoryData['marketplace'])
            ->where('tc.category_main_mode = ?', (int)$categoryData['mode']);

        if ($categoryData['mode'] == Ess_M2ePro_Model_Ebay_Template_Category::CATEGORY_MODE_EBAY) {
            $select->where('tc.category_main_id = ?', $categoryData['value']);
        } else {
            $select->where('tc.category_main_attribute = ?', $categoryData['value']);
        }

        $templatesResult = $connRead->fetchAll($select);
        if (empty($templatesResult)) {
            return array();
        }

        $specificsSets = array();
        foreach ($templatesResult as $templateRow) {
            $templateId = (int)$templateRow['id'];

            $collection = Mage::getModel('M2ePro/Ebay_Template_Category_Specific')->getCollection();
            $collection->addFieldToFilter('template_category_id', array('eq' => $templateId));
            $specifics = $collection->toArray();

            $specificsSets[$templateId] = array(
                'create_date' => new DateTime($templateRow['create_date']),
                'specifics' => $specifics['items'],
            );
        }

        return $specificsSets;
    }

    // ########################################

    public function specificsSetsSortCallback($first, $second)
    {
        if ($first['create_date'] == $second['create_date']) {
            return 0;
        }

        return ($first['create_date'] > $second['create_date']) ? -1 : 1;
    }

    // ########################################
}