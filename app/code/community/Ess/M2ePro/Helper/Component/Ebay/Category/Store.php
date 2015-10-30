<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Helper_Component_Ebay_Category_Store extends Mage_Core_Helper_Abstract
{
    //########################################

    public function getPath($categoryId, $accountId, $delimiter = ' > ')
    {
        $account = Mage::helper('M2ePro/Component_Ebay')->getCachedObject('Account', $accountId);
        $categories = $account->getChildObject()->getEbayStoreCategories();

        $pathData = array();

        while (true) {

            $currentCategory = NULL;

            foreach ($categories as $category) {
                if ($category['category_id'] == $categoryId) {
                    $currentCategory = $category;
                    break;
                }
            }

            if (is_null($currentCategory)) {
                break;
            }

            $pathData[] = $currentCategory['title'];

            if ($currentCategory['parent_id'] == 0) {
                break;
            }

            $categoryId = $currentCategory['parent_id'];
        }

        array_reverse($pathData);
        return implode($delimiter, $pathData);
    }

    //########################################

    public function getSameTemplatesData($ids)
    {
        return Mage::helper('M2ePro/Component_Ebay_Category')->getSameTemplatesData(
            $ids, Mage::getResourceModel('M2ePro/Ebay_Template_OtherCategory')->getMainTable(),
            array('category_secondary','store_category_main','store_category_secondary')
        );
    }

    public function isExistDeletedCategories()
    {
        /** @var $connRead Varien_Db_Adapter_Pdo_Mysql */
        $connRead = Mage::getSingleton('core/resource')->getConnection('core_read');

        $etocTable = Mage::getModel('M2ePro/Ebay_Template_OtherCategory')->getResource()->getMainTable();
        $eascTable = Mage::getSingleton('core/resource')->getTableName('m2epro_ebay_account_store_category');

        // prepare category main select
        // ---------------------------------------
        $primarySelect = $connRead->select();
        $primarySelect->from(
                array('primary_table' => $etocTable)
            )
            ->reset(Zend_Db_Select::COLUMNS)
            ->columns(array(
                'store_category_main_id as category_id',
                'account_id',
            ))
            ->where('store_category_main_mode = ?', Ess_M2ePro_Model_Ebay_Template_Category::CATEGORY_MODE_EBAY)
            ->group(array('category_id', 'account_id'));
        // ---------------------------------------

        // prepare category secondary select
        // ---------------------------------------
        $secondarySelect = $connRead->select();
        $secondarySelect->from(
                array('secondary_table' => $etocTable)
            )
            ->reset(Zend_Db_Select::COLUMNS)
            ->columns(array(
                'store_category_secondary_id as category_id',
                'account_id',
            ))
            ->where('store_category_secondary_mode = ?', Ess_M2ePro_Model_Ebay_Template_Category::CATEGORY_MODE_EBAY)
            ->group(array('category_id', 'account_id'));
        // ---------------------------------------

        $unionSelect = $connRead->select();
        $unionSelect->union(array(
            $primarySelect,
            $secondarySelect,
        ));

        $mainSelect = $connRead->select();
        $mainSelect->reset()
            ->from(array('main_table' => $unionSelect))
            ->joinLeft(
                array('easc' => $eascTable),
                'easc.account_id = main_table.account_id
                    AND easc.category_id = main_table.category_id'
            )
            ->where('easc.category_id IS NULL');

        return $connRead->query($mainSelect)->fetchColumn() !== false;
    }

    //########################################
}