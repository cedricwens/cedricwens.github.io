<?php
/**
 * Created by PhpStorm.
 * User: Cedric Wens
 * Date: 20/02/2015
 * Time: 15:10
 */

namespace AppBundle\Form\Type;

use AppBundle\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use AppBundle\Entity\Group;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class RoleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('roles', 'choice', array(
                'label' => 'app.action.functionalities',
                'choices' => array(
                    'ROLE_ADMIN'    => 'app.menu.admin',
                    'ROLE_USER+++'  => 'app.menu.meetings',
                    'ROLE_USER++'   => 'app.menu.agendas',
                    'ROLE_USER+'    => 'app.menu.dossiers'
                ),
                'multiple'  => true,
                'expanded'  => true,
                'data'      => $options['roles']
            ))
            ->add('save',        'submit', array(
                'label' => 'app.action.save',
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
            'roles' => ''
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