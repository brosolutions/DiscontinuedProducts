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

namespace BroSolutions\DiscontinuedProducts\Plugin\Model\Product\Type;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Type\AbstractType;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Phrase;
use Magento\Store\Model\ScopeInterface;

/**
 * A configurable (or bundle/grouped) product's own isSalable() only reflects whether it has
 * ANY salable child - it does not know which specific child variant the shopper selected.
 * Magento\ConfigurableProduct\Model\Product\Type\Configurable::_prepareProduct() resolves that
 * child without ever consulting its isSalable() flag, so
 * DisableSalableForDiscontinuedProduct's event-based block never runs for it. This plugin
 * catches it at the one place every add-to-cart path (storefront, admin order, API, Quick
 * Order) funnels through: the resolved product list right before Quote::addProduct() decides
 * whether to proceed.
 *
 * @copyright  Copyright (c) 2026 BroSolutions
 * @link       https://www.brosolutions.net/
 */
class BlockDiscontinuedFromCart
{
    /**
     * @var string
     */
    private const CONFIG_PATH_ENABLE = 'brosolutions_discontinued_products/general/enable';

    /**
     * @var string
     */
    private const CONFIG_PATH_MESSAGE = 'brosolutions_discontinued_products/general/message';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @param AbstractType $subject
     * @param array|string $result
     * @param DataObject $buyRequest
     * @param Product $product
     * @param string|null $processMode
     * @return array|string
     */
    public function afterPrepareForCartAdvanced(
        AbstractType $subject,
        $result,
        DataObject $buyRequest,
        $product,
        $processMode = null
    ) {
        if (is_string($result) || $result instanceof Phrase) {
            return $result;
        }

        if (!$this->scopeConfig->isSetFlag(self::CONFIG_PATH_ENABLE, ScopeInterface::SCOPE_STORE)) {
            return $result;
        }

        foreach ((array)$result as $item) {
            if ($item instanceof Product && $item->getData('is_discontinued')) {
                return (string)$this->scopeConfig->getValue(self::CONFIG_PATH_MESSAGE, ScopeInterface::SCOPE_STORE);
            }
        }

        return $result;
    }
}
