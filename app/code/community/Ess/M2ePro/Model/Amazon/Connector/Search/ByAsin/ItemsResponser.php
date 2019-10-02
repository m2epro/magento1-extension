<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

abstract class Ess_M2ePro_Model_Amazon_Connector_Search_ByAsin_ItemsResponser
    extends Ess_M2ePro_Model_Amazon_Connector_Command_Pending_Responser
{
    // ########################################

    protected function validateResponse()
    {
        $responseData = $this->getResponse()->getData();
        if (!isset($responseData['item']) && !isset($responseData['unavailable'])) {
            return false;
        }

        return true;
    }

    // ########################################

    protected function prepareResponseData()
    {
        $responseData = $this->getResponse()->getData();

        if (!empty($responseData['unavailable'])) {
            $this->_preparedResponseData = false;
            return;
        }

        if (empty($responseData['item'])) {
            $this->_preparedResponseData = null;
            return;
        }

        $responseItem = $responseData['item'];

        $product = array(
            'general_id' => $responseItem['product_id'],
            'brand'      => isset($responseItem['brand']) ? $responseItem['brand'] : '',
            'title'      => $responseItem['title'],
            'image_url'  => $responseItem['image_url'],
            'is_variation_product' => $responseItem['is_variation_product'],
        );

        if ($product['is_variation_product']) {
            if(empty($responseItem['bad_parent'])) {
                $product += array(
                    'parentage'  => $responseItem['parentage'],
                    'variations' => $responseItem['variations'],
                    'bad_parent' => false
                );
            } else {
                $product['bad_parent'] = (bool)$responseItem['bad_parent'];
            }
        }

        if (!empty($responseItem['list_price'])) {
            $product['list_price'] = array(
                'amount'   => $responseItem['list_price']['amount'],
                'currency' => $responseItem['list_price']['currency'],
            );
        }

        if (!empty($responseItem['requested_child_id'])) {
            $product['requested_child_id'] = $responseItem['requested_child_id'];
        }

        $this->_preparedResponseData = $product;
    }

    // ########################################
}
