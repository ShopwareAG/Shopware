<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */

namespace Shopware\Bundle\StoreFrontBundle\Gateway\DBAL\Hydrator;

use Shopware\Bundle\StoreFrontBundle\Struct;
use Shopware\Components\Thumbnail\Manager;
use Shopware\Models;

/**
 * @category  Shopware
 * @package   Shopware\Bundle\StoreFrontBundle\Gateway\DBAL\Hydrator
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class MediaHydrator extends Hydrator
{
    /**
     * @var AttributeHydrator
     */
    private $attributeHydrator;

    /**
     * @var Manager
     */
    private $thumbnailManager;

    /**
     * @param AttributeHydrator $attributeHydrator
     * @param \Shopware\Components\Thumbnail\Manager $thumbnailManager
     */
    public function __construct(AttributeHydrator $attributeHydrator, Manager $thumbnailManager)
    {
        $this->attributeHydrator = $attributeHydrator;
        $this->thumbnailManager = $thumbnailManager;
    }

    /**
     * @param array $data
     * @return Struct\Media
     */
    public function hydrate(array $data)
    {
        $media = new Struct\Media();

        if (isset($data['__media_id'])) {
            $media->setId((int) $data['__media_id']);
        }

        if (isset($data['__media_name'])) {
            $media->setName($data['__media_name']);
        }

        if (isset($data['__media_description'])) {
            $media->setDescription($data['__media_description']);
        }

        if (isset($data['__media_type'])) {
            $media->setType($data['__media_type']);
        }

        if (isset($data['__media_extension'])) {
            $media->setExtension($data['__media_extension']);
        }

        if (isset($data['__media_path'])) {
            $media->setFile($data['__media_path']);
        }

        if ($media->getType() == Models\Media\Media::TYPE_IMAGE
            && $data['__mediaSettings_create_thumbnails']) {
            $media->setThumbnails(
                $this->getMediaThumbnails($data)
            );
        }

        if (!empty($data['__mediaAttribute_id'])) {
            $attribute = $this->attributeHydrator->hydrate(
                $this->extractFields('__mediaAttribute_', $data)
            );
            $media->addAttribute('media', $attribute);
        }

        return $media;
    }

    /**
     * @param array $data
     * @return \Shopware\Bundle\StoreFrontBundle\Struct\Media
     */
    public function hydrateProductImage(array $data)
    {
        $media = $this->hydrate($data);

        $data = array_merge($data, $this->getImageTranslation($data));

        $media->setName($data['__image_description']);

        $media->setPreview((bool) ($data['__image_main'] == 1));

        if (!empty($data['__imageAttribute_id'])) {
            $attribute = $this->attributeHydrator->hydrate(
                $this->extractFields('__imageAttribute_', $data)
            );

            $media->addAttribute('image', $attribute);
        }

        return $media;
    }

    /**
     * @param array $data Contains the array data for the media
     * @return array
     */
    private function getMediaThumbnails(array $data)
    {
        return $this->thumbnailManager->getMediaThumbnails(
            $data['__media_name'],
            $data['__media_type'],
            $data['__media_extension'],
            explode(';', $data['__mediaSettings_thumbnail_size'])
        );
    }

    /**
     * @param $data
     * @return array
     */
    private function getImageTranslation($data)
    {
        if (!isset($data['__image_translation'])
            || empty($data['__image_translation'])
        ) {
            $translation = [];
        } else {
            $translation = unserialize($data['__image_translation']);
        }

        if (isset($data['__image_translation_fallback'])
            && !empty($data['__image_translation_fallback'])
        ) {
            $fallbackTranslation = unserialize($data['__image_translation_fallback']);
            $translation += $fallbackTranslation;
        }

        if (empty($translation)) {
            return [];
        }

        return [
            '__image_description' => $translation['description']
        ];
    }
}
