<?php
/**
 * Created by PhpStorm.
 * User: Cedric Wens
 * Date: 3/01/2015
 * Time: 20:51
 */

namespace AppBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // add your custom field
        $builder->remove('username');
        $builder->remove('plainPassword');
        $builder->add('firstname');
        $builder->add('lastname');
        $builder->add('tel');
    }

    public function getParent()
    {
        return 'fos_user_registration';
    }

    public function getName()
    {
        return 'app_user_registration';
    }
}