<?php

class Ess_M2ePro_Model_Ebay_Account_Store_Category_Update
{
    /**
     * @param Mage_Catalog_Model_Ebay_Account $account
     * @return void
     */
    public function process($account)
    {
        $dispatcherObj = Mage::getModel('M2ePro/Ebay_Connector_Dispatcher');
        $connectorObj = $dispatcherObj->getVirtualConnector(
            'account',
            'get',
            'store',
            array(),
            null,
            null,
            $account->getId()
        );

        $dispatcherObj->process($connectorObj);
        $data = $connectorObj->getResponseData();

        if (!is_array($data)) {
            return;
        }

        $infoKeys = array(
            'title',
            'url',
            'subscription_level',
            'description',
        );

        $dataForUpdate = array();
        foreach ($infoKeys as $key) {
            if (!isset($data['data'][$key])) {
                $dataForUpdate['ebay_store_' . $key] = '';
                continue;
            }

            $dataForUpdate['ebay_store_' . $key] = $data['data'][$key];
        }

        $account->addData($dataForUpdate);
        $account->save();

        $tableAccountStoreCategories = Mage::helper('M2ePro/Module_Database_Structure')
            ->getTableNameWithPrefix('m2epro_ebay_account_store_category');

        Mage::getSingleton('core/resource')->getConnection('core_write')->delete(
            $tableAccountStoreCategories,
            array('account_id = ?' => $account->getId())
        );
        Mage::helper('M2ePro/Component_Ebay_Category')->removeStoreRecent();

        if (empty($data['categories'])) {
            return;
        }

        /** @var $connWrite Varien_Db_Adapter_Pdo_Mysql */
        $connWrite = Mage::getSingleton('core/resource')->getConnection('core_write');

        foreach ($data['categories'] as $item) {
            $row = array(
                'account_id'  => $account->getId(),
                'category_id' => $item['category_id'],
                'parent_id'   => $item['parent_id'],
                'title'       => $item['title'],
                'sorder'      => $item['sorder'],
                'is_leaf'     => $item['is_leaf'],
            );

            $connWrite->insertOnDuplicate($tableAccountStoreCategories, $row);
        }
    }
}
