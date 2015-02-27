<?php
/**
 * Created by PhpStorm.
 * User: Cedric Wens
 * Date: 3/02/2015
 * Time: 17:18
 */

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="report")
 */
class Report {
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="text", length=65535)
     */
    protected $description;

    /**
     * @ORM\Column(type="blob", nullable=true)
     * @Assert\File(maxSize="6000000")
     */
    protected $photo = null;

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
     * Set description
     *
     * @param string $description
     * @return Report
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
     * Set photo
     *
     * @param string $photo
     * @return Report
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
     * Set ddescription
     *
     * @param string $ddescription
     * @return Report
     */
    public function setDdescription($ddescription)
    {
        $this->ddescription = $ddescription;

        return $this;
    }

    /**
     * Get ddescription
     *
     * @return string 
     */
    public function getDdescription()
    {
        return $this->ddescription;
    }
}
