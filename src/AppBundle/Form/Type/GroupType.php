<?php
/**
 * Created by PhpStorm.
 * User: Cedric Wens
 * Date: 3/01/2015
 * Time: 20:51
 */

namespace AppBundle\Form\Type;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use AppBundle\Entity\Group;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class GroupType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', 'text', array(
                'label' => 'app.form.name',
                'attr' => array('placeholder' => 'app.form.name')
            ))
            ->add('shortname',  'text', array(
                'label' => 'app.form.abbreviation',
                'attr' => array('placeholder' => 'app.form.abbreviation'),
                'mapped' => false
            ))
            ->add('tel', 'text', array(
                'label' => 'app.form.telnumber',
                'attr' => array('placeholder' => 'app.form.telnumber'),
                'data' => $options['tel'],
                'mapped' => false
            ))
            ->add('street', 'text', array(
                'label' => 'app.form.address',
                'attr' => array('placeholder' => 'app.entity.address.street'),
                'data' => $options['street'],
                'mapped' => false

            ))
            ->add('nr', 'text', array(
                'label' => ' ',
                'attr' => array('placeholder' => 'app.entity.address.nr'),
                'data' => $options['nr'],
                'mapped' => false
            ))
            ->add('zipcode', 'text', array(
                'label' => ' ',
                'attr' => array('placeholder' => 'app.entity.address.zipcode'),
                'data' => $options['zipcode'],
                'mapped' => false
            ))
            ->add('city', 'text', array(
                'label' => ' ',
                'attr' => array('placeholder' => 'app.entity.address.city'),
                'data' => $options['city'],
                'mapped' => false
            ))
            ->add('email', 'email', array(
                'label' => 'app.form.email',
                'attr' => array('placeholder' => 'app.form.email'),
                'data' => $options['email'],
                'mapped' => false
            ))
            /*->add('groups', 'entity', array(
                'class' => 'AppBundle:Group',
                'query_builder' => function(EntityRepository $er) {
                    return $er->createQueryBuilder('d')
                        ->orderBy('d.name', 'ASC');
                },
                'property' => 'name',
                'label' => 'app.form.departments',
                'attr' => array('class' => ''),
                'expanded' => true,
                'multiple' => true
            ))*/
            ->add('photo', 'file', array(
                'label' => 'app.form.image',
                'data' => $options['photo'],
                'mapped' => false,
                'required' => false
            ))
            /*->add('groups', 'entity', array(
                'class' => 'AppBundle:Group',
                'query_builder' => function(EntityRepository $er) {
                    return $er->createQueryBuilder('d')
                        ->where('d.type = :type')
                        ->setParameter('type', 'Fraction')
                        ->orderBy('d.name', 'ASC');
                },
                'property' => 'name',
                'label' => 'app.form.departments',
                'attr' => array('class' => ''),
                'expanded' => true,
                'multiple' => true
            ))*/
            ->add('save',        'submit', array(
                'label' => $options['label'],
                'attr' => array('class' => 'btn-primary')
            ))
            ->getForm();
    }

    public function getName()
    {
        return 'app_form_group';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'tel'       => '',
            'street'    => '',
            'nr'        => '',
            'zipcode'   => '',
            'city'      => '',
            'email'     => '',
            'photo'     => '',
        ));
    }
}