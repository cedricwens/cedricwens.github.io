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
 * @ORM\Table(name="log_info")
 */
class LogInfo
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToMany(targetEntity="Log")
     * @ORM\JoinColumn(name="log_id", referencedColumnName="id")
     **/
    protected $log;

    /**
     * @ORM\OneToOne(targetEntity="Group", cascade={"persist"})
     * @ORM\JoinColumn(name="fos_group", referencedColumnName="id")
     */
    protected $group;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $date;

    /**
     * @ORM\Column(type="integer")
     */
    protected $logid;
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->log = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set date
     *
     * @param \DateTime $date
     * @return LogInfo
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get date
     *
     * @return \DateTime 
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Set logid
     *
     * @param integer $logid
     * @return LogInfo
     */
    public function setLogid($logid)
    {
        $this->logid = $logid;

        return $this;
    }

    /**
     * Get logid
     *
     * @return integer 
     */
    public function getLogid()
    {
        return $this->logid;
    }

    /**
     * Add log
     *
     * @param \AppBundle\Entity\Log $log
     * @return LogInfo
     */
    public function addLog(\AppBundle\Entity\Log $log)
    {
        $this->log[] = $log;

        return $this;
    }

    /**
     * Remove log
     *
     * @param \AppBundle\Entity\Log $log
     */
    public function removeLog(\AppBundle\Entity\Log $log)
    {
        $this->log->removeElement($log);
    }

    /**
     * Get log
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getLog()
    {
        return $this->log;
    }

    /**
     * Set group
     *
     * @param \AppBundle\Entity\Group $group
     * @return LogInfo
     */
    public function setGroup(\AppBundle\Entity\Group $group = null)
    {
        $this->group = $group;

        return $this;
    }

    /**
     * Get group
     *
     * @return \AppBundle\Entity\Group 
     */
    public function getGroup()
    {
        return $this->group;
    }
}
