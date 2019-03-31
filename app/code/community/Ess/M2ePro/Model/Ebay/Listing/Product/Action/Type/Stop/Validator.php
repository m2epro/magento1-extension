<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Ebay_Listing_Product_Action_Type_Stop_Validator
    extends Ess_M2ePro_Model_Ebay_Listing_Product_Action_Type_Validator
{
    //########################################

    public function validate()
    {
        if (!$this->getListingProduct()->isStoppable()) {

            $params = $this->getParams();

            if (empty($params['remove'])) {

                // M2ePro_TRANSLATIONS
                // Item is not Listed or not available
                $this->addMessage('Item is not Listed or not available');

            } else {
                $removeHandler = Mage::getModel(
                    'M2ePro/Listing_Product_RemoveHandler', array('listing_product' => $this->getListingProduct())
                );
                $removeHandler->process();
            }

            return false;
        }

        return true;
    }

    //########################################
}