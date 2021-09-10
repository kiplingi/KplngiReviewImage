<?php declare(strict_types=1);

namespace Kplngi\ReviewImage\Product;

use Shopware\Core\Framework\Struct\ArrayStruct;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Shopware\Storefront\Page\Product\ProductPageLoadedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PageLoaded implements EventSubscriberInterface
{
    private SystemConfigService $systemConfigService;

    public function __construct(SystemConfigService $systemConfigService)
    {

        $this->systemConfigService = $systemConfigService;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ProductPageLoadedEvent::class => 'onProductPageLoaded'
        ];
    }

    public function onProductPageLoaded(ProductPageLoadedEvent $event): void
    {
        $isActive = $this->systemConfigService->get('KplngiReviewImage.config.salesChannelActive', $event->getSalesChannelContext()->getSalesChannelId());

        if ($isActive) {
            $event->getPage()->addExtension('kplngiReviewImageActive', new ArrayStruct());
        }
    }
}