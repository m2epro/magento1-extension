<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

abstract class Ess_M2ePro_Model_Amazon_Connector_Search_ByIdentifier_ItemsResponser
    extends Ess_M2ePro_Model_Connector_Command_Pending_Responser
{
    //########################################

    protected function validateResponse()
    {
        $responseData = $this->getResponse()->getData();
        if (!isset($responseData['items']) && !isset($responseData['unavailable'])) {
            return false;
        }

        return true;
    }

    //########################################

    protected function prepareResponseData()
    {
        $responseData = $this->getResponse()->getData();

        if (!empty($responseData['unavailable'])) {
            $this->_preparedResponseData = false;
            return;
        }

        $result = array();

        foreach ($responseData['items'] as $item) {
            $product = array(
                'general_id' => $item['product_id'],
                'brand' => isset($item['brand']) ? $item['brand'] : '',
                'title' => $item['title'],
                'image_url' => $item['image_url'],
                'is_variation_product' => $item['is_variation_product'],
            );

            if ($product['is_variation_product']) {
                if(empty($item['bad_parent'])) {
                    $product += array(
                        'parentage' => $item['parentage'],
                        'variations' => $item['variations'],
                        'bad_parent' => false
                    );
                } else {
                    $product['bad_parent'] = (bool)$item['bad_parent'];
                }
            }

            if (!empty($item['list_price'])) {
                $product['list_price'] = array(
                    'amount' => $item['list_price']['amount'],
                    'currency' => $item['list_price']['currency'],
                );
            }

            if (!empty($item['requested_child_id'])) {
                $product['requested_child_id'] = $item['requested_child_id'];
            }

            $result[] = $product;
        }

        $this->_preparedResponseData = $result;
    }

    //########################################
}
