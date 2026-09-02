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

namespace BroSolutions\DiscontinuedProducts\Block\Product\View;

use Magento\Catalog\Block\Product\AbstractProduct;
use Magento\Catalog\Block\Product\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Shown on a discontinued product's own page in place of "Add to Cart".
 *
 * @copyright  Copyright (c) 2026 BroSolutions
 * @link       https://www.brosolutions.net/
 */
class DiscontinuedNotice extends AbstractProduct
{
    /**
     * @var string
     */
    private const CONFIG_PATH_MESSAGE = 'brosolutions_discontinued_products/general/message';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @param Context $context
     * @param ScopeConfigInterface $scopeConfig
     * @param array $data
     */
    public function __construct(
        Context $context,
        ScopeConfigInterface $scopeConfig,
        array $data = []
    ) {
        $this->scopeConfig = $scopeConfig;
        parent::__construct($context, $data);
    }

    /**
     * Whether the current product should show the discontinued notice.
     *
     * @return bool
     */
    public function isDiscontinued(): bool
    {
        $product = $this->getProduct();
        return $product && (bool)$product->getData('is_discontinued');
    }

    /**
     * Get the configured discontinued message.
     *
     * @return string
     */
    public function getMessage(): string
    {
        return (string)$this->scopeConfig->getValue(
            self::CONFIG_PATH_MESSAGE,
            ScopeInterface::SCOPE_STORE
        );
    }
}
