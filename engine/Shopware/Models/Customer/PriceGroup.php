<?php
/**
 * Shopware 4.0
 * Copyright © 2012 shopware AG
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
 *
 * @category   Shopware
 * @package    Shopware_Models
 * @subpackage Customer
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author     Oliver Denter
 * @author     $Author$
 */

namespace Shopware\Models\Customer;
use Shopware\Components\Model\ModelEntity,
    Doctrine\ORM\Mapping AS ORM;

/**
 * Shopware customer price group model represents a single customer price group.
 *
 * The Shopware customer group model represents a row of the s_core_customerpricegroups table.
 * The price group model data set from the Shopware\Models\Customer\Repository.
 * One price group has the follows associations:
 * <code>
 *   - Customer =>  Shopware\Models\Customer\Customer [1:1] [s_user]
 * </code>
 * The s_core_customerpricegroups table has the follows indices:
 * <code>
 *   - PRIMARY KEY (`id`)
 * </code>
 *
 * @ORM\Entity
 * @ORM\Table(name="s_core_customerpricegroups")
 */
class PriceGroup extends ModelEntity
{
    /**
     * The id property is an identifier property which means
     * doctrine associations can be defined over this field.
     *
     * @var integer $id
     * @ORM\Id
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * Contains the customer price group name value.
     *
     * @var string $name
     * @ORM\Column(name="name", type="string", length=255, nullable=false)
     */
    private $name;

    /**
     * Flag which indicates a net price.
     *
     * @var integer $netto
     * @ORM\Column(name="netto", type="integer", nullable=false)
     */
    private $netto;

    /**
     * Flag which indicates if a price group is active or not.
     *
     * @var integer $taxInput
     * @ORM\Column(name="active", type="integer", nullable=false)
     */
    private $active;
    /**
     * INVERSE SIDE
     * @ORM\OneToMany(targetEntity="Shopware\Models\Customer\Customer", mappedBy="priceGroup")
     * @var \Doctrine\Common\Collections\ArrayCollection
     */
    protected $customers;

    /**
     * Class constructor which initials the discounts association.
     */
    public function __construct()
    {
        $this->discounts = new \Doctrine\Common\Collections\ArrayCollection();
        $this->customers = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Getter function for the id field which is an identifier proerty. This means
     * doctrine association can be declared over this field.
     * The id field has no setter function, because the value is generated automatically.
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Setter function for the name property which is
     * a column property for the database field name.
     *
     * @param string $name
     * @return Group
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Getter function for the name property which is
     * a column property for the database field name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Returns an array collection with many instances of Shopware\Models\Customer\Customer models which
     * contains all data about the customer. The association is defined over
     * the Customer.group property (OWNING SIDE) and the Group.customers (INVERSE SIDE) property.
     * The customer data is joined over the s_user.groupkey field.
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getCustomers()
    {
        return $this->customers;
    }

    /**
     * Setter function for the customers property which contains an array collection with many instances of Shopware\Models\Customer\Customer models which
     * contains all data about the customer. The association is defined over
     * the Customer.group property (OWNING SIDE) and the Group.customers (INVERSE SIDE) property.
     * The customer data is joined over the s_user.groupkey field.
     *
     * @param $customers \Doctrine\Common\Collections\ArrayCollection
     */
    public function setCustomers($customers)
    {
        $this->customers = $customers;
    }

    /**
     * @return int
     */
    public function getNetto()
    {
        return $this->netto;
    }

    /**
     * @param int $netto
     * @return \Shopware\Models\Customer\PriceGroup
     */
    public function setNetto($netto)
    {
        $this->netto = $netto;
        return $this;
    }

    /**
     * @return int
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * @param int $active
     * @return \Shopware\Models\Customer\PriceGroup
     */
    public function setActive($active)
    {
        $this->active = $active;
        return $this;
    }
}
