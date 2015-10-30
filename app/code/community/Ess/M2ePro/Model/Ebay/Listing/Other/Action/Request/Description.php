<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Ebay_Listing_Other_Action_Request_Description
    extends Ess_M2ePro_Model_Ebay_Listing_Other_Action_Request
{
    //########################################

    public function getData()
    {
        return array_merge(
            $this->getTitleData(),
            $this->getSubtitleData(),
            $this->getDescriptionData()
        );
    }

    //########################################

    /**
     * @return array
     */
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

    /**
     * @return array
     */
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

    /**
     * @return array
     */
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

    //########################################
}