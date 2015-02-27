<?php
/**
 * Created by PhpStorm.
 * User: Cedric Wens
 * Date: 3/02/2015
 * Time: 12:12
 */

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToOne;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="user_settings")
 */
class UserSettings {
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @OneToOne(targetEntity="AppBundle\Entity\User", inversedBy="userSettings")
     * @JoinColumn(name="user_id", referencedColumnName="id")
     **/
    protected $user;

    /**
     * @ManyToOne(targetEntity="Group", cascade={"persist"})
     * @JoinColumn(name="default_organisation", referencedColumnName="id")
     */
    protected $defaultOrganisation;

    /**
     * @ManyToOne(targetEntity="Group", cascade={"persist"})
     * @JoinColumn(name="default_department", referencedColumnName="id")
     */
    protected $defaultDepartment;

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
     * Set defaultOrganisation
     *
     * @param \AppBundle\Entity\Group $defaultOrganisation
     * @return UserSettings
     */
    public function setDefaultOrganisation(\AppBundle\Entity\Group $defaultOrganisation = null)
    {
        $this->defaultOrganisation = $defaultOrganisation;

        return $this;
    }

    /**
     * Get defaultOrganisation
     *
     * @return \AppBundle\Entity\Group 
     */
    public function getDefaultOrganisation()
    {
        return $this->defaultOrganisation;
    }

    /**
     * Set defaultDepartment
     *
     * @param \AppBundle\Entity\Group $defaultDepartment
     * @return UserSettings
     */
    public function setDefaultDepartment(\AppBundle\Entity\Group $defaultDepartment = null)
    {
        $this->defaultDepartment = $defaultDepartment;

        return $this;
    }

    /**
     * Get defaultDepartment
     *
     * @return \AppBundle\Entity\Group 
     */
    public function getDefaultDepartment()
    {
        return $this->defaultDepartment;
    }

    /**
     * Set user
     *
     * @param \AppBundle\Entity\User $user
     * @return UserSettings
     */
    public function setUser(\AppBundle\Entity\User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \AppBundle\Entity\User 
     */
    public function getUser()
    {
        return $this->user;
    }
}
