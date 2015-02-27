<?php
/**
 * Created by PhpStorm.
 * User: Cedric Wens
 * Date: 5/01/2015
 * Time: 19:43
 */

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="dossier")
 */
class Dossier
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="DossierType")
     * @ORM\JoinColumn(name="dossier_type_id", referencedColumnName="id")
     */
    protected $dossierType;

    /**
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    protected $name;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $description;

    /**
     * @ORM\Column(type="object", nullable=true)
     */
    protected $content;

    /**
     * @ORM\ManyToOne(targetEntity="Group")
     * @ORM\JoinColumn(name="fos_group_id", referencedColumnName="id")
     */
    protected $author;

    /**
     * @ORM\Column(name="date_created", type="datetime", nullable=true)
     */
    protected $dateCreated;

    /**
     * @ORM\OneToOne(targetEntity="Log", cascade={"remove"})
     * @ORM\JoinColumn(name="log_id", referencedColumnName="id")
     */
    protected $log;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $status;

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
     * Set name
     *
     * @param string $name
     * @return Dossier
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
     * @return Dossier
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
     * Set content
     *
     * @param string $content
     * @return Dossier
     */
    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Get content
     *
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Set dateCreated
     *
     * @param \DateTime $dateCreated
     * @return Dossier
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
     * Set status
     *
     * @param integer $status
     * @return Dossier
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
     * Set dossierType
     *
     * @param \AppBundle\Entity\DossierType $dossierType
     * @return Dossier
     */
    public function setDossierType(\AppBundle\Entity\DossierType $dossierType = null)
    {
        $this->dossierType = $dossierType;

        return $this;
    }

    /**
     * Get dossierType
     *
     * @return \AppBundle\Entity\DossierType
     */
    public function getDossierType()
    {
        return $this->dossierType;
    }

    /**
     * Set author
     *
     * @param \AppBundle\Entity\Group $author
     * @return Dossier
     */
    public function setAuthor(\AppBundle\Entity\Group $author = null)
    {
        $this->author = $author;

        return $this;
    }

    /**
     * Get author
     *
     * @return \AppBundle\Entity\Group 
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * Set log
     *
     * @param \AppBundle\Entity\Log $log
     * @return Dossier
     */
    public function setLog(\AppBundle\Entity\Log $log = null)
    {
        $this->log = $log;

        return $this;
    }

    /**
     * Get log
     *
     * @return \AppBundle\Entity\Log 
     */
    public function getLog()
    {
        return $this->log;
    }
}
