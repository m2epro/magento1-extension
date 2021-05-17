<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

use Ess_M2ePro_Model_Walmart_Listing_Product_Action_ProcessingList as ProcessingList;

class Ess_M2ePro_Model_Walmart_Listing_Product_Action_List_ProcessingRunner
    extends Ess_M2ePro_Model_Connector_Command_Pending_Processing_Single_Runner
{
    const PENDING_REQUEST_MAX_LIFE_TIME = 86400;
    const MAX_LIFETIME                  = 172800;

    /** @var Ess_M2ePro_Model_Listing_Product $_listingProduct */
    protected $_listingProduct = null;

    /** @var Ess_M2ePro_Model_Walmart_Listing_Product_Action_Processing $_processingAction */
    protected $_processingAction = null;

    //########################################

    public function setListingProduct(Ess_M2ePro_Model_Listing_Product $listingProduct)
    {
        $this->_listingProduct = $listingProduct;
        return $this;
    }

    public function setProcessingAction(Ess_M2ePro_Model_Walmart_Listing_Product_Action_Processing $processingAction)
    {
        $this->_processingAction = $processingAction;
        return $this;
    }

    //########################################

    public function prepare()
    {
        if ($this->getProcessingObject() === null || !$this->getProcessingObject()->getId()) {
            throw new Ess_M2ePro_Model_Exception_Logic('Processing does not exist.');
        }

        if ($this->getProcessingAction() === null || !$this->getProcessingAction()->getId()) {
            throw new Ess_M2ePro_Model_Exception_Logic('Processing Action does not exist.');
        }

        $params = $this->getParams();

        $this->getProcessingObject()->setSettings('params', $this->getParams())->save();

        $this->getProcessingAction()->setData('is_prepared', 1);
        $this->getProcessingAction()->setData(
            'request_data', Mage::helper('M2ePro')->jsonEncode($params['request_data'])
        );
        $this->getProcessingAction()->save();

        $accountId = (int)$params['account_id'];
        $sku       = (string)$params['requester_params']['sku'];

        $processingActionList = Mage::getModel('M2ePro/Walmart_Listing_Product_Action_ProcessingList');
        $processingActionList->setData(
            array(
            'account_id'           => $accountId,
            'processing_action_id' => $this->getProcessingAction()->getId(),
            'listing_product_id'   => $this->getListingProduct()->getId(),
            'sku'                  => $sku,
            'stage'                => ProcessingList::STAGE_LIST_DETAILS
            )
        );
        $processingActionList->save();
    }

    public function stop()
    {
        if ($this->getProcessingObject() === null || !$this->getProcessingObject()->getId()) {
            return;
        }

        if ($this->getProcessingAction() === null || !$this->getProcessingAction()->getId()) {
            return;
        }

        $this->getProcessingAction()->deleteInstance();
        $this->getProcessingObject()->deleteInstance();

        $this->unsetLocks();
    }

    //########################################

    protected function eventBefore()
    {
        $params = $this->getParams();

        /** @var Ess_M2ePro_Model_Walmart_Listing_Product_Action_Processing $processingAction */
        $processingAction = Mage::getModel('M2ePro/Walmart_Listing_Product_Action_Processing');
        $processingAction->setData(
            array(
            'listing_product_id' => $params['listing_product_id'],
            'processing_id'      => $this->getProcessingObject()->getId(),
            'type'               => Ess_M2ePro_Model_Walmart_Listing_Product_Action_Processing::TYPE_ADD,
            'is_prepared'        => 0,
            'group_hash'         => $params['group_hash'],
            )
        );
        $processingAction->save();
    }

    protected function eventAfter()
    {
        $params = $this->getParams();

        $accountId = (int)$params['account_id'];
        $sku       = (string)$params['request_data']['sku'];

        $processingActionListSkuCollection = Mage::getResourceModel(
            'M2ePro/Walmart_Listing_Product_Action_ProcessingList_Collection'
        );
        $processingActionListSkuCollection->addFieldToFilter('account_id', $accountId);
        $processingActionListSkuCollection->addFieldToFilter('sku', $sku);

        /** @var Ess_M2ePro_Model_Walmart_Listing_Product_Action_ProcessingList $processingActionListSku */
        $processingActionListSku = $processingActionListSkuCollection->getFirstItem();

        if ($processingActionListSku->getId()) {
            $processingActionListSku->deleteInstance();
        }
    }

    //########################################

    public function processAddResult()
    {
        try {
            /** @var Ess_M2ePro_Model_Walmart_Connector_Product_List_Responser $responser */
            $responser = $this->getResponser();
            $responser->process();
        } catch (Exception $exception) {
            $this->getResponser()->failDetected($exception->getMessage());
            return false;
        }

        return $this->getResponser()->isSuccess();
    }

    public function processRelistResult(ProcessingList $processingList, array $resultData)
    {
        try {
            $response = Mage::getModel('M2ePro/Connector_Connection_Response');
            $response->initFromPreparedResponse($resultData);

            $responser = new Ess_M2ePro_Model_Walmart_Connector_Product_List_UpdateInventory_Responser(
                $this->getResponserParams(), $response
            );
            $responser->setProcessingList($processingList);
            $responser->process();
        } catch (Exception $exception) {
            $responser->failDetected($exception->getMessage());
            return false;
        }

        return $responser->isSuccess();
    }

    //########################################

    public function complete()
    {
        // listing product can be removed during processing action
        if ($this->getListingProduct()->getId() === null) {
            $this->getProcessingObject()->deleteInstance();
            return;
        }

        parent::complete();
    }

    //########################################

    protected function setLocks()
    {
        parent::setLocks();

        $params = $this->getParams();

        $this->getListingProduct()->addProcessingLock(null, $this->getProcessingObject()->getId());
        $this->getListingProduct()->addProcessingLock('in_action', $this->getProcessingObject()->getId());
        $this->getListingProduct()->addProcessingLock(
            $params['lock_identifier'].'_action', $this->getProcessingObject()->getId()
        );

        /** @var Ess_M2ePro_Model_Walmart_Listing_Product $walmartListingProduct */
        $walmartListingProduct = $this->getListingProduct()->getChildObject();
        $variationManager = $walmartListingProduct->getVariationManager();

        if ($variationManager->isRelationChildType()) {
            /** @var Ess_M2ePro_Model_Listing_Product $parentListingProduct */
            $parentListingProduct = $variationManager->getTypeModel()->getParentListingProduct();

            $parentListingProduct->addProcessingLock(null, $this->getProcessingObject()->getId());
            $parentListingProduct->addProcessingLock(
                'child_products_in_action', $this->getProcessingObject()->getId()
            );
        }

        $this->getListingProduct()->getListing()->addProcessingLock(null, $this->getProcessingObject()->getId());
    }

    protected function unsetLocks()
    {
        parent::unsetLocks();

        $params = $this->getParams();

        $this->getListingProduct()->deleteProcessingLocks(null, $this->getProcessingObject()->getId());
        $this->getListingProduct()->deleteProcessingLocks('in_action', $this->getProcessingObject()->getId());
        $this->getListingProduct()->deleteProcessingLocks(
            $params['lock_identifier'].'_action', $this->getProcessingObject()->getId()
        );

        /** @var Ess_M2ePro_Model_Walmart_Listing_Product $walmartListingProduct */
        $walmartListingProduct = $this->getListingProduct()->getChildObject();
        $variationManager = $walmartListingProduct->getVariationManager();

        if ($variationManager->isRelationChildType()) {
            /** @var Ess_M2ePro_Model_Listing_Product $parentListingProduct */
            $parentListingProduct = $variationManager->getTypeModel()->getParentListingProduct();

            $parentListingProduct->deleteProcessingLocks(null, $this->getProcessingObject()->getId());
            $parentListingProduct->deleteProcessingLocks(
                'child_products_in_action', $this->getProcessingObject()->getId()
            );
        }

        $this->getListingProduct()->getListing()->deleteProcessingLocks(null, $this->getProcessingObject()->getId());
    }

    //########################################

    protected function getListingProduct()
    {
        if ($this->_listingProduct !== null) {
            return $this->_listingProduct;
        }

        $params = $this->getParams();

        /** @var Ess_M2ePro_Model_Resource_Listing_Product_Collection $collection */
        $collection = Mage::helper('M2ePro/Component_Walmart')->getCollection('Listing_Product');
        $collection->addFieldToFilter('id', array('in' => $params['listing_product_id']));

        return $this->_listingProduct = $collection->getFirstItem();
    }

    protected function getProcessingAction()
    {
        if ($this->_processingAction !== null) {
            return $this->_processingAction;
        }

        $processingActionCollection = Mage::getResourceModel(
            'M2ePro/Walmart_Listing_Product_Action_Processing_Collection'
        );
        $processingActionCollection->addFieldToFilter('processing_id', $this->getProcessingObject()->getId());

        $processingAction = $processingActionCollection->getFirstItem();

        return $processingAction->getId() ? $this->_processingAction = $processingAction : null;
    }

    //########################################
}
