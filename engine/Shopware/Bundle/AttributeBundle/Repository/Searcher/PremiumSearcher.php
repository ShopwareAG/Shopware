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

namespace Shopware\Bundle\AttributeBundle\Repository\Searcher;

use Shopware\Bundle\AttributeBundle\Repository\SearchCriteria;
use Shopware\Models\Premium\Premium;

/**
 * @category  Shopware
 *
 * @copyright Copyright (c) shopware AG (http://www.shopware.com)
 */
class PremiumSearcher extends GenericSearcher
{
    /**
     * {@inheritdoc}
     */
    protected function createQuery(SearchCriteria $criteria)
    {
        $query = $this->entityManager->createQueryBuilder();
        $query->select($this->getIdentifierField());
        $query->from(Premium::class, 'entity');
        $query->innerJoin('entity.articleDetail', 'variant');
        $query->innerJoin('variant.article', 'article');
        $query->leftJoin('entity.shop', 's');

        return $query;
    }

    /**
     * @param SearchCriteria $criteria
     *
     * @return array
     */
    protected function getSearchFields(SearchCriteria $criteria)
    {
        return [
            'entity.orderNumberExport',
            'variant.number',
            'article.name',
            's.name',
        ];
    }
}
