<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Resource_Listing_Product_ScheduledAction_Collection
    extends Ess_M2ePro_Model_Resource_Collection_Abstract
{
    /** how much time should pass to increase priority value by 1 */
    const SECONDS_TO_INCREMENT_PRIORITY = 30;

    /** @var null|string */
    protected $componentMode = null;

    //########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Listing_Product_ScheduledAction');
    }

    //########################################

    /**
     * @param $componentMode
     * @return $this
     */
    public function setComponentMode($componentMode)
    {
        $this->componentMode = $componentMode;
        return $this;
    }

    /**
     * @return string
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function getComponentMode()
    {
        if ($this->componentMode === null) {
            throw new Ess_M2ePro_Model_Exception_Logic('Component mode is not set.');
        }

        return $this->componentMode;
    }

    //########################################

    /**
     * @param int $priority
     * @param int $actionType
     * @return $this
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function getScheduledActionsPreparedCollection($priority, $actionType)
    {
        $this->getSelect()->joinLeft(
            array('lp' => Mage::getResourceModel('M2ePro/Listing_Product')->getMainTable()),
            'main_table.listing_product_id = lp.id'
        );
        $this->getSelect()->joinLeft(
            array('l' => Mage::getResourceModel('M2ePro/Listing')->getMainTable()),
            'lp.listing_id = l.id'
        );
        $this->getSelect()->joinLeft(
            array('pl' => Mage::getResourceModel('M2ePro/Processing_Lock')->getMainTable()),
            'pl.object_id = main_table.listing_product_id AND model_name = \'M2ePro/Listing_Product\''
        );

        $this->addFieldToFilter('component', $this->getComponentMode());
        $this->addFieldToFilter('pl.id', array('null' => true));
        $this->addFieldToFilter('main_table.action_type', $actionType);

        $now = Mage::helper('M2ePro')->getCurrentGmtDate();
        $this->getSelect()->reset(Zend_Db_Select::COLUMNS)
            ->columns(
                array(
                    'id'                 => 'main_table.id',
                    'listing_product_id' => 'main_table.listing_product_id',
                    'account_id'         => 'l.account_id',
                    'action_type'        => 'main_table.action_type',
                    'tag'                => new Zend_Db_Expr('NULL'),
                    'additional_data'    => 'main_table.additional_data',
                    'coefficient'        => new Zend_Db_Expr(
                        "{$priority} +
                        (time_to_sec(timediff('{$now}', main_table.create_date)) / "
                        . self::SECONDS_TO_INCREMENT_PRIORITY . ")"
                    ),
                    'create_date'        => 'main_table.create_date',
                )
            );

        return $this;
    }

    /**
     * @return $this
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function joinAccountTable()
    {
        $componentMode = ucfirst($this->getComponentMode());
        $this->getSelect()->joinLeft(
            array('account' => Mage::getResourceModel("M2ePro/{$componentMode}_Account")->getMainTable()),
            'l.account_id = account.account_id'
        );

        return $this;
    }

    /**
     * @param $tag
     * @param bool $canBeEmpty
     * @return $this
     */
    public function addTagFilter($tag, $canBeEmpty = false)
    {
        $whereExpression = "main_table.tag LIKE '%/{$tag}/%'";
        if ($canBeEmpty) {
            $whereExpression .= " OR main_table.tag IS NULL OR main_table.tag = ''";
        }

        $this->getSelect()->where($whereExpression);
        return $this;
    }

    /**
     * @param Zend_Db_Expr $expression
     * @return $this
     */
    public function addFilteredTagColumnToSelect(Zend_Db_Expr $expression)
    {
        $this->getSelect()->columns(array('filtered_tag' => $expression));
        return $this;
    }

    /**
     * @param $secondsInterval
     * @return $this
     * @throws Exception
     */
    public function addCreatedBeforeFilter($secondsInterval)
    {
        $interval = new \DateTime('now', new \DateTimeZone('UTC'));
        $interval->modify("-{$secondsInterval} seconds");

        $this->addFieldToFilter('main_table.create_date', array('lt' => $interval->format('Y-m-d H:i:s')));
        return $this;
    }

    //########################################
}
