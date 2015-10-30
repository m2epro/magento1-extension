<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Synchronization_Templates_Runner
{
    private $items = array();

    /** @var Ess_M2ePro_Model_Synchronization_LockItem $lockItem */
    private $lockItem      = NULL;
    private $percentsStart = 0;
    private $percentsEnd   = 100;

    private $maxProductsPerStep = 10;
    private $connectorModel     = NULL;

    //########################################

    /**
     * @param Ess_M2ePro_Model_Synchronization_LockItem $object
     */
    public function setLockItem(Ess_M2ePro_Model_Synchronization_LockItem $object)
    {
        $this->lockItem = $object;
    }

    /**
     * @return Ess_M2ePro_Model_Synchronization_LockItem
     */
    public function getLockItem()
    {
        return $this->lockItem;
    }

    // ---------------------------------------

    /**
     * @param int $value
     */
    public function setPercentsStart($value)
    {
        $this->percentsStart = $value;
    }

    /**
     * @return int
     */
    public function getPercentsStart()
    {
        return $this->percentsStart;
    }

    // ---------------------------------------

    /**
     * @param int $value
     */
    public function setPercentsEnd($value)
    {
        $this->percentsEnd = $value;
    }

    /**
     * @return int
     */
    public function getPercentsEnd()
    {
        return $this->percentsEnd;
    }

    // ---------------------------------------

    /**
     * @param int $value
     */
    public function setMaxProductsPerStep($value)
    {
        $this->maxProductsPerStep = $value;
    }

    /**
     * @return int
     */
    public function getMaxProductsPerStep()
    {
        return $this->maxProductsPerStep;
    }

    // ---------------------------------------

    public function setConnectorModel($value)
    {
        $this->connectorModel = $value;
    }

    public function getConnectorModel()
    {
        return $this->connectorModel;
    }

    //########################################

    /**
     * @param $product
     * @param $action
     * @param Ess_M2ePro_Model_Listing_Product_Action_Configurator $configurator
     * @return bool
     */
    public function addProduct($product,
                               $action,
                               Ess_M2ePro_Model_Listing_Product_Action_Configurator $configurator)
    {
        if (isset($this->items[$product->getId()])) {

            $existedItem = $this->items[$product->getId()];

            if ($existedItem['action'] == $action) {

                /** @var Ess_M2ePro_Model_Listing_Product_Action_Configurator $existedConfigurator */
                $existedConfigurator = $existedItem['product']->getActionConfigurator();
                $existedConfigurator->mergeData($configurator);
                $existedConfigurator->mergeParams($configurator);

                return true;
            }

            do {

                if ($action == Ess_M2ePro_Model_Listing_Product::ACTION_STOP) {
                    $this->deleteProduct($existedItem['product']);
                    break;
                }

                if ($existedItem['action'] == Ess_M2ePro_Model_Listing_Product::ACTION_STOP) {
                    return false;
                }

                if ($action == Ess_M2ePro_Model_Listing_Product::ACTION_LIST) {
                    $this->deleteProduct($existedItem['product']);
                    break;
                }

                if ($existedItem['action'] == Ess_M2ePro_Model_Listing_Product::ACTION_LIST) {
                    return false;
                }

                if ($action == Ess_M2ePro_Model_Listing_Product::ACTION_RELIST) {
                    $this->deleteProduct($existedItem['product']);
                    break;
                }

                if ($existedItem['action'] == Ess_M2ePro_Model_Listing_Product::ACTION_RELIST) {
                    return false;
                }

            } while (false);
        }

        $product->setActionConfigurator($configurator);

        $this->items[$product->getId()] = array(
            'product' => $product,
            'action'  => $action,
        );

        return true;
    }

    public function deleteProduct($product)
    {
        if (isset($this->items[$product->getId()])) {
            unset($this->items[$product->getId()]);
            return true;
        }

        return false;
    }

    // ---------------------------------------

    /**
     * @param $product
     * @param $action
     * @param Ess_M2ePro_Model_Listing_Product_Action_Configurator $configurator
     * @return bool
     */
    public function isExistProduct($product,
                                   $action,
                                   Ess_M2ePro_Model_Listing_Product_Action_Configurator $configurator)
    {
        if (!isset($this->items[$product->getId()]) || $this->items[$product->getId()]['action'] != $action) {
            return false;
        }

        /** @var Ess_M2ePro_Model_Listing_Product_Action_Configurator $existedConfigurator */
        $existedConfigurator = $this->items[$product->getId()]['product']->getActionConfigurator();

        return $existedConfigurator->isDataConsists($configurator) &&
               $existedConfigurator->isParamsConsists($configurator);
    }

    public function resetProducts()
    {
        $this->items = array();
    }

    //########################################

    public function execute()
    {
        $this->setPercents($this->getPercentsStart());

        $actions = array(
            Ess_M2ePro_Model_Listing_Product::ACTION_STOP,
            Ess_M2ePro_Model_Listing_Product::ACTION_RELIST,
            Ess_M2ePro_Model_Listing_Product::ACTION_REVISE,
            Ess_M2ePro_Model_Listing_Product::ACTION_LIST
        );

        $results = array();

        $iteration = 0;
        $percentsForOneIteration = $this->getPercentsInterval() / count($actions);

        foreach ($actions as $action) {

            $tempResults = $this->executeAction($action,
                                                $this->getPercentsStart() + $iteration*$percentsForOneIteration,
                                                $this->getPercentsStart() + (++$iteration)*$percentsForOneIteration);

            $results = array_merge($results,$tempResults);
        }

        $this->setPercents($this->getPercentsEnd());
        return Mage::helper('M2ePro')->getMainStatus($results);
    }

    public function executeAction($action, $percentsFrom, $percentsTo)
    {
        $this->setPercents($percentsFrom);

        $products = $this->getActionProducts($action);
        if (empty($products)) {
            return array();
        }

        $totalProductsCount = count($products);
        $processedProductsCount = 0;

        $percentsOneProduct = ($percentsTo - $percentsFrom)/$totalProductsCount;

        $results = array();

        foreach (array_chunk($products, $this->getMaxProductsPerStep()) as $stepProducts) {

            $countString = Mage::helper('M2ePro')->__('%perStep% from %total% Product(s).',
                                                      count($stepProducts), $totalProductsCount);

            if (count($stepProducts) < 10) {

                $productsIds = array();
                foreach ($stepProducts as $product) {
                    $productsIds[] = $product->getProductId();
                }

                $productsIds = implode('", "',$productsIds);
                $countString = Mage::helper('M2ePro')->__('Product(s) with ID(s)')." \"{$productsIds}\".";
            }

            $this->setStatus(Ess_M2ePro_Model_Listing_Product::getActionTitle($action).
                             ' '.$countString.
                             ' '.Mage::helper('M2ePro')->__('Please wait...'));

            $results[] = Mage::getModel('M2ePro/'.$this->getConnectorModel())->process(
                $action, $stepProducts,
                array('status_changer' => Ess_M2ePro_Model_Listing_Product::STATUS_CHANGER_SYNCH)
            );

            $processedProductsCount += count($stepProducts);
            $tempPercents = $percentsFrom + ($processedProductsCount * $percentsOneProduct);

            $this->setPercents($tempPercents > $percentsTo ? $percentsTo : $tempPercents);
            $this->activate();
        }

        $this->setPercents($percentsTo);
        return $results;
    }

    //########################################

    private function getActionProducts($action)
    {
        $resultProducts = array();

        foreach ($this->items as $item) {
            if ($item['action'] != $action) {
                continue;
            }

            $resultProducts[] = $item['product'];
        }

        return $resultProducts;
    }

    // ---------------------------------------

    private function setPercents($value)
    {
        if (!$this->getLockItem()) {
            return;
        }

        $this->getLockItem()->setPercents($value);
    }

    private function setStatus($text)
    {
        if (!$this->getLockItem()) {
            return;
        }

        $this->getLockItem()->setStatus($text);
    }

    private function activate()
    {
        if (!$this->getLockItem()) {
            return;
        }

        $this->getLockItem()->activate();
    }

    // ---------------------------------------

    private function getPercentsInterval()
    {
        return $this->getPercentsEnd() - $this->getPercentsStart();
    }

    //########################################
}