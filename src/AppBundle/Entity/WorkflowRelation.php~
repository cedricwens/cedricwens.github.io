<?php
/**
 * Created by PhpStorm.
 * User: Cedric Wens
 * Date: 21/02/2015
 * Time: 18:23
 */

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToOne;

/**
 * @ORM\Entity
 * @ORM\Table(name="workflow_relation")
 */
class WorkflowRelation {
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @OneToOne(targetEntity="AppBundle\Entity\WorkflowStep", inversedBy="relation")
     * @ORM\JoinColumn(name="step_id", referencedColumnName="id")
     **/
    protected $step;

    /**
     * @ManyToOne(targetEntity="AppBundle\Entity\WorkflowStep", cascade={"persist"})
     * @ORM\JoinColumn(name="step_previous", referencedColumnName="id")
     **/
    protected $previousStep;

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
     * Set step
     *
     * @param \AppBundle\Entity\WorkflowStep $step
     * @return WorkflowRelation
     */
    public function setStep(\AppBundle\Entity\WorkflowStep $step = null)
    {
        $this->step = $step;

        return $this;
    }

    /**
     * Get step
     *
     * @return \AppBundle\Entity\WorkflowStep 
     */
    public function getStep()
    {
        return $this->step;
    }

    /**
     * Set previousStep
     *
     * @param \AppBundle\Entity\WorkflowStep $previousStep
     * @return WorkflowRelation
     */
    public function setPreviousStep(\AppBundle\Entity\WorkflowStep $previousStep = null)
    {
        $this->previousStep = $previousStep;

        return $this;
    }

    /**
     * Get previousStep
     *
     * @return \AppBundle\Entity\WorkflowStep 
     */
    public function getPreviousStep()
    {
        return $this->previousStep;
    }

    /**
     * Set nextStep
     *
     * @param \AppBundle\Entity\WorkflowStep $nextStep
     * @return WorkflowRelation
     */
    public function setNextStep(\AppBundle\Entity\WorkflowStep $nextStep = null)
    {
        $this->nextStep = $nextStep;

        return $this;
    }

    /**
     * Get nextStep
     *
     * @return \AppBundle\Entity\WorkflowStep 
     */
    public function getNextStep()
    {
        return $this->nextStep;
    }
}
