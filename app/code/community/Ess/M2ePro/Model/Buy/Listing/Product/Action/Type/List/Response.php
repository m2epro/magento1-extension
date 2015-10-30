<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Buy_Listing_Product_Action_Type_List_Response
    extends Ess_M2ePro_Model_Buy_Listing_Product_Action_Type_Response
{
    //########################################

    public function processSuccess($params = array())
    {
        $data = array(
            'status' => Ess_M2ePro_Model_Listing_Product::STATUS_LISTED,
        );

        $data = $this->appendStatusChangerValue($data);
        $data = $this->appendIdentifiersData($data);
        $data = $this->appendConditionValues($data);

        $data = $this->appendQtyValues($data);
        $data = $this->appendPriceValues($data);

        $data = $this->appendShippingValues($data);

        $this->getListingProduct()->addData($data);
        $this->getListingProduct()->save();

        $this->createBuyItem();
    }

    //########################################

    private function appendIdentifiersData($data)
    {
        $data['sku'] = $this->getRequestData()->getSku();

        $data['general_id'] = $this->getRequestData()->getProductId();

        return $data;
    }

    //########################################

    private function createBuyItem()
    {
        /** @var Ess_M2ePro_Model_Buy_Listing_Product_Action_Type_List_Linking $linkingObject */
        $linkingObject = Mage::getModel('M2ePro/Buy_Listing_Product_Action_Type_List_Linking');
        $linkingObject->setListingProduct($this->getListingProduct());

        $linkingObject->createBuyItem();
    }

    //########################################
}