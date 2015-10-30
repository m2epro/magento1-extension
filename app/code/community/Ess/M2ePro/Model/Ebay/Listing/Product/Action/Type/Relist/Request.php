<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Ebay_Listing_Product_Action_Type_Relist_Request
    extends Ess_M2ePro_Model_Ebay_Listing_Product_Action_Type_Request
{
    //########################################

    /**
     * @return array
     */
    public function getActionData()
    {
        $data = array_merge(
            array(
                'item_id' => $this->getEbayListingProduct()->getEbayItemIdReal()
            ),
            $this->getRequestVariations()->getData()
        );

        if ($this->getConfigurator()->isGeneralAllowed()) {

            $data['sku'] = $this->getEbayListingProduct()->getSku();

            $data = array_merge(

                $data,

                $this->getRequestCategories()->getData(),

                $this->getRequestPayment()->getData(),
                $this->getRequestReturn()->getData(),
                $this->getRequestShipping()->getData()
            );
        }

        return array_merge(
            $data,
            $this->getRequestSelling()->getData(),
            $this->getRequestDescription()->getData()
        );
    }

    protected function prepareFinalData(array $data)
    {
        $data = $this->addConditionIfItIsNecessary($data);
        $data = $this->removeImagesIfThereAreNoChanges($data);
        return parent::prepareFinalData($data);
    }

    //########################################

    private function addConditionIfItIsNecessary(array $data)
    {
        $additionalData = $this->getListingProduct()->getAdditionalData();

        if (!isset($additionalData['is_need_relist_condition']) ||
            !$additionalData['is_need_relist_condition'] ||
            isset($data['item_condition'])) {
            return $data;
        }

        $data = array_merge($data, $this->getRequestDescription()->getConditionData());

        return $data;
    }

    private function removeImagesIfThereAreNoChanges(array $data)
    {
        $additionalData = $this->getListingProduct()->getAdditionalData();

        $key = 'ebay_product_images_hash';
        if (!empty($additionalData[$key]) && isset($data['images']['images']) &&
            $additionalData[$key] == Mage::helper('M2ePro/Component_Ebay')->getImagesHash($data['images']['images'])) {
            unset($data['images']['images']);
        }

        $key = 'ebay_product_variation_images_hash';
        if (!empty($additionalData[$key]) && isset($data['variation_image']) &&
            $additionalData[$key] == Mage::helper('M2ePro/Component_Ebay')->getImagesHash($data['variation_image'])) {
            unset($data['variation_image']);
        }

        return $data;
    }

    //########################################
}