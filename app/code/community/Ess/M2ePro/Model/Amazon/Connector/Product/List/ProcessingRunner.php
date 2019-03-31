<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Amazon_Connector_Product_List_ProcessingRunner
    extends Ess_M2ePro_Model_Amazon_Connector_Product_ProcessingRunner
{
    // ########################################

    public function prepare()
    {
        parent::prepare();

        $params = $this->getParams();

        $accountId = (int)$params['account_id'];
        $sku       = (string)$params['request_data']['sku'];

        $processingActionListSku = Mage::getModel('M2ePro/Amazon_Listing_Product_Action_ProcessingListSku');
        $processingActionListSku->setData(array(
            'account_id' => $accountId,
            'sku'        => $sku,
        ));
        $processingActionListSku->save();
    }

    public function complete()
    {
        parent::complete();

        $params = $this->getParams();

        $accountId = (int)$params['account_id'];
        $sku       = (string)$params['request_data']['sku'];

        $processingActionListSkuCollection = Mage::getResourceModel(
            'M2ePro/Amazon_Listing_Product_Action_ProcessingListSku_Collection'
        );
        $processingActionListSkuCollection->addFieldToFilter('account_id', $accountId);
        $processingActionListSkuCollection->addFieldToFilter('sku', $sku);

        /** @var Ess_M2ePro_Model_Amazon_Listing_Product_Action_ProcessingListSku $processingActionListSku */
        $processingActionListSku = $processingActionListSkuCollection->getFirstItem();

        if ($processingActionListSku->getId()) {
            $processingActionListSku->deleteInstance();
        }
    }

    // ########################################
}