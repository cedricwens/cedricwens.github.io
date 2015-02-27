<?php
/**
 * Created by PhpStorm.
 * User: Cedric Wens
 * Date: 22/02/2015
 * Time: 19:05
 */

namespace AppBundle\Form\Type;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class WorkflowType extends AbstractType
{
    private $organisation;

    public function __construct($organisation)
    {
        $this->organisation= $organisation;
    }
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', 'text', array(
                'attr' => array('id' => 'tags')
            ))
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
            ->add('steps', 'collection', array(
                'type'          => new WorkflowStepType($this->organisation),
                'allow_add'     => true,
                'allow_delete'  => true,
                'by_reference'  => false,
                'mapped'        => false
            ))
            ->add('save', 'submit');
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Workflow',
        ));
    }

    public function getName()
    {
        return 'workflow';
    }
}