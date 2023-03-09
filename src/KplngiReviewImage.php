<?php

namespace Kplngi\ReviewImage;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Plugin;
use Shopware\Core\Framework\Plugin\Context\InstallContext;
use Shopware\Core\Framework\Plugin\Context\UninstallContext;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\SystemConfig\SystemConfigService;

class KplngiReviewImage extends Plugin
{
    public const MEDIA_FOLDER_NAME = "Produkt Review Bilder";
    public const MEDIA_FOLDER_ID_KEY = "KplngiReviewImage.settings.mediaFolderId";

    public function install(InstallContext $context): void
    {
        $mediaFolderId = Uuid::randomHex();

        $this->createConfiguration($mediaFolderId);
        $this->createMediaFolder($mediaFolderId, $context);
    }

    public function uninstall(UninstallContext $uninstallContext): void
    {
        parent::uninstall($uninstallContext);

        if ($uninstallContext->keepUserData()) {
            return;
        }

        $this->removeTable();
        $this->removeMediaFolder($uninstallContext->getContext());
        $this->removeConfiguration($uninstallContext->getContext());
    }

    private function removeMediaFolder(Context $context): void
    {
        $mediaFolderRepository = $this->container->get('media_folder.repository');

        $systemConfigRepository = $this->container->get('system_config.repository');

        $criteria = (new Criteria())->addFilter(new EqualsFilter('configurationKey', self::MEDIA_FOLDER_ID_KEY));
        $mediaFolderConfig = $systemConfigRepository->search($criteria, $context)->first();

        if ($mediaFolderConfig === null) {
            return;
        }

        $mediaFolderRepository->delete([
            ['id' => $mediaFolderConfig->getConfigurationValue()]
        ], $context);
    }

    private function removeConfiguration(Context $context): void
    {
        $systemConfigRepository = $this->container->get('system_config.repository');

        $criteria = (new Criteria())->addFilter(new EqualsFilter('configurationKey', self::MEDIA_FOLDER_ID_KEY));
        $configurationIdSearchResult = $systemConfigRepository->searchIds($criteria, $context);

        $configurationIds = [];
        foreach ($configurationIdSearchResult->getData() as $idResult) {
            $configurationIds[] = $idResult;
        }

        $systemConfigRepository->delete($configurationIds, $context);
    }

    private function removeTable(): void
    {
        $connection = $this->container->get(Connection::class);
        if ($connection == null) {
            return;
        }
        $connection->executeStatement('DROP TABLE IF EXISTS `kplngi_review_image_media`');
    }

    private function createConfiguration(string $mediaFolderId): void
    {
        /** @var SystemConfigService $systemConfigService */
        $systemConfigService = $this->container->get(SystemConfigService::class);

        $systemConfigService->set(self::MEDIA_FOLDER_ID_KEY, $mediaFolderId);
    }

    private function createMediaFolder($mediaFolderId, InstallContext $installContext): void
    {
        $context = $installContext->getContext();

        $mediaFolderRepo = $this->container->get('media_folder.repository');
        $thumbnailSizesRepo = $this->container->get('media_thumbnail_size.repository');

        $thumbnailCriteria = new Criteria();
        $thumbnails = $thumbnailSizesRepo->searchIds($thumbnailCriteria, $context);

        $thumbnailIds = [];
        foreach ($thumbnails->getData() as $thumbnail) {
            $thumbnailIds[] = $thumbnail;
        }

        $mediaFolderRepo->create([
            [
                'id' => $mediaFolderId,
                'useParentConfiguration' => false,
                'name' => self::MEDIA_FOLDER_NAME,
                'configuration' => [
                    'createThumbnails' => true,
                    'mediaThumbnailSizes' => $thumbnailIds
                ]
            ]
        ], $context);
    }
}
