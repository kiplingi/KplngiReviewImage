<?php

namespace Kplngi\ReviewImage\Review;

use Kplngi\ReviewImage\Service\ReviewImageService;
use Shopware\Core\Content\Media\MediaCollection;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Event\EntitySearchResultLoadedEvent;
use Shopware\Core\Framework\DataAbstractionLayer\Event\EntityWrittenEvent;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\EntitySearchResult;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ReviewSubscriber implements EventSubscriberInterface
{
    private EntityRepositoryInterface $kplngiReviewImageRepository;
    private ReviewImageService $reviewImageService;

    public function __construct(
        EntityRepositoryInterface $kplngiReviewImageRepository,
        ReviewImageService        $reviewImageService
    )
    {
        $this->kplngiReviewImageRepository = $kplngiReviewImageRepository;
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
        $this->addReviewMedia($event->getResult(), $event->getContext());
    }

    private function addReviewMedia(EntitySearchResult $reviewCollection, Context $context)
    {
        $reviews = $reviewCollection->getElements();

        foreach ($reviews as $review) {
            $criteria = new Criteria();
            $criteria->addFilter(new EqualsFilter('reviewId', $review->getId()));
            $criteria->addAssociation('media');

            $reviewMedia = $this->kplngiReviewImageRepository->search(
                $criteria,
                $context
            );

            if ($reviewMedia->first() === null) {
                return;
            }

            $reviewMedia = $reviewMedia->first()->get('media');
            $mediaCollection = new MediaCollection();

            $mediaCollection->add($reviewMedia);

            $review->addExtension('kplngiMedia', $mediaCollection);
        }
    }
}
