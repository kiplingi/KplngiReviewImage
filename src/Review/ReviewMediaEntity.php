<?php declare(strict_types=1);

namespace Kplngi\ReviewImage\Review;

use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;

class ReviewMediaEntity extends Entity
{
    use EntityIdTrait;

    protected string $reviewId;

    protected string $mediaId;

    public function getReviewId(): string
    {
        return $this->reviewId;
    }

    public function setReviewId(string $reviewId): void
    {
        $this->reviewId = $reviewId;
    }

    public function getMediaId(): string
    {
        return $this->mediaId;
    }

    public function setMediaId(string $mediaId): void
    {
        $this->mediaId = $mediaId;
    }


}