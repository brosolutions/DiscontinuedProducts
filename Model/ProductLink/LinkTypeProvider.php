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

namespace BroSolutions\DiscontinuedProducts\Model\ProductLink;

use Magento\Catalog\Model\Product\LinkTypeProvider as CoreProvider;
use Magento\Framework\App\ResourceConnection;
use Magento\Catalog\Api\Data\ProductLinkTypeInterfaceFactory;
use Magento\Catalog\Api\Data\ProductLinkAttributeInterfaceFactory;
use \Magento\Catalog\Model\Product\LinkFactory;

/**
 * @copyright  Copyright (c) 2026 BroSolutions
 * @link       https://www.brosolutions.net/
 */
class LinkTypeProvider extends CoreProvider
{
    /**
     * @param ResourceConnection $resource
     * @param ProductLinkTypeInterfaceFactory $linkTypeFactory
     * @param ProductLinkAttributeInterfaceFactory $linkAttributeFactory
     * @param LinkFactory $linkFactory
     * @param array $linkTypes
     */
    public function __construct(
        ResourceConnection $resource,
        ProductLinkTypeInterfaceFactory $linkTypeFactory,
        ProductLinkAttributeInterfaceFactory $linkAttributeFactory,
        LinkFactory $linkFactory,
        array $linkTypes = []
    ) {
        $connection = $resource->getConnection();
        $table = $resource->getTableName('catalog_product_link_type');

        $replacementId = (int) $connection->fetchOne(
            "SELECT link_type_id FROM {$table} WHERE code = ?",
            ['replacement']
        );

        $linkTypes['replacement'] = $replacementId;

        parent::__construct($linkTypeFactory, $linkAttributeFactory, $linkFactory, $linkTypes);
    }
}
