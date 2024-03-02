<?php

namespace Calcurates\ModuleMagento\Plugin\Model\Cart;

use Magento\Catalog\Helper\Image;
use Magento\Catalog\Model\Product\Configuration\Item\ItemResolverInterface;
use Magento\Checkout\Model\Cart\ImageProvider;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\CartItemRepositoryInterface;
use Magento\Quote\Model\Quote\Item;

class CheckoutImageProvider
{
    /**
     * @var Image
     */
    private Image $imageHelper;

    /**
     * @var CartItemRepositoryInterface
     */
    private CartItemRepositoryInterface $cartItemRepository;

    /**
     * @var ItemResolverInterface
     */
    private ItemResolverInterface $itemResolver;

    /**
     * @param Image $imageHelper
     * @param CartItemRepositoryInterface $cartItemRepository
     * @param ItemResolverInterface $itemResolver
     */
    public function __construct(
        Image $imageHelper,
        CartItemRepositoryInterface $cartItemRepository,
        ItemResolverInterface $itemResolver
    ) {
        $this->imageHelper = $imageHelper;
        $this->cartItemRepository = $cartItemRepository;
        $this->itemResolver = $itemResolver;
    }

    /**
     * Add thumbnail information for bundle product children (shipped separately)
     *
     * @param ImageProvider $subject
     * @param array $result
     * @param int $cartId
     * @return array
     */
    public function afterGetImages(ImageProvider $subject, array $result, int $cartId): array
    {
        try {
            $cartItems = $this->cartItemRepository->getList($cartId);
        } catch (NoSuchEntityException $e) {
            return $result;
        }
        /** @var Item $cartItem */
        foreach ($cartItems as $cartItem) {
            if ($cartItem->getHasChildren() && $cartItem->isShipSeparately()) {
                foreach ($cartItem->getChildren() as $child) {
                    /** @var Item $child */
                    $result[$child->getItemId()] = $this->getProductImageData($child);
                }
            }
        }

        return $result;
    }

    /**
     * Get product image data
     *
     * @param Item $cartItem
     *
     * @return array
     */
    private function getProductImageData(Item $cartItem): array
    {
        $imageHelper = $this->imageHelper->init(
            $this->itemResolver->getFinalProduct($cartItem),
            'mini_cart_product_thumbnail'
        );
        return [
            'src' => $imageHelper->getUrl(),
            'alt' => $imageHelper->getLabel(),
            'width' => $imageHelper->getWidth(),
            'height' => $imageHelper->getHeight(),
        ];
    }
}
