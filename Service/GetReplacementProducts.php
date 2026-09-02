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

namespace BroSolutions\DiscontinuedProducts\Service;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Catalog\Model\ResourceModel\Product\Collection;

/**
 * @copyright  Copyright (c) 2026 BroSolutions
 * @link       https://www.brosolutions.net/
 */
class GetReplacementProducts
{
    /**
     * @var CollectionFactory
     */
    private $productCollectionFactory;

    /**
     * @param CollectionFactory $productCollectionFactory
     */
    public function __construct(
        CollectionFactory $productCollectionFactory
    ) {
        $this->productCollectionFactory = $productCollectionFactory;
    }

    /**
     * @param $product
     * @return Collection
     */
    public function execute($product)
    {
        if (!$this->isDiscontinued($product)) {
            return null;
        }

        $links = $product->getProductLinks();

        $linkedProductSkus = [];

        foreach ($links as $link) {
            if ($link->getLinkType() === 'replacement') {
                $linkedProductSkus[] = $link['linked_product_sku'];
            }
        }

        $collection = $this->productCollectionFactory->create();
        $collection->setStoreId($product->getStoreId());
        $collection->addStoreFilter($product->getStoreId());
        $collection->addAttributeToSelect('*');

        if (!empty($linkedProductSkus)) {
            $collection->addAttributeToFilter('sku', ['in' => $linkedProductSkus]);
        } else {
            // No replacement links configured - without an explicit filter here, an
            // unfiltered collection would match every product in the catalog.
            $collection->addFieldToFilter('entity_id', ['eq' => -1]);
        }

        return $collection;
    }

    /**
     * @param ProductInterface $product
     * @return bool
     */
    private function isDiscontinued(ProductInterface $product): bool
    {
        return (bool)$product->getData('is_discontinued');
    }
}
