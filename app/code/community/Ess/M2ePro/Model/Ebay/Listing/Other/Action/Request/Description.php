<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Ebay_Listing_Other_Action_Request_Description
    extends Ess_M2ePro_Model_Ebay_Listing_Other_Action_Request
{
    // ########################################

    public function getData()
    {
        return array_merge(
            $this->getTitleData(),
            $this->getSubtitleData(),
            $this->getDescriptionData()
        );
    }

    // ########################################

    public function getTitleData()
    {
        if (!$this->getConfigurator()->isTitleAllowed()) {
            return array();
        }

        $title = $this->getEbayListingOther()->getMappedTitle();

        if (is_null($title)) {
            return array();
        }

        return array(
            'title' => $title
        );
    }

    public function getSubtitleData()
    {
        if (!$this->getConfigurator()->isSubtitleAllowed()) {
            return array();
        }

        $subtitle = $this->getEbayListingOther()->getMappedSubTitle();

        if (is_null($subtitle)) {
            return array();
        }

        return array(
            'subtitle' => $subtitle
        );
    }

    public function getDescriptionData()
    {
        if (!$this->getConfigurator()->isDescriptionAllowed()) {
            return array();
        }

        $description = $this->getEbayListingOther()->getMappedDescription();

        if (is_null($description)) {
            return array();
        }

        return array(
            'description' => $description
        );
    }

    // ########################################
}