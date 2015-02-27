<?php
/**
 * Created by PhpStorm.
 * User: Cedric Wens
 * Date: 22/02/2015
 * Time: 19:04
 */

namespace AppBundle\Form\Type;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class WorkflowStepType extends AbstractType
{
    private $organisation;

    public function __construct($organisation)
    {
        $this->organisation = $organisation;
    }
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', 'text')
            ->add('groups', 'entity', array(
                'class'         => 'AppBundle:Group',
                'query_builder' => function(EntityRepository $er) {
                    return $er->createQueryBuilder('d')
                        ->join('d.parents', 'p')
                        ->where('p.id = ?1 AND d.type = ?2')
                        ->orWhere('p.id = ?1 AND d.type = ?3')
                        ->setParameter(1, $this->organisation)
                        ->setParameter(2, 'User')
                        ->setParameter(3, 'Department')
                        ->orderBy('d.type', 'ASC');
                },
                'property'      => 'name',
                'label'         => 'app.form.departments',
                'attr'          => array('class' => ''),
                'expanded'      => false,
                'multiple'      => true,
                'mapped'        => false
            ))
            ->add('function', 'choice', array(
                'choices'   => array(
                    'create_agenda'     => 'app.workflow.function.create_agenda',
                    'extern_advice'     => 'app.workflow.function.extern_advice'
                ),
                'multiple'  => true,
                'mapped'    => false
            ))
            ->add('previous', 'integer', array(
                'mapped'    => false
            ))
            ->add('duration', 'time');
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\WorkflowStep'
        ));
    }

    public function getName()
    {
        return 'step';
    }
}