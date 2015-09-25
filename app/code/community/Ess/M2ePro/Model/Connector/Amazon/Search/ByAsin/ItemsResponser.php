<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

abstract class Ess_M2ePro_Model_Connector_Amazon_Search_ByAsin_ItemsResponser
    extends Ess_M2ePro_Model_Connector_Amazon_Responser
{
    // ########################################

    protected function validateResponseData($response)
    {
        if (!isset($response['item']) && !isset($response['unavailable'])) {
            return false;
        }

        return true;
    }

    // ########################################

    protected function prepareResponseData($response)
    {
        if (!empty($response['unavailable'])) {
            return false;
        }

        if (empty($response['item'])) {
            return null;
        }

        $responseItem = $response['item'];

        $product = array(
            'general_id' => $responseItem['product_id'],
            'brand' => isset($responseItem['brand']) ? $responseItem['brand'] : '',
            'title' => $responseItem['title'],
            'image_url' => $responseItem['image_url'],
            'is_variation_product' => $responseItem['is_variation_product'],
        );

        if ($product['is_variation_product']) {
            if(empty($responseItem['bad_parent'])) {
                $product += array(
                    'parentage' => $responseItem['parentage'],
                    'variations' => $responseItem['variations'],
                    'bad_parent' => false
                );
            } else {
                $product['bad_parent'] = (bool)$responseItem['bad_parent'];
            }
        }

        if (!empty($responseItem['list_price'])) {
            $product['list_price'] = array(
                'amount' => $responseItem['list_price']['amount'],
                'currency' => $responseItem['list_price']['currency'],
            );
        }

        if (!empty($responseItem['requested_child_id'])) {
            $product['requested_child_id'] = $responseItem['requested_child_id'];
        }

        return $product;
    }

    // ########################################
}