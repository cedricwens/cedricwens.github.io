<?php
/**
 * Created by PhpStorm.
 * User: Cedric Wens
 * Date: 1/01/2015
 * Time: 20:16
 */

namespace AppBundle\Entity;

use AppBundle\Entity\UserGroup;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\OneToOne;
use FOS\UserBundle\Model\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="fos_user")
 */

class User extends BaseUser
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(name="first_name", type="string", length=255)
     *
     * @Assert\NotBlank(message="Please enter your firstname.", groups={"Registration", "Profile"})
     * @Assert\Length(
     *     min=3,
     *     max="255",
     *     minMessage="The name is too short.",
     *     maxMessage="The name is too long.",
     *     groups={"Registration", "Profile"}
     * )
     */
    protected $firstName;

    /**
     * @ORM\Column(name="last_name", type="string", length=255)
     *
     * @Assert\NotBlank(message="Please enter your lastname.", groups={"Registration", "Profile"})
     * @Assert\Length(
     *     min=3,
     *     max="255",
     *     minMessage="The name is too short.",
     *     maxMessage="The name is too long.",
     *     groups={"Registration", "Profile"}
     * )
     */
    protected $lastName;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     *
     * @Assert\NotBlank(message="Please enter your telephone number.", groups={"Registration", "Profile"})
     * @Assert\Length(
     *     min=3,
     *     max="255",
     *     minMessage="The telephone number is too short.",
     *     maxMessage="The telephone number is too long.",
     *     groups={"Registration", "Profile"}
     * )
     */
    protected $tel = null;

    /**
     * @OneToOne(targetEntity="AppBundle\Entity\Photo", cascade={"remove"})
     * @JoinColumn(name="user_photo", referencedColumnName="id")
     **/
    protected $photo;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $status = null;

    /**
     * @ORM\Column(name="locked_organisations", type="array", nullable=true)
     */
    protected $lockedOrganisations;

    /**
     * @ORM\Column(name="date_created", type="datetime", nullable=true)
     */
    protected $dateCreated = null;

    /**
     * @OneToOne(targetEntity="AppBundle\Entity\UserSettings", mappedBy="user", cascade={"remove", "persist"})
     **/
    protected $userSettings;

    /**
     * @OneToMany(targetEntity="AppBundle\Entity\UserGroup", mappedBy="user", cascade={"remove", "persist"})
     **/
    protected $userGroups;

    public function __construct()
    {
        parent::__construct();
        // your own logic
        $this->setStatus(0);
        $this->setDateCreated(new Assert\DateTime());
        $this->lockedOrganisations = array();
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
     * Set firstName
     *
     * @param string $firstName
     * @return User
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;

        return $this;
    }

    /**
     * Get firstName
     *
     * @return string
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * Set lastName
     *
     * @param string $lastName
     * @return User
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;

        return $this;
    }

    /**
     * Get lastName
     *
     * @return string
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * Set tel
     *
     * @param string $tel
     * @return User
     */
    public function setTel($tel)
    {
        $this->tel = $tel;

        return $this;
    }

    /**
     * Get tel
     *
     * @return string
     */
    public function getTel()
    {
        return $this->tel;
    }

    /**
     * Set photo
     *
     * @param string $photo
     * @return User
     */
    public function setPhoto($photo)
    {
        $this->photo = $photo;

        return $this;
    }

    /**
     * Get photo
     *
     * @return string
     */
    public function getPhoto()
    {
        return $this->photo;
    }

    /**
     * Set status
     *
     * @param integer $status
     * @return User
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return integer
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set dateCreated
     *
     * @param \DateTime $dateCreated
     * @return User
     */
    public function setDateCreated($dateCreated)
    {
        $this->dateCreated = $dateCreated;

        return $this;
    }

    /**
     * Get dateCreated
     *
     * @return \DateTime
     */
    public function getDateCreated()
    {
        return $this->dateCreated;
    }

    /**
     * Set id
     *
     * @param integer $id
     * @return User
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Add departments
     *
     * @param \AppBundle\Entity\Group $groups
     * @return User
     */
    public function addDepartments(\AppBundle\Entity\Group $groups)
    {
        $this->groups[] = $groups;

        return $this;
    }

    /**
     * Set userSettings
     *
     * @param \AppBundle\Entity\UserSettings $userSettings
     * @return User
     */
    public function setUserSettings(\AppBundle\Entity\UserSettings $userSettings = null)
    {
        $this->userSettings = $userSettings;

        return $this;
    }

    /**
     * Get userSettings
     *
     * @return \AppBundle\Entity\UserSettings
     */
    public function getUserSettings()
    {
        return $this->userSettings;
    }


    /*
     * Get user organisations
     * */
    public function getOrganisations(){
        $organisations = new ArrayCollection();
        foreach($this->getUserGroups() as $userGroup) {
            if ($userGroup->getGroup()->getType() == 'Organisation') {
                $organisations->add($userGroup->getGroup());
            }
        }
        return $organisations;
    }

    /*
     * Get organisations in which user isn't locked
     * */
    public function getUnlockedOrganisations(){
        $organisations = new ArrayCollection();
        foreach($this->getUserGroups() as $userGroup) {
            if ($userGroup->getGroup()->getType() == 'Organisation' && !in_array($userGroup->getGroup()->getId(), $this->getLockedOrganisations())) {
                $organisations->add($userGroup->getGroup());
            }
        }
        return $organisations;
    }

    /*
     * Get user departments
     * */
    public function getDepartments(){
        $organisations = new ArrayCollection();
        foreach($this->getUserGroups() as $userGroup) {
            if ($userGroup->getGroup()->getType() == 'Department') {
                $organisations->add($userGroup->getGroup());
            }
        }
        return $organisations;
    }

    /**
     * Set locked_organisations
     *
     * @param array $locked
     * @return User
     */
    public function setLockedOrganisations(array $lockedOrganisations)
    {
        $this->lockedOrganisations = array();

        foreach ($lockedOrganisations as $lockedOrganisation) {
            $this->addLockedOrganisation($lockedOrganisation);
        }

        return $this;
    }

    /**
     * Get lockedOrganisations
     *
     * @return array
     */
    public function getLockedOrganisations()
    {
        return $this->lockedOrganisations;
    }

    /**
     * Add locked organisation
     */
    public function addLockedOrganisation($organisationId)
    {

        if (!in_array($organisationId, $this->lockedOrganisations, true)) {
            $this->lockedOrganisations[] = $organisationId;
        }

        return $this;
    }

    /**
     * Remove locked organisation
     */
    public function removeLockedOrganisation($organisationId)
    {
        if (false !== $key = array_search($organisationId, $this->lockedOrganisations, true)) {
            unset($this->lockedOrganisations[$key]);
            $this->lockedOrganisations = array_values($this->lockedOrganisations);
        }

        return $this;
    }

    /**
     * Check if user is locked in specific organisation
     */
    public function isLockedInOrganisation($organisationId)
    {
        return in_array($organisationId, $this->getLockedOrganisations(), true);
    }

    /**
     * Add userGroups
     *
     * @param UserGroup $userGroup
     * @return User
     */
    public function addUserGroup(UserGroup $userGroup)
    {
        if (!in_array($userGroup, $this->getUserGroups()->toArray())) {
            $this->getUserGroups()->add($userGroup);
        }

        return $this;
    }

    /**
     * Remove userGroups
     *
     * @param UserGroup $userGroups
     */
    public function removeUserGroup(UserGroup $userGroups)
    {
        $this->userGroups->removeElement($userGroups);
    }

    /**
     * Get userGroups
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getUserGroups()
    {
        return $this->userGroups ?: $this->userGroups = new ArrayCollection();
    }

    public function isChildOf(Group $group){
        foreach($this->userGroups as $userGroup){
            //if($userGroup->getGroup()->getType() == 'User') {
                if ($userGroup->getGroup()->getId() == $group->getId()) {
                    //echo 'true<br />';
                    return true;
                } elseif (sizeof($userGroup->getGroup()->getParents()) > 0) {
                    if ($userGroup->getGroup()->isChildOf($group)) {
                        return true;
                    }
                } else {
                }
            //}
        }
        return false;
    }
}
