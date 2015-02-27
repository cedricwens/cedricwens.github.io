<?php
/**
 * Created by PhpStorm.
 * User: Cedric Wens
 * Date: 14/02/2015
 * Time: 16:17
 */

namespace AppBundle\Form\Type;

use AppBundle\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use AppBundle\Entity\Group;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class AdminTableType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('groups', 'entity', array(
                'multiple'=>true,
                'expanded'=>true,
                'class'=>'AppBundle:Group',
                'property'=>'id',
                'choices' => $options["groups"],
                'label'=>false,
            ))
            ->add('add', 'submit', array(
                'attr' => array('class' => 'glyphicon glyphicon-plus btn-success action-button'),
                'label' => ' '
            ))
            ->add('edit', 'submit', array(
                'attr' => array('class' => 'glyphicon glyphicon-pencil btn-warning action-button'),
                'label' => ' '
            ))
            ->add('delete', 'submit', array(
                'attr' => array('class' => 'glyphicon glyphicon-trash btn-danger action-button'),
                'label' => ' '
            ))
            ->add('type', 'hidden', array(
                'data' => $options["type"]
            ))->getForm();
    }

    public function getName()
    {
        return 'app_form_admin_table';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'groups' => '',
            'type' => '',
        ));
    }
}