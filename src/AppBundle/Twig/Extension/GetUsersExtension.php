<?php

namespace AppBundle\Twig\Extension;

use Symfony\Component\HttpKernel\KernelInterface;
use AppBundle\Entity\Group;
use AppBundle\Entity\User;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class GetUsersExtension extends \Twig_Extension
{

    private $generator;
    private $doctrine;

    public function __construct( RegistryInterface $doctrine, UrlGeneratorInterface $generator)
    {
        $this->doctrine = $doctrine;
        $this->generator = $generator;
    }

    public function getFunctions()
    {
        return array(
            'get_users' => new \Twig_Function_Method($this, 'get_users'),
            'get_groups' => new \Twig_Function_Method($this, 'get_groups'),
            'group_url' => new \Twig_Function_Method($this, 'group_url'),
            'get_organisation' => new \Twig_Function_Method($this, 'get_organisation'),
        );
    }

    //Trim last comma
    public function get_users(Group $group){
        return substr($this->get_users1($group), 0, -2);
    }

    //Get all users recursive
    public function get_users1(Group $group){
        $string = "";
        foreach($group->getGroups() as $child)
            if($child->getType() == 'User'){
                $user = $this->doctrine->getRepository('AppBundle:User')->findOneBy(array('username' => $child->getName()));
                $string .= '<a href="'.$this->generator->generate('profile', array('username'=>$child->getName())).'">'.$user->getFirstName().' '.$user->getLastName().'</a>, ';
            }
            else
                $string .= $this->get_users1($child);
        return $string;
    }

    //Trim last comma
    public function get_groups(Group $group){
        return substr($this->get_groups1($group), 0, -2);
    }

    //Get all groups recursive
    public function get_groups1(Group $group){
        $string = "";
        foreach($group->getGroups() as $child)
            if($child->getType() == 'User'){
                $user = $this->doctrine->getRepository('AppBundle:User')->findOneBy(array('username' => $child->getName()));
                $string .= '<a href="'.$this->generator->generate('profile', array('username'=>$child->getName())).'">'.$user->getFirstName().' '.$user->getLastName().'</a>, ';
            }
            elseif($child->getType() == 'System')
                $string .= $this->get_users1($child);
            elseif($this->get_organisation($child) != null && $child->getGroupInfo() != null)
                $string .= '<a href="'.$this->generator->generate('group', array('groupName' => $child->getName(), 'organisationName' => $this->get_organisation($child)->getName())).'">'.$child->getName().'</a>, ';
            else
                $string .= $child->getName().', ';
        return $string;
    }

    //get organisation
    public function get_organisation(Group $group){
        foreach($group->getParents() as $parent)
            if($parent->getType() == 'Organisation')
                return $parent;
            elseif($parent->getType() == 'System')
                return null;
            else
                return $this->get_organisation($parent);
        return null;
    }

    //Create URL from group
    public function group_url(Group $group){
        if($group->getType() == 'User') {
            $user = $this->doctrine->getRepository('AppBundle:User')->findOneBy(array('username' => $group->getName()));
            return '<a href="' . $this->generator->generate('profile', array('username' => $group->getName())).'">' . $user->getFirstName() . ' ' . $user->getLastName() . '</a>';
        }
        else
            return '<a href="'.$this->generator->generate('group', array('groupName' => $group->getName(), 'organisationName' => $this->get_organisation($group)->getName())).'">'.$group->getGroupInfo()->getShortName().'</a>';
    }

    public function getName()
    {
        return 'get_users';
    }

}