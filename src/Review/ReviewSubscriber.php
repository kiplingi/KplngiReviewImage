<?php

namespace Kplngi\ReviewImage\Review;

use Kplngi\ReviewImage\Service\ReviewImageService;
use Shopware\Core\Framework\DataAbstractionLayer\Event\EntitySearchResultLoadedEvent;
use Shopware\Core\Framework\DataAbstractionLayer\Event\EntityWrittenEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ReviewSubscriber implements EventSubscriberInterface
{
    private ReviewImageService $reviewImageService;

    public function __construct(
        ReviewImageService $reviewImageService
    )
    {
        $this->reviewImageService = $reviewImageService;
    }

    public static function getSubscribedEvents()
    {
        return [
            'product_review.written' => 'reviewWritten',
            'product_review.search.result.loaded' => 'extendProductReview'
        ];
    }

    public function reviewWritten(EntityWrittenEvent $event): void
    {
        if (
            $event->getWriteResults()[0]->getOperation() === 'insert' ||
            $event->getWriteResults()[0]->getOperation() === 'update'
        ) {
            $this->reviewImageService->saveReviewMedia($event);
        }
    }

    public function extendProductReview(EntitySearchResultLoadedEvent $event): void
    {
        $this->reviewImageService->addReviewMedia($event->getResult(), $event->getContext());
    }
}
