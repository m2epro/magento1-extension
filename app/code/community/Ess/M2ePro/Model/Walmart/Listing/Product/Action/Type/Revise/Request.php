<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Walmart_Listing_Product_Action_Type_Revise_Request
    extends Ess_M2ePro_Model_Walmart_Listing_Product_Action_Type_Request
{
    const PRODUCT_ID_UPDATE_METADATA_KEY = 'product_id_update_details';

    //########################################

    protected function getActionData()
    {
        $data = array_merge(
            array(
                'sku'  => $this->getWalmartListingProduct()->getSku(),
                'wpid' => $this->getWalmartListingProduct()->getWpid(),
            ),
            $this->getQtyData(),
            $this->getLagTimeData(),
            $this->getPriceData(),
            $this->getPromotionsData(),
            $this->getDetailsData()
        );

        $params = $this->getParams();

        if (isset($params['changed_sku'])) {
            $data['sku'] = $params['changed_sku'];
            $data['is_need_sku_update'] = true;
        }

        if (isset($params['changed_identifier'])) {
            $changedType  = strtoupper($params['changed_identifier']['type']);
            $changedValue = $params['changed_identifier']['value'];

            unset($data['product_id_data']);

            $data['product_id_data'] = array(
                'type' => $changedType,
                'id'   => $changedValue,
            );

            $this->addMetaData(self::PRODUCT_ID_UPDATE_METADATA_KEY, $params['changed_identifier']);
            $data['is_need_product_id_update'] = true;
        }

        // walmart requirement is send price with some details data
        if ($this->getConfigurator()->isDetailsAllowed() && !$this->getConfigurator()->isPriceAllowed()) {
            $data['price'] = $this->getWalmartListingProduct()->getOnlinePrice();
        }

        return $data;
    }

    //########################################
}
