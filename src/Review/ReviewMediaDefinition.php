<?php declare(strict_types=1);

namespace Kplngi\ReviewImage\Review;

use Shopware\Core\Content\Media\MediaDefinition;
use Shopware\Core\Content\Product\Aggregate\ProductReview\ProductReviewDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\CreatedAtField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\CascadeDelete;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\OneToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class ReviewMediaDefinition extends EntityDefinition
{
    public const ENTITY_NAME = 'kplngi_review_image_media';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getEntityClass(): string
    {
        return ReviewMediaEntity::class;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', 'id'))->addFlags(new PrimaryKey(), new Required()),
            (new FkField('review_id', 'reviewId', ProductReviewDefinition::class))->addFlags(new Required()),
            (new FkField('media_id', 'mediaId', MediaDefinition::class))->addFlags(new Required()),
            (new OneToOneAssociationField('review', 'review_id', 'id', ProductReviewDefinition::class, false))->addFlags(new CascadeDelete()),
            (new OneToOneAssociationField('media', 'media_id', 'id', MediaDefinition::class, false))->addFlags(new CascadeDelete()),
            new CreatedAtField()
        ]);
    }
}
