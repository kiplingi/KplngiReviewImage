<?php declare(strict_types=1);

namespace Kplngi\ReviewImage\Service;

use Kplngi\ReviewImage\KplngiReviewImage;
use Kplngi\ReviewImage\Review\ReviewMediaEntity;
use Shopware\Core\Content\Media\File\FileSaver;
use Shopware\Core\Content\Media\File\MediaFile;
use Shopware\Core\Content\Media\MediaCollection;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Event\EntityWrittenEvent;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\EntitySearchResult;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsAnyFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Util\Random;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RequestStack;

class ReviewImageService
{
    private EntityRepository $mediaRepository;
    private FileSaver $fileSaver;
    private RequestStack $requestStack;
    private EntityRepository $reviewImageRepository;
    private SystemConfigService $configService;

    public function __construct(
        EntityRepository $mediaRepository,
        FileSaver                 $fileSaver,
        RequestStack              $requestStack,
        EntityRepository $reviewImageRepository,
        SystemConfigService       $configService
    )
    {
        $this->mediaRepository = $mediaRepository;
        $this->fileSaver = $fileSaver;
        $this->requestStack = $requestStack;
        $this->reviewImageRepository = $reviewImageRepository;
        $this->configService = $configService;
    }

    public function saveReviewMedia(EntityWrittenEvent $event): void
    {
        if (count($event->getIds()) === 0) {
            return;
        }

        $reviewId = $event->getIds()[0];
        $context = $event->getContext();

        $mediaId = $this->createMediaFileFromUpload($context);

        if ($mediaId === null) {
            return;
        }

        $reviewMediaEntity = $this->getReviewMedia($reviewId, $context);

        if ($reviewMediaEntity) {
            $this->reviewImageRepository->delete([['id' => $reviewMediaEntity->getId()]], $context);
        }

        $this->createReviewMedia($reviewId, $mediaId, $context);
    }

    public function addReviewMedia(EntitySearchResult $reviewCollection, Context $context): void
    {
        $reviews = $reviewCollection->getElements();
        $reviewIds = $reviewCollection->getIds();

        $criteria = new Criteria();
        $criteria->addFilter(new EqualsAnyFilter('reviewId', $reviewIds));
        $criteria->addAssociation('media');

        $reviewMedia = $this->reviewImageRepository->search(
            $criteria,
            $context
        );

        foreach ($reviews as $review) {
            foreach ($reviewMedia->getElements() as $reviewMediaItem) {
                if ($review->getId() === $reviewMediaItem->getReviewId()) {
                    $mediaCollection = new MediaCollection();
                    $mediaCollection->add($reviewMediaItem->get('media'));
                    $review->addExtension('kplngiMediaItem', $mediaCollection);
                }
            }
        }
    }

    private function createMediaFileFromUpload(Context $context): ?string
    {
        $request = $this->requestStack->getCurrentRequest();
        if ($request === null) {
            return null;
        }

        $uploadedFile = $request->files->get('reviewImageUpload');
        if ($uploadedFile === null) {
            return null;
        }

        if (!($uploadedFile instanceof UploadedFile)) {
            return null;
        }

        if (!$this->isImage($uploadedFile)) {
            return null;
        }

        if ($uploadedFile->getSize() > (1024 * 1024 * 2)) {
            return null;
        }

        $filePath = $uploadedFile->getRealPath();

        if (!$filePath) {
            return null;
        }

        $mediaFiles = new MediaFile(
            $filePath,
            mime_content_type($filePath),
            $uploadedFile->getClientOriginalExtension(),
            $uploadedFile->getSize()
        );

        return $this->saveMediaFile($mediaFiles, $context, $uploadedFile);
    }

    private function createReviewMedia(string $reviewId, string $mediaId, Context $context): void
    {
        $this->reviewImageRepository->create([
            [
                'id' => Uuid::randomHex(),
                'mediaId' => $mediaId,
                'reviewId' => $reviewId
            ]
        ], $context);
    }

    private function saveMediaFile(MediaFile $mediaFile, Context $context, UploadedFile $uploadedFile): string
    {
        $mediaId = Uuid::randomHex();
        $this->mediaRepository->create(
            [
                [
                    'id' => $mediaId,
                    'private' => false,
                    'mediaFolderId' => $this->getMediaFolderId(),
                ]
            ],
            $context
        );

        $this->fileSaver->persistFileToMedia(
            $mediaFile,
            pathinfo($uploadedFile->getClientOriginalName())['filename'] . Random::getAlphanumericString(6),
            $mediaId,
            $context
        );

        return $mediaId;
    }

    private function getMediaFolderId(): ?string
    {
        $mediaFolderId = $this->configService->get(KplngiReviewImage::MEDIA_FOLDER_ID_KEY);
        return $mediaFolderId ? (string)$mediaFolderId : null;
    }

    private function isImage(UploadedFile $uploadedFile): bool
    {
        $supportedMimeType = [
            'image/gif',
            'image/jpeg',
            'image/png'
        ];

        $mimeType = mime_content_type($uploadedFile->getRealPath());

        return in_array($mimeType, $supportedMimeType);
    }

    private function getReviewMedia(string $reviewId, Context $context): ?ReviewMediaEntity
    {
        $criteria = (new Criteria())->addFilter(new EqualsFilter('reviewId', $reviewId));

        $reviewImageSearchResult = $this->reviewImageRepository->search($criteria, $context);

        if ($reviewImageSearchResult->getTotal() > 0) {
            return $reviewImageSearchResult->first();
        }

        return null;
    }
}
