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

namespace BroSolutions\DiscontinuedProducts\Observer;

use Magento\Catalog\Model\Product;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * A discontinued product is never purchasable, whether or not it also redirects
 * elsewhere - this is what makes the "Add to Cart" button disappear (Luma's
 * addtocart.phtml/form.phtml check $product->isSaleable()) and what makes
 * Magento\Quote\Model\Quote::addProduct() reject a direct add-to-cart attempt.
 *
 * @copyright  Copyright (c) 2026 BroSolutions
 * @link       https://www.brosolutions.net/
 */
class DisableSalableForDiscontinuedProduct implements ObserverInterface
{
    /**
     * @var string
     */
    private const CONFIG_PATH_ENABLE = 'brosolutions_discontinued_products/general/enable';

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
     * @inheritDoc
     */
    public function execute(Observer $observer): void
    {
        /** @var Product $product */
        $product = $observer->getEvent()->getData('product');

        if (!$product->getData('is_discontinued')) {
            return;
        }

        if (!$this->scopeConfig->isSetFlag(self::CONFIG_PATH_ENABLE, ScopeInterface::SCOPE_STORE)) {
            return;
        }

        /** @var DataObject $salableObject */
        $salableObject = $observer->getEvent()->getData('salable');
        $salableObject->setIsSalable(false);
    }
}
