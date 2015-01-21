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

namespace Shopware\Bundle\SearchBundle\FacetResult;

use Shopware\Bundle\SearchBundle\FacetResultInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\Attribute;
use Shopware\Bundle\StoreFrontBundle\Struct\Extendable;

/**
 * @category  Shopware
 * @package   Shopware\Bundle\SearchBundle\FacetResult
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class FacetResultGroup
    extends Extendable
    implements FacetResultInterface
{
    /**
     * @var FacetResultInterface[]
     */
    private $facetResults;

    /**
     * @var string
     */
    private $facetName;

    /**
     * @var string
     */
    private $label;

    /**
     * @param FacetResultInterface[] $facetResults
     * @param string|null $headline
     * @param string $facetName
     * @param Attribute[] $attributes
     */
    public function __construct($facetResults, $headline, $facetName, $attributes = [])
    {
        $this->facetResults = $facetResults;
        $this->label = $headline;
        $this->facetName = $facetName;
        $this->attributes = $attributes;
    }

    /**
     * @return string
     */
    public function getFacetName()
    {
        return $this->facetName;
    }

    /**
     * @return boolean
     */
    public function isActive()
    {
        return false;
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @return FacetResultInterface[]
     */
    public function getFacetResults()
    {
        return $this->facetResults;
    }
}
