<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Adminhtml_Common_GeneralController
    extends Ess_M2ePro_Controller_Adminhtml_Common_SimpleController
{
    //#############################################

    public function searchAutocompleteAction()
    {
        $model       = $this->getRequest()->getParam('model');
        $component   = $this->getRequest()->getParam('component');
        $queryString = $this->getRequest()->getParam('query');
        $maxResults  = (int) $this->getRequest()->getParam('maxResults');

        if (!$model || !$component || !$queryString || !$maxResults) {
            return $this->getResponse()->setBody(json_encode(array()));
        }

        $where = array();
        $parts = explode(' ', $queryString);
        foreach ($parts as $part) {
            $part = trim($part);
            if (!$part) {
                continue;
            }
            $where[]['like'] = "%$part%";
        }

        if (empty($where)) {
            return $this->getResponse()->setBody(json_encode(array()));
        }

        $quotedQueryString = addslashes(trim($queryString));

        $relevanceQueryString  = "IF( `main_table`.`title` LIKE '%". $quotedQueryString. "%', ";
        $relevanceQueryString .= substr_count($quotedQueryString, " ") + 1;
        $relevanceQueryString .= "*3, 0) + IF( `main_table`.`title` LIKE '%";
        $relevanceQueryString .= str_replace(" ", "%', 1, 0) + IF( `main_table`.`title` LIKE '%", $quotedQueryString);
        $relevanceQueryString .= "%', 1 , 0)";

        $collection = Mage::helper('M2ePro/Component')
            ->getComponentModel($component, $model)
            ->getCollection()
            ->addFieldToFilter("`main_table`.`title`", $where)
            ->setOrder('relevance', 'DESC');

        $collection->getSelect()->columns(array('relevance' => new Zend_Db_Expr($relevanceQueryString)));

        $quantity = $collection->getSize();
        $collection->getSelect()->limit($maxResults);
        $results = $collection->getData();

        $suggestions = array();
        $ids         = array();

        foreach ($results as $result) {
            $suggestions[] = $result['title'];
            $ids[] = $result['id'];
        }
        $array = array(
            'query'       => $queryString,
            'suggestions' => $suggestions,
            'data'        => $ids,
            'quantity'    => $quantity
        );
        return $this->getResponse()->setBody(json_encode($array));
    }

    //#############################################
}