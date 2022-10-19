<?php

/**
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Ebay_Feedback extends Ess_M2ePro_Model_Component_Abstract
{
    const ROLE_BUYER  = 'Buyer';
    const ROLE_SELLER = 'Seller';

    const TYPE_NEUTRAL  = 'Neutral';
    const TYPE_POSITIVE = 'Positive';
    const TYPE_NEGATIVE = 'Negative';

    /** @var Ess_M2ePro_Model_Account */
    protected $_accountModel = null;
    /** @var Ess_M2ePro_Model_Synchronization_Log */
    protected $_synchronizationLog;
    /** @var Ess_M2ePro_Helper_Data */
    protected $_dataHelper;
    /** @var Ess_M2ePro_Helper_Module_Exception */
    protected $_exceptionHelper;
    /** @var Ess_M2ePro_Helper_Module_Translation */
    protected $_translationHelper;

    public function __construct()
    {
        parent::__construct();

        $this->_synchronizationLog = Mage::getModel('M2ePro/Synchronization_Log');
        $this->_synchronizationLog->setComponentMode(Ess_M2ePro_Helper_Component_Ebay::NICK);
        $this->_synchronizationLog->setSynchronizationTask(Ess_M2ePro_Model_Synchronization_Log::TASK_OTHER);

        $this->_dataHelper = Mage::helper('M2ePro');
        $this->_exceptionHelper = Mage::helper('M2ePro/Module_Exception');
        $this->_translationHelper = Mage::helper('M2ePro/Module_Translation');
    }

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Ebay_Feedback');
    }

    public function deleteInstance()
    {
        $temp = parent::deleteInstance();
        $temp && $this->_accountModel = null;
        return $temp;
    }

    /**
     * @return Ess_M2ePro_Model_Account
     */
    public function getAccount()
    {
        if ($this->_accountModel === null) {
            $this->_accountModel = Mage::helper('M2ePro/Component_Ebay')->getCachedObject(
                'Account', $this->getData('account_id')
            );
        }

        return $this->_accountModel;
    }

    /**
     * @param Ess_M2ePro_Model_Account $instance
     */
    public function setAccount(Ess_M2ePro_Model_Account $instance)
    {
         $this->_accountModel = $instance;
    }

    /**
     * @return Ess_M2ePro_Model_Amazon_Account|Ess_M2ePro_Model_Ebay_Account|Ess_M2ePro_Model_Walmart_Account
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function getEbayAccount()
    {
        return $this->getAccount()->getChildObject();
    }

    public function isNeutral()
    {
        return $this->getData('buyer_feedback_type') == self::TYPE_NEUTRAL;
    }

    public function isNegative()
    {
        return $this->getData('buyer_feedback_type') == self::TYPE_NEGATIVE;
    }

    public function isPositive()
    {
        return $this->getData('buyer_feedback_type') == self::TYPE_POSITIVE;
    }

    /**
     * @return Ess_M2ePro_Model_Component_Parent_Abstract|null
     */
    public function getOrder()
    {
        /** @var $collection Ess_M2ePro_Model_Resource_Order_Collection */
        $collection = Mage::helper('M2ePro/Component_Ebay')->getCollection('Order');
        $collection->getSelect()
            ->join(
                array('oi' => Mage::getResourceModel('M2ePro/Order_Item')->getMainTable()),
                '`oi`.`order_id` = `main_table`.`id`',
                array()
            )
            ->join(
                array('eoi' => Mage::getResourceModel('M2ePro/Ebay_Order_Item')->getMainTable()),
                '`eoi`.`order_item_id` = `oi`.`id`',
                array()
            );

        $collection->addFieldToFilter('account_id', $this->getData('account_id'));
        $collection->addFieldToFilter('eoi.item_id', $this->getData('ebay_item_id'));
        $collection->addFieldToFilter('eoi.transaction_id', $this->getData('ebay_transaction_id'));

        $collection->getSelect()->limit(1);

        $order = $collection->getFirstItem();

        return $order->getId() !== null ? $order : null;
    }
}
