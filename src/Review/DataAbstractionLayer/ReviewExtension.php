<?php declare(strict_types=1);

namespace Kplngi\ReviewImage\Review\DataAbstractionLayer;

use Kplngi\ReviewImage\Review\ReviewMediaDefinition;
use Shopware\Core\Content\Product\Aggregate\ProductReview\ProductReviewDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityExtension;
use Shopware\Core\Framework\DataAbstractionLayer\Field\OneToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class ReviewExtension extends EntityExtension
{
    public function getDefinitionClass(): string
    {
        return ProductReviewDefinition::class;
    }

    public function extendFields(FieldCollection $collection): void
    {
        $collection->add(
            (new OneToOneAssociationField(
                'kplngiMedia',
                'id',
                'review_id',
                ReviewMediaDefinition::class,
                false
            ))
        );
    }
}
