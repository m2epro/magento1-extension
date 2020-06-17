<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Helper_Component_Ebay_Category_Store extends Mage_Core_Helper_Abstract
{
    //########################################

    public function getPath($categoryId, $accountId, $delimiter = '>')
    {
        /** @var Ess_M2ePro_Model_Account $account */
        $account = Mage::helper('M2ePro/Component_Ebay')->getCachedObject('Account', $accountId);
        $categories = $account->getChildObject()->getEbayStoreCategories();

        $pathData = array();

        while (true) {
            $currentCategory = null;

            foreach ($categories as $category) {
                if ($category['category_id'] == $categoryId) {
                    $currentCategory = $category;
                    break;
                }
            }

            if ($currentCategory === null) {
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

    public function isExistDeletedCategories()
    {
        $stmt = Mage::getSingleton('core/resource')->getConnection('core_read')
            ->select()
            ->from(
                array('etsc' => Mage::getModel('M2ePro/Ebay_Template_StoreCategory')->getResource()->getMainTable())
            )
            ->joinLeft(
                array(
                    'edc' => Mage::helper('M2ePro/Module_Database_Structure')
                                    ->getTableNameWithPrefix('m2epro_ebay_account_store_category')
                ),
                'edc.account_id = etsc.account_id AND edc.category_id = etsc.category_id'
            )
            ->reset(Zend_Db_Select::COLUMNS)
            ->columns(
                array(
                    'category_id',
                    'account_id',
                )
            )
            ->where('etsc.category_mode = ?', Ess_M2ePro_Model_Ebay_Template_Category::CATEGORY_MODE_EBAY)
            ->where('edc.category_id IS NULL')
            ->group(
                array('etsc.category_id', 'etsc.account_id')
            )
            ->query();

        return $stmt->fetchColumn() !== false;
    }

    //########################################
}
