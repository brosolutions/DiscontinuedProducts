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

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * @copyright  Copyright (c) 2026 BroSolutions
 * @link       https://www.brosolutions.net/
 */
class ValidateRedirectSku implements ObserverInterface
{
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(ProductRepositoryInterface $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    /**
     * @inheritDoc
     */
    public function execute(Observer $observer): void
    {
        /** @var Product $product */
        $product = $observer->getEvent()->getData('product');

        if (!$product->getData('discontinued_redirect_enabled')) {
            return;
        }

        $redirectSku = trim((string)$product->getData('discontinued_redirect_sku'));

        if ($redirectSku === '') {
            throw new LocalizedException(
                __('Enter a SKU to redirect to, or turn off "Redirect to Another Product".')
            );
        }

        if ($redirectSku === $product->getSku()) {
            throw new LocalizedException(
                __('A product cannot redirect to itself. Enter a different SKU to redirect to.')
            );
        }

        try {
            $this->productRepository->get($redirectSku, false, $product->getStoreId());
        } catch (NoSuchEntityException $e) {
            throw new LocalizedException(
                __('No product found with SKU "%1" to redirect to.', $redirectSku)
            );
        }
    }
}
