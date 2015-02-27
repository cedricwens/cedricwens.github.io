<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToOne;

/**
 * @ORM\Entity
 * @ORM\Table("workflow_step")
 */
class WorkflowStep
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     *
     * @ORM\Column(name="status", type="integer")
     */
    private $status;

    /**
     *
     * @ORM\Column(name="step", type="integer")
     */
    private $step;

    /**
     * @ManyToOne(targetEntity="AppBundle\Entity\Workflow", inversedBy="steps")
     * @ORM\JoinColumn(name="workflow_id", referencedColumnName="id")
     **/
    protected $workflow;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @ManyToOne(targetEntity="AppBundle\Entity\Group")
     * @ORM\JoinColumn(name="group_id", referencedColumnName="id")
     **/
    protected $group;

    /**
     * @OneToOne(targetEntity="AppBundle\Entity\WorkflowFunction", mappedBy="step", cascade={"remove"})
     **/
    protected $function;

    /**
     * @var integer
     *
     * @ORM\Column(name="duration", type="time")
     */
    private $duration;

    /**
     * @ORM\Column(name="start", type="datetime", nullable=true)
     */
    protected $start;

    /**
     * @ORM\Column(name="end", type="datetime", nullable=true)
     */
    protected $end;

    /**
     * @OneToOne(targetEntity="AppBundle\Entity\WorkflowRelation", mappedBy="step", cascade={"remove"})
     **/
    protected $relation;

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
     * Set status
     *
     * @param integer $status
     * @return WorkflowStep
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
     * Set step
     *
     * @param integer $step
     * @return WorkflowStep
     */
    public function setStep($step)
    {
        $this->step = $step;

        return $this;
    }

    /**
     * Get step
     *
     * @return integer 
     */
    public function getStep()
    {
        return $this->step;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return WorkflowStep
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
     * Set duration
     *
     * @param \DateTime $duration
     * @return WorkflowStep
     */
    public function setDuration($duration)
    {
        $this->duration = $duration;

        return $this;
    }

    /**
     * Get duration
     *
     * @return \DateTime 
     */
    public function getDuration()
    {
        return $this->duration;
    }

    /**
     * Set start
     *
     * @param \DateTime $start
     * @return WorkflowStep
     */
    public function setStart($start)
    {
        $this->start = $start;

        return $this;
    }

    /**
     * Get start
     *
     * @return \DateTime 
     */
    public function getStart()
    {
        return $this->start;
    }

    /**
     * Set end
     *
     * @param \DateTime $end
     * @return WorkflowStep
     */
    public function setEnd($end)
    {
        $this->end = $end;

        return $this;
    }

    /**
     * Get end
     *
     * @return \DateTime 
     */
    public function getEnd()
    {
        return $this->end;
    }

    /**
     * Set workflow
     *
     * @param \AppBundle\Entity\Workflow $workflow
     * @return WorkflowStep
     */
    public function setWorkflow(\AppBundle\Entity\Workflow $workflow = null)
    {
        $this->workflow = $workflow;

        return $this;
    }

    /**
     * Get workflow
     *
     * @return \AppBundle\Entity\Workflow 
     */
    public function getWorkflow()
    {
        return $this->workflow;
    }

    /**
     * Set group
     *
     * @param \AppBundle\Entity\Group $group
     * @return WorkflowStep
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

    /**
     * Set function
     *
     * @param \AppBundle\Entity\WorkflowFunction $function
     * @return WorkflowStep
     */
    public function setFunction(\AppBundle\Entity\WorkflowFunction $function = null)
    {
        $this->function = $function;

        return $this;
    }

    /**
     * Get function
     *
     * @return \AppBundle\Entity\WorkflowFunction 
     */
    public function getFunction()
    {
        return $this->function;
    }

    /**
     * Set relation
     *
     * @param \AppBundle\Entity\WorkflowRelation $relation
     * @return WorkflowStep
     */
    public function setRelation(\AppBundle\Entity\WorkflowRelation $relation = null)
    {
        $this->relation = $relation;

        return $this;
    }

    /**
     * Get relation
     *
     * @return \AppBundle\Entity\WorkflowRelation 
     */
    public function getRelation()
    {
        return $this->relation;
    }
}
