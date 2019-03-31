<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2017 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */
class Ess_M2ePro_Model_Mysql4_Listing_Product_Instruction_Collection extends Ess_M2ePro_Model_Mysql4_Collection_Abstract
{
    //########################################

    public function _construct()
    {
        $this->_init('M2ePro/Listing_Product_Instruction');
    }

    //########################################

    /**
     * @param DateTime|NULL $dateTime
     * @return $this
     */
    public function applySkipUntilFilter($dateTime = NULL)
    {
        is_null($dateTime) && $dateTime = new DateTime('now', new DateTimeZone('UTC'));

        $this->getSelect()->where(
            'skip_until IS NULL OR ? > skip_until', $dateTime->format('Y-m-d H:i:s')
        );

        return $this;
    }

    /**
     * @return $this
     */
    public function applyNonBlockedFilter()
    {
        $this->getSelect()
            ->joinLeft(
                array('pl' => $this->getResource()->getTable('M2ePro/Processing_Lock')),
                'pl.object_id = main_table.listing_product_id AND model_name = \'M2ePro/Listing_Product\''
            );

        $this->addFieldToFilter('pl.id', array('null' => true));
        return $this;
    }

    //########################################
}