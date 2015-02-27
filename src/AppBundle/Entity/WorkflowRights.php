<?php
/**
 * Created by PhpStorm.
 * User: Cedric Wens
 * Date: 22/02/2015
 * Time: 17:48
 */

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\ManyToOne;

/**
 * @ORM\Entity
 * @ORM\Table("workflow_rights")
 */
class WorkflowRights
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
     * @ManyToOne(targetEntity="AppBundle\Entity\WorkflowStep")
     * @ORM\JoinColumn(name="workflow_step_id", referencedColumnName="id")
     **/
    protected $step;

    /**
     * @ORM\Column(name="field_name", type="string")
     */
    private $fieldName;

    /**
     * @ORM\Column(name="right", type="integer")
     */
    private $right;

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
     * Set fieldName
     *
     * @param string $fieldName
     * @return WorkflowRights
     */
    public function setFieldName($fieldName)
    {
        $this->fieldName = $fieldName;

        return $this;
    }

    /**
     * Get fieldName
     *
     * @return string 
     */
    public function getFieldName()
    {
        return $this->fieldName;
    }

    /**
     * Set right
     *
     * @param integer $right
     * @return WorkflowRights
     */
    public function setRight($right)
    {
        $this->right = $right;

        return $this;
    }

    /**
     * Get right
     *
     * @return integer 
     */
    public function getRight()
    {
        return $this->right;
    }

    /**
     * Set step
     *
     * @param \AppBundle\Entity\WorkflowStep $step
     * @return WorkflowRights
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
