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

namespace Shopware\Models\User;
use Shopware\Components\Model\ModelEntity,
    Doctrine\ORM\Mapping AS ORM;

/**
 * Shopware role model represents a acl role in shopware.
 *
 * The Shopware role model represents a row of the s_core_auth_roles table.
 * The role model data set from a dynamic Doctrine Repository which is not defined in a php class.
 * One role has the follows associations:
 * <code>
 *   - User         =>  Shopware\Models\User\User       [1:n] [s_core_auth]
 *   - Privileges   =>  Shopware\Models\User\Privilege  [1:n] [s_core_acl_privileges]
 * </code>
 * The s_core_auth_roles table has the follows indices:
 * <code>
 *  -   PRIMARY KEY (`id`),
 *  -   UNIQUE KEY `name` (`name`)
 * </code>
 *
 * @ORM\Entity
 * @ORM\Table(name="s_core_auth_roles")
 */
class Role extends ModelEntity implements \Zend_Acl_Role_Interface
{
    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var integer $parentId
     *
     * @ORM\Column(name="parentID", type="integer", nullable=true)
     */
    private $parentId;

    /**
     * @var string $name
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=false)
     */
    private $name;

    /**
     * @var string $description
     *
     * @ORM\Column(name="description", type="text", nullable=false)
     */
    private $description;

    /**
     * @var string $source
     *
     * @ORM\Column(name="source", type="string", length=255, nullable=false)
     */
    private $source;

    /**
     * @var integer $enabled
     *
     * @ORM\Column(name="enabled", type="integer", nullable=false)
     */
    private $enabled;

    /**
     * @var integer $admin
     *
     * @ORM\Column(name="admin", type="integer", nullable=false)
     */
    private $admin;

    /**
     * The users property is the inverse side of the association between user and role.
     * The association is joined over the s_core_auth_roles.id field and the s_core_auth.roleID
     *
     * @var $role \Doctrine\Common\Collections\ArrayCollection
     * @ORM\OneToMany(targetEntity="User", mappedBy="role")
     */
    private $users;

    /**
     * The privileges property is the inverse side of the association between resource and privileges.
     * The association is joined over the s_core_acl_privileges.resourceID field and the s_core_acl_resources.id
     *
     * @ORM\OneToMany(targetEntity="Shopware\Models\User\Rule", mappedBy="role", cascade={"remove"})
     *
     * @var $rules \Doctrine\Common\Collections\ArrayCollection
     */
    private $rules;

    /**
     * The children property contains all inherited Shopware\Models\User\Role instances.
     * The children inherits all privileges from his parent.
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     * @ORM\OneToMany(targetEntity="\Shopware\Models\User\Role", mappedBy="parent")
     */
    private $children;

    /**
     * The parent property contains the instance of the inherited Shopware\Models\User\Role
     * model. The Role inherits all privileges from his parent.
     *
     * @var null|\Shopware\Models\User\Role $parent
     * @ORM\ManyToOne(targetEntity="\Shopware\Models\User\Role", inversedBy="children")
     * @ORM\JoinColumn(name="parentID", referencedColumnName="id")
     */
    private $parent;

    /**
     * Initials the collections
     */
    public function __construct()
    {
        $this->rules    = new \Doctrine\Common\Collections\ArrayCollection();
        $this->children = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set parentId
     *
     * @param integer $parentId
     * @return Role
     */
    public function setParentId($parentId)
    {
        $this->parentId = $parentId;
        return $this;
    }

    /**
     * Get parentId
     *
     * @return integer
     */
    public function getParentId()
    {
        return $this->parentId;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return Role
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set description
     *
     * @param string $description
     * @return Role
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set source
     *
     * @param string $source
     * @return Role
     */
    public function setSource($source)
    {
        $this->source = $source;
        return $this;
    }

    /**
     * Get source
     *
     * @return string
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * Set enabled
     *
     * @param integer $enabled
     * @return Role
     */
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;
        return $this;
    }

    /**
     * Get enabled
     *
     * @return integer
     */
    public function getEnabled()
    {
        return $this->enabled;
    }

    /**
     * Set admin
     *
     * @param integer $admin
     * @return Role
     */
    public function setAdmin($admin)
    {
        $this->admin = $admin;
        return $this;
    }

    /**
     * Get admin
     *
     * @return integer
     */
    public function getAdmin()
    {
        return $this->admin;
    }

    /**
     * Returns an array collection of Shopware\Models\User\User model instances, which
     * contains all data about the a single user. The association is defined over
     * the Role.id property (INVERSE SIDE) and the User.roleId (OWNING SIDE) property.
     * The user data is joined over the s_core_auth.roleID field.
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getUsers()
    {
        return $this->users;
    }

    /**
     * Setter function for the users association property which contains many instances of the Shopware\Models\User\User model which
     * contains all data about the a single user. The association is defined over
     * the Role.id property (INVERSE SIDE) and the User.roleId (OWNING SIDE) property.
     * The user data is joined over the s_core_auth.roleID field.
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $users
     * @return \Shopware\Models\User\Role
     */
    public function setUsers($users)
    {
        $this->users = $users;
        return $this;
    }

    /**
     * Returns an array collection of Shopware\Models\User\Privilege model instances,
     * which defines whether the user has rights to a shared Privilege.
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getPrivileges()
    {
        $privileges = new \Doctrine\Common\Collections\ArrayCollection();
        foreach ($this->getRules() as $rule) {
            $privileges->add($rule->getPrivilege());
        }

        return $privileges;
    }

    /**
     * Returns an array collection of Shopware\Models\User\Role model instances, which
     * contains all inherited child roles. The association is defined over
     * the Role.id property and the Role.parentId property.
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * Setter function for the children association property, which
     * contains all inherited child roles. The association is defined over
     * the Role.id property and the Role.parentId property.
     *
     * @param $children \Doctrine\Common\Collections\ArrayCollection
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function setChildren($children)
    {
        $this->children = $children;
        return $this;
    }

    /**
     * Returns the instance of the Shopware\Models\User\Role model which
     * contains the inherited parent role. The association is defined over
     * the Role.id property and the Role.parentId property.
     *
     * @return \Shopware\Models\User\Role
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Setter function for the parent association property which contains an instance
     * of the Shopware\Models\User\Role model which
     * contains the inherited parent role. The association is defined over
     * the Role.id property and the Role.parentId property.
     *
     * @param $parent \Shopware\Models\User\Role
     */
    public function setParent($parent)
    {
        $this->parent = $parent;
    }

    /**
     * Returns the string identifier of the Role
     *
     * @return string
     */
    public function getRoleId()
    {
        return $this->name;
    }

    /**
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getRules()
    {
        return $this->rules;
    }
}
