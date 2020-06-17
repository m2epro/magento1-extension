<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Ebay_Template_AffectedListingsProducts_Processor
{
    /** @var Ess_M2ePro_Model_Listing_Product */
    protected $_listingProduct;

    //########################################

    public function processChanges(array $newData, array $oldData)
    {
        $this->templateManagerTemplatesChange($newData, $oldData);

        $this->categoryTemplatesChange(
            $newData,
            $oldData,
            'Ebay_Template_Category',
            'template_category_id'
        );
        $this->categoryTemplatesChange(
            $newData,
            $oldData,
            'Ebay_Template_Category',
            'template_category_secondary_id'
        );
        $this->categoryTemplatesChange(
            $newData,
            $oldData,
            'Ebay_Template_StoreCategory',
            'template_store_category_id'
        );
        $this->categoryTemplatesChange(
            $newData,
            $oldData,
            'Ebay_Template_StoreCategory',
            'template_store_category_secondary_id'
        );
    }

    //########################################

    public function templateManagerTemplatesChange(
        array $newData,
        array $oldData
    ) {
        $templateManager = Mage::getSingleton('M2ePro/Ebay_Template_Manager');

        $newTemplates = $templateManager->getTemplatesFromData($newData);
        $oldTemplates = $templateManager->getTemplatesFromData($oldData);

        foreach ($templateManager->getAllTemplates() as $template) {
            $templateManager->setTemplate($template);

            /** @var Ess_M2ePro_Model_ActiveRecord_SnapshotBuilder $snapshotBuilder */
            if ($templateManager->isHorizontalTemplate()) {
                $snapshotBuilder = Mage::getModel(
                    'M2ePro/Ebay_'.$templateManager->getTemplateModelName().'_SnapshotBuilder'
                );
            } else {
                $snapshotBuilder = Mage::getModel(
                    'M2ePro/'.$templateManager->getTemplateModelName().'_SnapshotBuilder'
                );
            }

            $snapshotBuilder->setModel($newTemplates[$template]);

            $newTemplateData = $snapshotBuilder->getSnapshot();

            /** @var Ess_M2ePro_Model_ActiveRecord_SnapshotBuilder $snapshotBuilder */
            if ($templateManager->isHorizontalTemplate()) {
                $snapshotBuilder = Mage::getModel(
                    'M2ePro/Ebay_'.$templateManager->getTemplateModelName().'_SnapshotBuilder'
                );
            } else {
                $snapshotBuilder = Mage::getModel(
                    'M2ePro/'.$templateManager->getTemplateModelName().'_SnapshotBuilder'
                );
            }

            $snapshotBuilder->setModel($oldTemplates[$template]);

            $oldTemplateData = $snapshotBuilder->getSnapshot();

            /** @var Ess_M2ePro_Model_ActiveRecord_Diff $diff */
            if ($templateManager->isHorizontalTemplate()) {
                $diff = Mage::getModel('M2ePro/Ebay_'.$templateManager->getTemplateModelName().'_Diff');
            } else {
                $diff = Mage::getModel('M2ePro/'.$templateManager->getTemplateModelName().'_Diff');
            }

            $diff->setNewSnapshot($newTemplateData);
            $diff->setOldSnapshot($oldTemplateData);

            /** @var Ess_M2ePro_Model_Template_ChangeProcessorAbstract $changeProcessor */
            if ($templateManager->isHorizontalTemplate()) {
                $changeProcessor = Mage::getModel(
                    'M2ePro/Ebay_'.$templateManager->getTemplateModelName().'_ChangeProcessor'
                );
            } else {
                $changeProcessor = Mage::getModel(
                    'M2ePro/'.$templateManager->getTemplateModelName().'_ChangeProcessor'
                );
            }

            $changeProcessor->process(
                $diff,
                array(
                    array(
                        'id'     => $this->_listingProduct->getId(),
                        'status' => $this->_listingProduct->getStatus()
                    )
                )
            );
        }
    }

    public function categoryTemplatesChange(
        array $newData,
        array $oldData,
        $templateModel,
        $templateIdField
    ) {
        $newTemplateSnapshot = array();

        try {
            if (!empty($newData[$templateIdField])) {
                $newTemplate = Mage::helper('M2ePro')->getCachedObject(
                    $templateModel,
                    $newData[$templateIdField],
                    null,
                    array('template')
                );

                /** @var Ess_M2ePro_Model_ActiveRecord_SnapshotBuilder $snapshotBuilder */
                $snapshotBuilder = Mage::getModel('M2ePro/' .$templateModel. '_SnapshotBuilder');
                $snapshotBuilder->setModel($newTemplate);

                $newTemplateSnapshot = $snapshotBuilder->getSnapshot();
            }
        } catch (Exception $exception) {
            Mage::helper('M2ePro/Module_Exception')->process($exception);
        }

        $oldTemplateSnapshot = array();

        try {
            if (!empty($oldData[$templateIdField])) {
                $oldTemplate = Mage::helper('M2ePro')->getCachedObject(
                    $templateModel,
                    $oldData[$templateIdField],
                    null,
                    array('template')
                );

                /** @var Ess_M2ePro_Model_ActiveRecord_SnapshotBuilder $snapshotBuilder */
                $snapshotBuilder = Mage::getModel('M2ePro/' .$templateModel. '_SnapshotBuilder');
                $snapshotBuilder->setModel($oldTemplate);

                $oldTemplateSnapshot = $snapshotBuilder->getSnapshot();
            }
        } catch (Exception $exception) {
            Mage::helper('M2ePro/Module_Exception')->process($exception);
        }

        /** @var Ess_M2ePro_Model_ActiveRecord_Diff $diff */
        $diff = Mage::getModel('M2ePro/' .$templateModel. '_Diff');
        $diff->setNewSnapshot($newTemplateSnapshot);
        $diff->setOldSnapshot($oldTemplateSnapshot);

        /** @var Ess_M2ePro_Model_Template_ChangeProcessorAbstract $changeProcessor */
        $changeProcessor = Mage::getModel('M2ePro/' .$templateModel. '_ChangeProcessor');
        $changeProcessor->process(
            $diff,
            array(
                array(
                    'id'     => $this->_listingProduct->getId(),
                    'status' => $this->_listingProduct->getStatus()
                )
            )
        );
    }

    //########################################

    /**
     * @param Ess_M2ePro_Model_Listing_Product $listingProduct
     */
    public function setListingProduct($listingProduct)
    {
        $this->_listingProduct = $listingProduct;
    }

    //########################################
}
