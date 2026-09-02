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

namespace BroSolutions\DiscontinuedProducts\Plugin\Controller\Product\View;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Controller\Product\View;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * Sends the shopper straight to the replacement product's page instead of rendering a
 * discontinued product's own page, when a valid redirect SKU is configured.
 *
 * @copyright  Copyright (c) 2026 BroSolutions
 * @link       https://www.brosolutions.net/
 */
class RedirectDiscontinuedProduct
{
    /**
     * @var string
     */
    private const CONFIG_PATH_ENABLE = 'brosolutions_discontinued_products/general/enable';

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var RedirectFactory
     */
    private $redirectFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @param ProductRepositoryInterface $productRepository
     * @param StoreManagerInterface $storeManager
     * @param RedirectFactory $redirectFactory
     * @param LoggerInterface $logger
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        StoreManagerInterface $storeManager,
        RedirectFactory $redirectFactory,
        LoggerInterface $logger,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->productRepository = $productRepository;
        $this->storeManager = $storeManager;
        $this->redirectFactory = $redirectFactory;
        $this->logger = $logger;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @param View $subject
     * @param callable $proceed
     * @return ResultInterface|ResponseInterface
     */
    public function aroundExecute(View $subject, callable $proceed)
    {
        $productId = (int)$subject->getRequest()->getParam('id');
        $redirectUrl = $productId ? $this->getRedirectUrl($productId) : null;

        if ($redirectUrl === null) {
            return $proceed();
        }

        return $this->redirectFactory->create()->setUrl($redirectUrl);
    }

    /**
     * Resolve the redirect target URL for a discontinued product, if applicable.
     *
     * @param int $productId
     * @return string|null
     */
    private function getRedirectUrl(int $productId): ?string
    {
        if (!$this->scopeConfig->isSetFlag(self::CONFIG_PATH_ENABLE, ScopeInterface::SCOPE_STORE)) {
            return null;
        }

        try {
            $storeId = $this->storeManager->getStore()->getId();
            $product = $this->productRepository->getById($productId, false, $storeId);

            if (!$product->getData('is_discontinued') || !$product->getData('discontinued_redirect_enabled')) {
                return null;
            }

            $redirectSku = trim((string)$product->getData('discontinued_redirect_sku'));
            if ($redirectSku === '' || $redirectSku === $product->getSku()) {
                return null;
            }

            $targetProduct = $this->productRepository->get($redirectSku, false, $storeId);

            return $targetProduct->getProductUrl();
        } catch (NoSuchEntityException $e) {
            // Misconfigured redirect (target SKU no longer exists) - fall back to
            // rendering the discontinued product's own page instead of a broken redirect.
            $this->logger->warning(
                sprintf('Discontinued product %d has an invalid redirect SKU configured.', $productId)
            );
            return null;
        }
    }
}
