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

namespace BroSolutions\DiscontinuedProducts\Model\ProductLink\CollectionProvider;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ProductLink\CollectionProviderInterface;
use Magento\Catalog\Model\ResourceModel\Product\Link as LinkResource;
use Exception;

/**
 * @copyright  Copyright (c) 2026 BroSolutions
 * @link       https://www.brosolutions.net/
 */
class Replacement implements CollectionProviderInterface
{
    /**
     * @var LinkResource
     */
    private $linkResource;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @param LinkResource $linkResource
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(
        LinkResource $linkResource,
        ProductRepositoryInterface $productRepository
    ) {
        $this->linkResource = $linkResource;
        $this->productRepository = $productRepository;
    }

    /**
     * @param Product $product
     * @return array|Product[]
     */
    public function getLinkedProducts(Product $product)
    {
        $connection = $this->linkResource->getConnection();
        $linkTable = $this->linkResource->getTable('catalog_product_link');
        $linkTypeTable = $this->linkResource->getTable('catalog_product_link_type');

        $linkTypeId = $connection->fetchOne(
            $connection->select()
                ->from($linkTypeTable, ['link_type_id'])
                ->where('code = ?', 'replacement')
        );

        if (!$linkTypeId) {
            return [];
        }

        $rows = $connection->fetchAll(
            $connection->select()
                ->from($linkTable)
                ->where('product_id = ?', $product->getId())
                ->where('link_type_id = ?', $linkTypeId)
        );

        $products = [];

        foreach ($rows as $row) {
            try {
                $linkedProduct = $this->productRepository->getById(
                    $row['linked_product_id'],
                    false,
                    $product->getStoreId()
                );
                $products[] = $linkedProduct;
            } catch (Exception $e) {
                continue;
            }
        }

        return $products;
    }
}
