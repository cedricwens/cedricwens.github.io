<?php

namespace AppBundle\Controller;

use Doctrine\Common\Collections\ArrayCollection;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class SearchController extends Controller
{
    public function indexAction(Request $request)
    {
        $data = array();
        $form = $this->createFormBuilder($data)
            ->setAction($this->generateUrl('search'))
            ->add('searchterm', 'search', array(
                'required' => false
            ))
            ->add('search', 'submit', array(
                'label' => 'app.menu.search'
            ))
            ->getForm();

        $form->handleRequest($request);

        if($form->isValid()){
            $finder = $this->container->get('fos_elastica.finder.search.user');
            $results = $finder->find($form['searchterm']->getData());
            foreach($results as $result){
                echo $result;
            }
            return $this->render('AppBundle:Search:search.html.twig');
        }

        return $this->render('AppBundle:Search:searchbar.html.twig', array('search_form' => $form->createView()));
    }

    /**
     * @Route("/search", name="search")
     *
     */
    public function searchAction(Request $request)
    {
        $data = array();
        $form = $this->createFormBuilder($data)
            ->setAction($this->generateUrl('search'))
            ->add('searchterm', 'search', array(
                'required' => false
            ))
            ->add('search', 'submit', array(
                'label' => 'app.menu.search'
            ))
            ->getForm();

        $form->handleRequest($request);

        if($form->isValid()){
            /*$finder = $this->container->get('fos_elastica.finder.search.user');
            $users = $finder->find('*'.$form['searchterm']->getData().'*');*/

            $organisationId = $this->get('session')->get('organisation');
            $organisation = $this->getDoctrine()->getRepository('AppBundle:Group')->findOneBy(array('id' => $organisationId));

            $finder = $this->container->get('fos_elastica.finder.search.group');
            $groups = $finder->find('*'.$form['searchterm']->getData().'*');

            $users = new ArrayCollection();
            $departments = new ArrayCollection();
            $organs = new ArrayCollection();
            $fractions = new ArrayCollection();
            $organisations = new ArrayCollection();
            foreach($groups as $group){
                switch($group->getType()){
                    case "User":
                        foreach($this->getDoctrine()->getRepository('AppBundle:User')->findBy(array('username' => $group->getName())) as $user){
                            foreach($user->getUserGroups() as $userGroup){
                                if($group == $userGroup->getGroup()){
                                    $users->add($user);
                                    break;
                                }
                            }
                        }
                        break;
                    case "Department":
                        if(in_array($group, $organisation->getGroups()->toArray())) { // Only find departments of current organisation
                            $departments->add($group);
                        }
                        break;
                    case "Organ":
                        if(in_array($group, $organisation->getGroups()->toArray())) { // Only find organs of current organisation
                            $organs->add($group);
                        }
                        break;
                    case "Fraction":
                        if(in_array($group, $organisation->getGroups()->toArray())) { // Only find fractions of current organisation
                            $fractions->add($group);
                        }
                        break;
                    case "Organisation":
                        if($this->getUser()->hasRole('ROLE_SUPER_ADMIN')) {
                            $organisations->add($group);
                        }
                        break;
                }
            }
            $results = sizeof($users) + sizeof($departments) + sizeof($organs) + sizeof($fractions) + sizeof($organisations);
            return $this->render('AppBundle:Search:search.html.twig', array('searchterm' => $form['searchterm']->getData(), 'users' => $users, 'departments' => $departments, 'organs' => $organs, 'fractions' => $fractions, 'organisations' => $organisations, 'results' => $results));
        }

        return $this->render('AppBundle:Search:search.html.twig');
    }
}