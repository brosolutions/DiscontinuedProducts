<?php
/**
 * Copyright (c) 2026 BroSolutions
 * All rights reserved
 *
 * This product includes proprietary software developed at BroSolutions, Ukraine
 * For more information see https://www.brosolutions.net/
 *
 * To obtain a valid license for using this software please contact us at
 * contact@brosolutions.net
 */
declare(strict_types=1);

namespace BroSolutions\DiscontinuedProducts\Block\Product\Replacement;

use BroSolutions\DiscontinuedProducts\Service\GetReplacementProducts;
use Exception;
use Magento\Catalog\Block\Product\AbstractProduct;
use Magento\Catalog\Block\Product\Context;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Visibility as ProductVisibility;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Module\Manager;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * @copyright  Copyright (c) 2026 BroSolutions
 * @link       https://www.brosolutions.net/
 */
class ListProducts extends AbstractProduct implements IdentityInterface
{
    /**
     * @var Collection
     */
    protected $_itemCollection;

    /**
     * @var ProductVisibility
     */
    protected $_catalogProductVisibility;

    /**
     * @var Manager
     */
    protected $moduleManager;

    /**
     * @var CollectionFactory
     */
    private $productCollectionFactory;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var GetReplacementProducts
     */
    private $getReplacementProducts;

    /**
     * @param Context $context
     * @param ProductVisibility $catalogProductVisibility
     * @param Manager $moduleManager
     * @param CollectionFactory $productCollectionFactory
     * @param StoreManagerInterface $storeManager
     * @param LoggerInterface $logger
     * @param ResourceConnection $resourceConnection
     * @param GetReplacementProducts $getReplacementProducts
     * @param array $data
     */
    public function __construct(
        Context                   $context,
        ProductVisibility         $catalogProductVisibility,
        Manager                   $moduleManager,
        CollectionFactory         $productCollectionFactory,
        StoreManagerInterface     $storeManager,
        LoggerInterface           $logger,
        ResourceConnection        $resourceConnection,
        GetReplacementProducts $getReplacementProducts,
        array                     $data = []
    ) {

        $this->_catalogProductVisibility = $catalogProductVisibility;
        $this->moduleManager = $moduleManager;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->storeManager = $storeManager;
        $this->logger = $logger;
        $this->resourceConnection = $resourceConnection;
        $this->getReplacementProducts = $getReplacementProducts;
        parent::__construct($context, $data);
    }

    /**
     * Prepare data
     *
     * @return $this
     * @throws NoSuchEntityException
     */
    protected function _prepareData()
    {

        try {
            /* @var $product Product */
            $product = $this->getProduct();

            $this->_itemCollection = $this->getReplacementProducts->execute($product);
            if($this->_itemCollection === null){
                $this->_itemCollection = [];
                return $this;
            }

            if ($this->moduleManager->isEnabled('Magento_Checkout')) {
                $this->_addProductAttributesAndPrices($this->_itemCollection);
            }


            foreach ($this->_itemCollection as $product) {
                $product->setDoNotUseCategoryId(true);
            }

        } catch (Exception|NoSuchEntityException $exception) {
            $this->logger->error(
                $exception->getMessage(),
                $exception->getTrace()
            );
        }

        return $this;
    }

    /**
     * Before to html handler
     *
     * @return $this
     */
    protected function _beforeToHtml(): ListProducts
    {
        $this->_prepareData();
        return parent::_beforeToHtml();
    }

    /**
     * Get collection items
     *
     * @return Collection
     */
    public function getItems()
    {

        if ($this->_itemCollection === null) {
            $this->_prepareData();
        }
        return $this->_itemCollection;
    }

    /**
     * Return identifiers for produced content
     *
     * @return array
     */
    public function getIdentities(): array
    {
        $identities = [];
        foreach ($this->getItems() as $item) {
            $identities[] = $item->getIdentities();
        }
        return array_merge([], ...$identities);
    }

    /**
     * Find out if some products can be easy added to cart
     *
     * @return bool
     */
    public function canItemsAddToCart()
    {
        foreach ($this->getItems() as $item) {
            if (!$item->isComposite() && $item->isSaleable() && !$item->getRequiredOptions()) {
                return true;
            }
        }
        return false;
    }
}
