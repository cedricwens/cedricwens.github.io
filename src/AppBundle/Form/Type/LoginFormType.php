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
use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;

class LoginFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('_username', 'email', array('label' => 'form.username', 'translation_domain' => 'FOSUserBundle')) // TODO: user can login with email by inhibit the user to enter username
            ->add('_password', 'password', array(
                    'label' => 'form.current_password',
                    'translation_domain' => 'FOSUserBundle',
                    'mapped' => false,
                    'constraints' => new UserPassword()))
            ->add('captcha', 'captcha')
            ->getForm();
    }
    public function getName()
    {
        return 'app_from_login';
    }
}
