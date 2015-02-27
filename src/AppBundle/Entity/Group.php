<?php
/**
 * Created by PhpStorm.
 * User: Cedric Wens
 * Date: 5/01/2015
 * Time: 19:43
 */

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\JoinTable;
use Doctrine\ORM\Mapping\ManyToMany;
use Doctrine\ORM\Mapping\OneToOne;
use FOS\UserBundle\Model\Group as BaseGroup;

/**
 * @ORM\Entity
 * @ORM\Table(name="fos_group")
 */
class Group extends BaseGroup
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\JoinColumn(name="groupInfo_id", referencedColumnName="id")
     * @OneToOne(targetEntity="AppBundle\Entity\GroupInfo", cascade={"remove"})
     */
    protected $groupInfo;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $status = null;

    /**
     * @ORM\Column(type="string", length=255)
     */
    protected $type;

    /**
     * @ORM\Column(type="integer")
     */
    protected $ref;

    /**
     * @ManyToMany(targetEntity="Group", mappedBy="groups")
     **/
    private $parents;

    /**
     * @ManyToMany(targetEntity="Group", inversedBy="parents")
     * @JoinTable(name="group_groups",
     *      joinColumns={@JoinColumn(name="group_parent", referencedColumnName="id")},
     *      inverseJoinColumns={@JoinColumn(name="group_child", referencedColumnName="id")}
     *      )
     **/
    protected $groups;

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
     * Set type
     *
     * @param string $type
     * @return Group
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set name
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
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set status
     *
     * @param string $status
     * @return Group
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    public function __toString()
    {
        return strval($this->name);
    }

    /**
     * Set groupInfo
     *
     * @param \AppBundle\Entity\GroupInfo $groupInfo
     * @return Group
     */
    public function setGroupInfo(\AppBundle\Entity\GroupInfo $groupInfo = null)
    {
        $this->groupInfo = $groupInfo;

        return $this;
    }

    /**
     * Get groupInfo
     *
     * @return \AppBundle\Entity\GroupInfo
     */
    public function getGroupInfo()
    {
        return $this->groupInfo;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->groups = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add groups
     *
     * @param \AppBundle\Entity\Group $groups
     * @return Group
     */
    public function addGroup(\AppBundle\Entity\Group $groups)
    {
        $this->groups[] = $groups;

        return $this;
    }

    /**
     * Remove groups
     *
     * @param \AppBundle\Entity\Group $groups
     */
    public function removeGroup(\AppBundle\Entity\Group $groups)
    {
        $this->groups->removeElement($groups);
    }

    /**
     * Get groups
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getGroups()
    {
        return $this->groups;
    }

    /**
     * Set ref
     *
     * @param integer $ref
     * @return Group
     */
    public function setRef($ref)
    {
        $this->ref = $ref;

        return $this;
    }

    /**
     * Get ref
     *
     * @return integer
     */
    public function getRef()
    {
        return $this->ref;
    }

    /**
     * Add parents
     *
     * @param \AppBundle\Entity\Group $parents
     * @return Group
     */
    public function addParent(\AppBundle\Entity\Group $parents)
    {
        $this->parents[] = $parents;

        return $this;
    }

    /**
     * Remove parents
     *
     * @param \AppBundle\Entity\Group $parents
     */
    public function removeParent(\AppBundle\Entity\Group $parents)
    {
        $this->parents->removeElement($parents);
    }

    /**
     * Get parents
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getParents()
    {
        return $this->parents;
    }

    public function isChildOf(Group $group){
        foreach($this->parents as $parent){
            if($parent->getId() == $group->getId()){
                return true;
            }elseif(sizeof($parent->getParents()) > 0){
                $this->isChildOf($parent);
            }else{

            }
        }
        return false;
    }
}
