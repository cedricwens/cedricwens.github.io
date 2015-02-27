<?php
/**
 * Created by PhpStorm.
 * User: Cedric Wens
 * Date: 3/01/2015
 * Time: 20:51
 */

namespace AppBundle\Form\Type;

use AppBundle\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use AppBundle\Entity\Group;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('firstName', 'text', array(
                'label' => 'app.form.first_name',
                'attr' => array('placeholder' => 'app.form.first_name')
            ))
            ->add('lastName',  'text', array(
                'label' => 'app.form.last_name',
                'attr' => array('placeholder' => 'app.form.last_name')
            ))
            ->add('email',     'email', array(
                'label' => 'app.form.email',
                'attr' => array('placeholder' => 'app.form.email')
            ))
            ->add('groups', 'entity', array(
                'class' => 'AppBundle:Group',
                /*'query_builder' => function(EntityRepository $er) {
                    return $er->createQueryBuilder('g')
                        ->orderBy('g.name', 'ASC');
                },*/
                'choices' => $this->getOrganisationGroups($options['organisation']),
                'property' => 'name',
                'attr' => array('class' => ''),
                'expanded' => true,
                'multiple' => true,
                'mapped' => false,
                'required' => false,
                'data' => $options['userData']
            ))
            ->add('save',        'submit', array(
                'label' => $options['label'],
                'attr' => array('class' => 'btn-primary')
            ))
            ->getForm();
    }

    public function getName()
    {
        return 'app_form_user';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'organisation' => '',
            'userData' => '',
        ));
    }

    /*
     * Get organisation groups
     * */
    public function getOrganisationGroups(Group $organisation){
        $organisations = new ArrayCollection();
        foreach($organisation->getGroups() as $group){
            $organisations->add($group);
        }
        return $organisations;
    }
}