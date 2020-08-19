<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Adminhtml_ControlPanel_Tools_M2ePro_GeneralController
    extends Ess_M2ePro_Controller_Adminhtml_ControlPanel_CommandController
{
    //########################################

    /**
     * @hidden
     */
    public function deleteBrokenDataAction()
    {
        $tableNames = $this->getRequest()->getParam('table', array());

        if (empty($tableNames)) {
            $this->_getSession()->addError('Required params are not presented.');
            return $this->_redirectUrl($this->_getRefererUrl());
        }

        $inspection = Mage::getSingleton('M2ePro/ControlPanel_Inspection_Manager')
            ->getInspection('Ess_M2ePro_Model_ControlPanel_Inspection_Inspector_BrokenTables');
        $inspection->fix($tableNames);

        return $this->_redirectUrl($this->_getRefererUrl());
    }

    /**
     * @title "Show Broken Table IDs"
     * @hidden
     */
    public function showBrokenTableIdsAction()
    {
        $tableNames = $this->getRequest()->getParam('table', array());

        if (empty($tableNames)) {
            $this->_redirectUrl(Mage::helper('adminhtml')->getUrl('*/*/checkTables/'));
        }

        $tableName = array_pop($tableNames);

        $inspection = Mage::getSingleton('M2ePro/ControlPanel_Inspection_Manager')
            ->getInspection('Ess_M2ePro_Model_ControlPanel_Inspection_Inspector_BrokenTables');

        $info = $inspection->getBrokenRecordsInfo($tableName);

        return $this->getResponse()->setBody(
            '<pre>' .
            "<span>Broken Records '{$tableName}'<span><br>" .
            print_r($info, true)
        );
    }

    // ---------------------------------------

    /**
     * @title "Repair Removed Store"
     * @hidden
     */
    public function repairRemovedMagentoStoreAction()
    {
        $replaceIdFrom = $this->getRequest()->getParam('replace_from');
        $replaceIdTo = $this->getRequest()->getParam('replace_to');

        if (!$replaceIdFrom || !$replaceIdTo) {
            $this->_getSession()->addError('Required params are not presented.');
            $this->_redirectUrl(Mage::helper('M2ePro/View_ControlPanel')->getPageToolsTabUrl());
        }

        $manager = Mage::getSingleton('M2ePro/ControlPanel_Inspection_Manager');
        $manager->getInspection('Ess_M2ePro_Model_ControlPanel_Inspection_Inspector_RemovedStores')->fix(
            array(
                $replaceIdFrom => $replaceIdTo
            )
        );

        $this->_redirectUrl(Mage::helper('M2ePro/View_ControlPanel')->getPageInspectionTabUrl());
    }

    // ---------------------------------------

    /**
     * @hidden
     */
    public function repairListingProductStructureAction()
    {
        $repairInfo = $this->getRequest()->getPost('repair_info');

        if (empty($repairInfo)) {
            $this->_redirectUrl($this->_getRefererUrl());
        }

        $dataForRepair = array();
        foreach ($repairInfo as $item) {
            $temp = (array)Mage::helper('M2ePro')->jsonDecode($item);
            $dataForRepair[$temp['table']] = $temp['ids'];
        }

        $inspector = Mage::getSingleton('M2ePro/ControlPanel_Inspection_Manager')
            ->getInspection('Ess_M2ePro_Model_ControlPanel_Inspection_Inspector_ListingProductStructure');
        $inspector->fix($dataForRepair);

        $this->_redirectUrl($this->_getRefererUrl());
    }

    /**
     * @hidden
     */
    public function repairOrderItemStructureAction()
    {
        $repairInfo = $this->getRequest()->getPost('repair_info');

        if (empty($repairInfo)) {
            $this->_redirectUrl($this->_getRefererUrl());
        }

        $dataForRepair = (array)Mage::helper('M2ePro')->jsonDecode($repairInfo);

        $inspector = Mage::getSingleton('M2ePro/ControlPanel_Inspection_Manager')
            ->getInspection('Ess_M2ePro_Model_ControlPanel_Inspection_Inspector_OrderItemStructure');
        $inspector->fix($dataForRepair);

        $this->_redirectUrl($this->_getRefererUrl());
    }

    /**
     * @hidden
     */
    public function repairEbayItemIdStructureAction()
    {
        $ids = $this->getRequest()->getPost('repair_info');

        if (empty($ids)) {
            $this->_redirectUrl($this->_getRefererUrl());
        }

        $dataForRepair = (array)Mage::helper('M2ePro')->jsonDecode($ids);

        $inspector = Mage::getSingleton('M2ePro/ControlPanel_Inspection_Manager')
            ->getInspection('Ess_M2ePro_Model_ControlPanel_Inspection_Inspector_EbayItemIdUnknow');
        $inspector->fix($dataForRepair);

        $this->_redirectUrl($this->_getRefererUrl());
    }

    /**
     * @hidden
     */
    public function repairAmazonProductWithoutVariationsAction()
    {
        $ids = $this->getRequest()->getPost('repair_info');

        if (empty($ids)) {
            $this->_redirectUrl($this->_getRefererUrl());
        }

        $dataForRepair = (array)Mage::helper('M2ePro')->jsonDecode($ids);

        $inspector = Mage::getSingleton('M2ePro/ControlPanel_Inspection_Manager')
            ->getInspection('Ess_M2ePro_Model_ControlPanel_Inspection_Inspector_AmazonProductsWithoutVariations');
        $inspector->fix($dataForRepair);

        $this->_redirectUrl($this->_getRefererUrl());
    }

    //########################################
}
