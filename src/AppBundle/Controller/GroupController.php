<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Group;
use Doctrine\Common\Collections\ArrayCollection;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Request;

class GroupController extends Controller
{
    /**
     * @Route("/group/{organisationName}/{groupName}", name="group", defaults={"groupName" = ""})
     * @Route("/group/{organisationName}")
     * @param $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(Request $request, $organisationName, $groupName)
    {
        $organisation = $this->getDoctrine()->getRepository('AppBundle:Group')->findOneBy(array('name' => $organisationName));
        if($organisation != null) {
            if ($organisation->getName() == $this->get('session')->get('organisation')) {
                $group = new Group();
                if ($groupName != "") {
                    $groups = $this->getDoctrine()->getRepository('AppBundle:Group')->findBy(array('name' => $groupName));

                    $group = new Group();
                    foreach ($groups as $oneGroup) {
                        if (in_array($oneGroup, $organisation->getGroups()->toArray())) {
                            $group = $oneGroup;
                            break;
                        }
                    }
                } else {
                    $group = $organisation;
                }

                $users = new ArrayCollection();
                foreach ($this->getDoctrine()->getRepository('AppBundle:User')->findAll() as $user) {
                    if (in_array($group, $user->getGroups()->toArray())) {
                        $users->add($user);
                    }
                }

                $photo = "";
                if($group->getType() == 'Fraction'){
                    $photo = base64_encode(stream_get_contents($group->getGroupInfo()->getPhoto()->getPhoto()));
                }

                return $this->render('AppBundle:Group:group.html.twig', array('group' => $group, 'users' => $users, 'photo' => $photo));
            } else {
                return $this->redirect($this->generateUrl('homepage'));
            }
        }else{
            return $this->redirect($this->generateUrl('homepage'));
        }


    }
}