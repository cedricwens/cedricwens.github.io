<?php
/**
 * Created by PhpStorm.
 * User: Cedric Wens
 * Date: 22/02/2015
 * Time: 17:43
 */

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToOne;

/**
 * @ORM\Entity
 * @ORM\Table("workflow_function")
 */
class WorkflowFunction
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
     * @OneToOne(targetEntity="AppBundle\Entity\WorkflowStep", inversedBy="function")
     * @ORM\JoinColumn(name="workflow_step_id", referencedColumnName="id")
     **/
    protected $step;

    /**
     * @ORM\Column(name="function", type="array")
     */
    private $function;

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
     * Set function
     *
     * @param array $function
     * @return WorkflowFunction
     */
    public function setFunction($function)
    {
        $this->function = $function;

        return $this;
    }

    /**
     * Get function
     *
     * @return array 
     */
    public function getFunction()
    {
        return $this->function;
    }

    /**
     * Set step
     *
     * @param \AppBundle\Entity\WorkflowStep $step
     * @return WorkflowFunction
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
}
