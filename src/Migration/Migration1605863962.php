<?php declare(strict_types=1);

namespace Kplngi\ReviewImage\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1605863962 extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1605863962;
    }

    public function update(Connection $connection): void
    {
        $connection->executeStatement('
            CREATE TABLE IF NOT EXISTS `kplngi_review_image_media` (
                `id` BINARY(16) NOT NULL,
                `review_id` BINARY(16) NOT NULL,
                `media_id` BINARY(16) NOT NULL,
                `created_at` DATETIME(3) NOT NULL,
                `updated_at` DATETIME(3) NULL,
              PRIMARY KEY (`id`),
              CONSTRAINT `fk.kplngi_review_image_media.review_id` FOREIGN KEY (`review_id`)
                REFERENCES `product_review` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
              CONSTRAINT `fk.kplngi_review_image_media.media_id` FOREIGN KEY (`media_id`)
                REFERENCES `media` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');
    }

    public function updateDestructive(Connection $connection): void
    {
    }
}
