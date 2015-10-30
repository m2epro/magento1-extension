<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

abstract class Ess_M2ePro_Model_Connector_Buy_Search_ByQuery_ItemsResponser
    extends Ess_M2ePro_Model_Connector_Buy_Responser
{
    //########################################

    protected function validateResponseData($response)
    {
        if (!isset($response['items'])) {
            return false;
        }

        return true;
    }

    protected function prepareResponseData($response)
    {
        $products = array();

        foreach ($response['items'] as $item) {

            if (isset($item['variations'])) {
                $product = array(
                    'title' => $item['title'],
                    'image_url' => $item['image_url'],
                    'variations' => $item['variations']
                );

                $products[] = $product;
                continue;
            }

            $product = array(
                'general_id' => $item['product_id'],
                'title' => $item['title'],
                'image_url' => $item['image_url']
            );

            if (!empty($item['price'])) {
                $product['price'] = $item['price'];
            }

            $products[] = $product;
        }

        return $products;
    }

    //########################################
}