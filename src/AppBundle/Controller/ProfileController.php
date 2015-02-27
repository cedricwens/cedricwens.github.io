<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Group;
use AppBundle\Entity\Photo;
use AppBundle\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Annotation\Route;

class ProfileController extends Controller
{

    public function indexAction($name)
    {
        return $this->render('', array('name' => $name));
    }

    /**
     * @Route("/profile/{username}", name="profile")
     * @param $username
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function profileAction(User $user)
    {
        $em = $this->getDoctrine()->getManager();
        $photo = $user->getPhoto()->getPhoto();
        $data = stream_get_contents($photo);
        //echo '<img src="data:image/jpg;base64,' .  base64_encode($data)  . '" />';



        $userManager = $this->container->get('fos_user.user_manager');
        $groups = $user->getUserGroups();
        $userGroup = new Group();
        foreach($user->getUserGroups() as $group){
            if($group->getGroup()->getType() == 'User'){
                $userGroup = $group->getGroup();
                break;
            }
        }
        return $this->render('AppBundle:Profile:profile.html.twig', array('user' => $user, 'loggedin_user' => $this->getUser(), 'photo' => base64_encode($data), 'groups' => $groups, 'userGroup' => $userGroup));
    }
}
