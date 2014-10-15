<?php
/**
 * Shopware 4
 * Copyright © shopware AG
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

namespace Shopware\Bundle\SearchBundle;

/**
 * The criteria object is used for the search gateway.
 *
 * The sorting, facet and condition classes are defined global and has
 * to be compatible with all gateway engines.
 *
 * Each of this sorting, facet and condition classes are handled by their
 * own handler classes which implemented for each gateway engine.
 *
 * @category  Shopware
 * @package   Shopware\Bundle\SearchBundle
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class Criteria implements \JsonSerializable
{
    /**
     * Offset for the limitation
     * @var int
     */
    private $offset;

    /**
     * Count of result
     * @var int
     */
    private $limit;

    /**
     * @var ConditionInterface[]
     */
    private $baseConditions = array();

    /**
     * @var ConditionInterface[]
     */
    private $conditions = array();

    /**
     * @var FacetInterface[]
     */
    private $facets = array();

    /**
     * @var SortingInterface[]
     */
    private $sortings = array();

    /**
     * @param $offset
     * @return $this
     */
    public function offset($offset)
    {
        $this->offset = $offset;

        return $this;
    }

    /**
     * @param $limit
     * @return $this
     */
    public function limit($limit)
    {
        $this->limit = $limit;

        return $this;
    }

    /**
     * @return int
     */
    public function getOffset()
    {
        return $this->offset;
    }

    /**
     * @return int
     */
    public function getLimit()
    {
        return $this->limit;
    }

    /**
     * @param $name
     * @return bool
     */
    public function hasCondition($name)
    {
        if (array_key_exists($name, $this->baseConditions)) {
            return true;
        }

        return array_key_exists($name, $this->conditions);
    }

    /**
     * @param $name
     * @return bool
     */
    public function hasSorting($name)
    {
        return array_key_exists($name, $this->sortings);
    }

    /**
     * @param $name
     * @return bool
     */
    public function hasFacet($name)
    {
        return array_key_exists($name, $this->facets);
    }

    /**
     * @param FacetInterface $facet
     * @return $this
     */
    public function addFacet(FacetInterface $facet)
    {
        $this->facets[$facet->getName()] = $facet;

        return $this;
    }

    /**
     * @param ConditionInterface $condition
     * @return $this
     */
    public function addCondition(ConditionInterface $condition)
    {
        $this->conditions[$condition->getName()] = $condition;

        return $this;
    }

    /**
     * @param ConditionInterface $condition
     * @return $this
     */
    public function addBaseCondition(ConditionInterface $condition)
    {
        $this->baseConditions[$condition->getName()] = $condition;

        return $this;
    }

    /**
     * @param SortingInterface $sorting
     * @return $this
     */
    public function addSorting(SortingInterface $sorting)
    {
        $this->sortings[$sorting->getName()] = $sorting;

        return $this;
    }

    /**
     * @param $name
     * @return null|ConditionInterface
     */
    public function getCondition($name)
    {
        if (array_key_exists($name, $this->baseConditions)) {
            return $this->baseConditions[$name];
        }

        if (array_key_exists($name, $this->conditions)) {
            return $this->conditions[$name];
        }

        return null;
    }

    /**
     * @param $name
     * @return null|FacetInterface
     */
    public function getFacet($name)
    {
        return $this->facets[$name];
    }

    /**
     * @param $name
     * @return null|SortingInterface
     */
    public function getSorting($name)
    {
        return $this->sortings[$name];
    }

    /**
     * @return \Shopware\Bundle\SearchBundle\ConditionInterface[]
     */
    public function getConditions()
    {
        return array_merge(
            $this->baseConditions,
            $this->conditions
        );
    }

    /**
     * @return \Shopware\Bundle\SearchBundle\FacetInterface[]
     */
    public function getFacets()
    {
        return $this->facets;
    }

    /**
     * @return \Shopware\Bundle\SearchBundle\SortingInterface[]
     */
    public function getSortings()
    {
        return $this->sortings;
    }

    /**
     * Allows to reset the internal sorting collection.
     *
     * @return $this
     */
    public function resetSorting()
    {
        $this->sortings = array();
        return $this;
    }

    /**
     * Allows to reset the internal base condition collection.
     *
     * @return $this
     */
    public function resetBaseConditions()
    {
        $this->baseConditions = array();
        return $this;
    }

    /**
     * Allows to reset the internal condition collection.
     *
     * @return $this
     */
    public function resetConditions()
    {
        $this->conditions = array();
        return $this;
    }

    /**
     * Allows to reset the internal facet collection.
     *
     * @return $this
     */
    public function resetFacets()
    {
        $this->facets = array();
        return $this;
    }

    /**
     * Removes a condition of the current criteria object.
     *
     * @param $name
     */
    public function removeCondition($name)
    {
        if (array_key_exists($name, $this->conditions)) {
            unset($this->conditions[$name]);
        }
    }

    /**
     * Removes a base condition of the current criteria object.
     *
     * @param $name
     */
    public function removeBaseCondition($name)
    {
        if (array_key_exists($name, $this->baseConditions)) {
            unset($this->baseConditions[$name]);
        }
    }

    /**
     * Removes a facet of the current criteria object.
     *
     * @param $name
     */
    public function removeFacet($name)
    {
        if (array_key_exists($name, $this->facets)) {
            unset($this->facets[$name]);
        }
    }

    /**
     * Removes a sorting of the current criteria object.
     *
     * @param $name
     */
    public function removeSorting($name)
    {
        if (array_key_exists($name, $this->sortings)) {
            unset($this->sortings[$name]);
        }
    }

    /**
     * @inheritdoc
     */
    public function jsonSerialize()
    {
        $data = get_object_vars($this);

        $data['baseConditions'] = array();
        foreach ($this->baseConditions as $object) {
            $data['baseConditions'][get_class($object)] = $object;
        }

        $data['conditions'] = array();
        foreach ($this->conditions as $object) {
            $data['conditions'][get_class($object)] = $object;
        }

        $data['sortings'] = array();
        foreach ($this->sortings as $object) {
            $data['sortings'][get_class($object)] = $object;
        }

        $data['facets'] = array();
        foreach ($this->facets as $object) {
            $data['facets'][get_class($object)] = $object;
        }

        return $data;
    }
}
