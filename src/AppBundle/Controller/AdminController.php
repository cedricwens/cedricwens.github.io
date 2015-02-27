<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Address;
use AppBundle\Entity\GroupInfo;
use AppBundle\Entity\Photo;
use AppBundle\Entity\User;
use AppBundle\Entity\Group;
use AppBundle\Entity\UserGroup;
use AppBundle\Entity\UserSettings;
use AppBundle\Form\Type\AdminTableType;
use AppBundle\Form\Type\GroupType;
use AppBundle\Form\Type\RoleType;
use AppBundle\Form\Type\UserType;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Intl\Data\Util\ArrayAccessibleResourceBundle;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class AdminController extends Controller
{
    /**
     * @Route("/admin", name="admin")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @throws AccessDeniedException
     */
    public function adminAction(Request $request)
    {
        $roles = $this->get('session')->get('roles');
        if(!in_array('ROLE_SUPER_ADMIN', $roles) && !in_array('ROLE_ADMIN', $roles)){
            throw new AccessDeniedException('Unauthorised access!');
        }
        $em = $this->getDoctrine()->getManager();
        //$userManager = $this->get('fos_user.user_manager');

        $departments = new ArrayCollection();
        $organisations = new ArrayCollection();
        $organs = new ArrayCollection();
        $fractions = new ArrayCollection();
        $organisation = $em->find('AppBundle:Group',$this->get('session')->get('organisation')->getId());
        $groups = $organisation->getGroups();

        foreach($groups as $group){
            switch($group->getType()){
                case 'Department':
                    $departments->add($group);
                    break;
                case 'Organisation':
                    $organisations->add($group);
                    if($group->getId() == $this->get('session')->get('organisation')->getId()){
                        $organisation = $group; // Currently active organisation
                    }
                    break;
                case 'Organ':
                    $organs->add($group);
                    break;
                case 'Fraction':
                    $fractions->add($group);
                    break;
            }
        }
        if(in_array('ROLE_SUPER_ADMIN', $this->get('session')->get('roles'))){
            $organisations = $this->getDoctrine()->getRepository('AppBundle:Group')->findBy(array('type' => 'Organisation'));
        }


        // Get all users from currently active organisation
        $allUsers = $this->getDoctrine()->getRepository('AppBundle:User')->findAll();
        $org = $this->getDoctrine()->getRepository('AppBundle:Group')->findOneBy(array('id' => $organisation));
        $users = new ArrayCollection();
        foreach ($allUsers as $user) {
            foreach($user->getUserGroups() as $group){
                if(in_array($group->getGroup(), $org->getGroups()->toArray())){
                    if($user->getUsername() != 'Admin') {
                        $users->add($user);
                        break;
                    }
                }
            }

        }
        /*$users = $this->getDoctrine()->getManager()$em->find('AppBundle:User',$this->get('session')->get('organisation')->getId());
        $query = $em->createQuery("SELECT u FROM AppBundle\Entity\User u JOIN u.userGroups g WHERE g.name = :name");
        $query->setParameter('name', $organisation->getName());
        $users = $query->getResult();*/


        $defaultData = array('message' => 'adminAction');

        $userForm = $this->get('form.factory')->createNamedBuilder('userform', 'form', $defaultData)
            ->add('users', 'entity', array(
                'multiple'=>true,
                'expanded'=>true,
                'class'=>'AppBundle:User',
                'property'=>'id',
                'choices' => $users,
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
            ->add('lock', 'submit', array(
                'attr' => array('class' => 'glyphicon glyphicon-lock btn-primary action-button'),
                'label' => ' '
            ))
            ->getForm();

        $userForm->handleRequest($request);
        if ($userForm->isValid()) {
            if($userForm->get('add')->isClicked()){
                return $this->redirect($this->generateUrl('addUser'));
            }
            elseif($userForm->get('edit')->isClicked()){
                if($userForm->get('users')->getData() != null) {
                    $user = new User();
                    foreach ($userForm->get('users')->getData() as $selectedUser) {
                        $user = $selectedUser;
                        break;
                    }

                    return $this->redirect($this->generateUrl('editUser', array('id' => $user->getId())));
                }else{
                    $message = $this->get('translator')->trans('app.messages.select_user');
                    $this->get('session')->getFlashBag()->add('warning', $message);
                }

            }
            elseif($userForm->get('delete')->isClicked()){
                $repository = $this->getDoctrine()->getRepository('AppBundle:Group');
                foreach($userForm->get('users')->getData() as $selectedUser){
                    if($selectedUser == $this->getUser()){ // You can't delete your own account
                        $message = '<strong>'.$this->get('translator')->trans('app.error.error_delete', array('%item%' => $selectedUser->getUsername())).'</strong>';
                        $message = $message.' '.$this->get('translator')->trans('app.messages.cant_delete_yourself');
                        $this->get('session')->getFlashBag()->add('danger', $message);
                    }else {
                        $group = $repository->findOneBy(array('name' => $selectedUser->getUsername(), 'type' => 'User'));
                        $userManager = $this->get('fos_user.user_manager');
                        if(1 == sizeof($selectedUser->getOrganisations())){ // If user is member of only 1 organisation
                            foreach($selectedUser->getGroups() as $group){
                                $em->remove($group);
                            }
                            $userManager->deleteUser($selectedUser);        // Delete user
                            $em->remove($group);
                        }else{                                              // If user is member of multiple organisations
                            // $removeGroup is the group that has to be removed
                            $removeGroup = $this->getDoctrine()->getRepository('AppBundle:UserGroup')->findOneBy(array('user' => $selectedUser, 'group' => $organisation));
                            $selectedUser->removeUserGroup($removeGroup);     // Remove user from active organisation
                            $em->remove($removeGroup);     // Remove user from active organisation
                            foreach ($selectedUser->getUserGroups() as $userGroup) {
                                // $removeGroup is the group that has to be removed
                                if(in_array($userGroup->getGroup(), $organisation->getGroups()->toArray())){
                                    $selectedUser->removeUserGroup($userGroup);    // Remove organisation groups from user
                                    $em->remove($userGroup);
                                }
                            }
                            $em->persist($selectedUser);
                        }
                        $em->flush();

                        $message = $this->get('translator')->transChoice('app.entity.user', 1);
                        $message = $message.' '.$this->get('translator')->trans('app.messages.successfully_deleted');
                        $this->get('session')->getFlashBag()->add('notice', ucfirst(strtolower($message)));
                    }
                }
                return $this->redirect($this->generateUrl('admin'));
            }
            elseif($userForm->get('lock')->isClicked()){
                foreach($userForm->get('users')->getData() as $selectedUser) {
                    if ($selectedUser == $this->getUser()) { // You can't (un)lock your own account
                        $message = '<strong>' . $this->get('translator')->trans(
                                'app.error.error_delete',
                                array('%item%' => $selectedUser->getUsername())
                            ) . '</strong>';
                        $message = $message . ' ' . $this->get('translator')->trans(
                                'app.messages.cant_delete_yourself'
                            );
                        $this->get('session')->getFlashBag()->add('danger', $message);
                    } else {
                        if ($selectedUser->isLocked() || $selectedUser->isLockedInOrganisation(
                                $organisation->getId()
                            )
                        ) {
                            $selectedUser->setLocked(false);
                            //$selectedUser->setStatus(0);
                            $selectedUser->removeLockedOrganisation($organisation->getId());
                            $em->persist($selectedUser);
                            $em->flush();
                            $message = $this->get('translator')->transChoice('app.entity.user', 1);
                            $message = $message . ' ' . $this->get('translator')->trans(
                                    'app.messages.successfully_unlocked'
                                );
                            $this->get('session')->getFlashBag()->add('notice', ucfirst(strtolower($message)));

                        } else {
                            //$selectedUser->setLocked(true);
                            //$selectedUser->setStatus(1);
                            $selectedUser->addLockedOrganisation($organisation->getId());
                            $em->persist($selectedUser);
                            $em->flush();
                            $message = $this->get('translator')->transChoice('app.entity.user', 1);
                            $message = $message . ' ' . $this->get('translator')->trans(
                                    'app.messages.successfully_locked'
                                );
                            $this->get('session')->getFlashBag()->add('notice', ucfirst(strtolower($message)));
                        }
                    }
                }
                return $this->redirect($this->generateUrl('admin'));




            }
        }


        $departmentForm = $this->createForm(new AdminTableType(), $defaultData, array(
            'groups'    => $departments,
            'type'      => 'department'
        ));

        $departmentForm->handleRequest($request);

        if ($departmentForm->isValid() && $departmentForm->get('type')->getData() == 'department') {
            if($departmentForm->get('add')->isClicked()){
                return $this->redirect($this->generateUrl('addGroup', array('type' => 'Department')));
            }elseif($departmentForm->get('edit')->isClicked()){
                $group = new Group();
                foreach($departmentForm->get('groups')->getData() as $selectedGroup){
                    $group = $selectedGroup;
                }
                return $this->redirect($this->generateUrl('editGroup', array('type' => 'Department', 'id' => $group->getId())));
            }elseif($departmentForm->get('delete')->isClicked()){
                foreach ($departmentForm->get('groups')->getData() as $selectedGroup) {
                    try {
                        $em->remove($selectedGroup);
                        $em->remove($selectedGroup->getGroupInfo());
                        $em->flush();
                        $message = $this->get('translator')->transChoice('app.entity.department', 1);
                        $message = $message .' '.$this->get('translator')->trans('app.messages.successfully_deleted');

                        $this->get('session')->getFlashBag()->add('notice', ucfirst(strtolower($message)));
                    }catch(\Exception $e){
                        $message = '<strong>'.$this->get('translator')->trans('app.error.error_delete', array('%item%' => $selectedGroup->getName())).'</strong>';
                        $message = $message.': '.$this->get('translator')->trans('app.error.remove_users_from_group');
                        $this->get('session')->getFlashBag()->add('danger', ucfirst(strtolower($message)));
                    }
                }
                return $this->redirect($this->generateUrl('admin'));
            }
        }


        $organisationForm = $this->createForm(new AdminTableType(), $defaultData, array(
            'groups'    => $organisations,
            'type'      => 'organisation'
        ));

        $organisationForm->handleRequest($request);

        if ($organisationForm->isValid() && $organisationForm->get('type')->getData() == 'organisation') {
            if($organisationForm->get('add')->isClicked()){
                return $this->redirect($this->generateUrl('addGroup', array('type' => 'Organisation')));
            }elseif($organisationForm->get('edit')->isClicked()){
                $group = new Group();
                foreach($organisationForm->get('groups')->getData() as $selectedGroup){
                    $group = $selectedGroup;
                }
                return $this->redirect($this->generateUrl('editGroup', array('type' => 'Organisation', 'id' => $group->getId())));
            }elseif($organisationForm->get('delete')->isClicked()){
                foreach ($organisationForm->get('groups')->getData() as $selectedGroup) {
                    if($selectedGroup != $organisation) {
                        try {
                            foreach ($selectedGroup->getGroups() as $group) {
                                $em->remove($group);
                            }

                            $em->remove($selectedGroup);
                            $em->remove($selectedGroup->getGroupInfo());
                            $em->flush();
                            $message = $this->get('translator')->transChoice('app.entity.organisation', 1);
                            $message = $message . ' ' . $this->get('translator')->trans(
                                    'app.messages.successfully_deleted'
                                );
                            $this->get('session')->getFlashBag()->add('notice', ucfirst(strtolower($message)));
                        } catch (\Exception $e) {
                            $message = '<strong>' . $this->get('translator')->trans(
                                    'app.error.error_delete',
                                    array('%item%' => $selectedGroup->getName())
                                ) . '</strong>';
                            $message = $message . ': ' . $this->get('translator')->trans(
                                    'app.error.remove_users_from_group'
                                );
                            $this->get('session')->getFlashBag()->add('danger', ucfirst(strtolower($message)));
                        }
                    }
                }
                return $this->redirect($this->generateUrl('admin'));
            }
        }


        $organForm = $this->createForm(new AdminTableType(), $defaultData, array(
            'groups'    => $organs,
            'type'      => 'organ'
        ));

        $organForm->handleRequest($request);

        if ($organForm->isValid() && $organForm->get('type')->getData() == 'organ') {
            if($organForm->get('add')->isClicked()){
                return $this->redirect($this->generateUrl('addGroup', array('type' => 'Organ')));
            }elseif($organForm->get('edit')->isClicked()){
                $group = new Group();
                foreach($organForm->get('groups')->getData() as $selectedGroup){
                    $group = $selectedGroup;
                }
                return $this->redirect($this->generateUrl('editGroup', array('type' => 'Organ', 'id' => $group->getId())));
            }elseif($organForm->get('delete')->isClicked()){
                foreach ($organForm->get('groups')->getData() as $selectedGroup) {
                    try {
                        $em->remove($selectedGroup);
                        $em->remove($selectedGroup->getGroupInfo());
                        $em->flush();
                        $message = $this->get('translator')->transChoice('app.entity.organ', 1);
                        $message = $message.' '.$this->get('translator')->trans('app.messages.successfully_deleted');
                        $this->get('session')->getFlashBag()->add('notice', ucfirst(strtolower($message)));
                    }catch(\Exception $e){
                        $message = '<strong>'.$this->get('translator')->trans('app.error.error_delete', array('%item%' => $selectedGroup->getName())).'</strong>';
                        $message = $message.': '.$this->get('translator')->trans('app.error.remove_users_from_group');
                        $this->get('session')->getFlashBag()->add('danger', ucfirst(strtolower($message)));
                    }
                }
                return $this->redirect($this->generateUrl('admin'));
            }
        }


        $fractionForm = $this->createForm(new AdminTableType(), $defaultData, array(
            'groups'    => $fractions,
            'type'      => 'fraction'
        ));

        $fractionForm->handleRequest($request);

        if ($fractionForm->isValid() && $fractionForm->get('type')->getData() == 'fraction') {
            if($fractionForm->get('add')->isClicked()){
                return $this->redirect($this->generateUrl('addGroup', array('type' => 'Fraction')));
            }elseif($fractionForm->get('edit')->isClicked()){
                $group = new Group();
                foreach($fractionForm->get('groups')->getData() as $selectedGroup){
                    $group = $selectedGroup;
                }
                return $this->redirect($this->generateUrl('editGroup', array('type' => 'Fraction', 'id' => $group->getId())));
            }elseif($fractionForm->get('delete')->isClicked()){
                foreach ($fractionForm->get('groups')->getData() as $selectedGroup) {
                    try {
                        $em->remove($selectedGroup);
                        $em->remove($selectedGroup->getGroupInfo());
                        $em->flush();
                        $message = $this->get('translator')->transChoice('app.entity.fraction', 1);
                        $message = $message.' '.$this->get('translator')->trans('app.messages.successfully_deleted');
                        $this->get('session')->getFlashBag()->add('notice', ucfirst(strtolower($message)));
                    }catch(\Exception $e){
                        $message = '<strong>'.$this->get('translator')->trans('app.error.error_delete', array('%item%' => $selectedGroup->getName())).'</strong>';
                        $message = $message.': '.$this->get('translator')->trans('app.error.remove_users_from_group');
                        $this->get('session')->getFlashBag()->add('danger', ucfirst(strtolower($message)));
                    }
                }
                return $this->redirect($this->generateUrl('admin'));
            }
        }




        return $this->render('AppBundle:Admin:admin.html.twig', array(
            'userform' => $userForm->createView(),
            'users' => $users,
            'departmentform' => $departmentForm->createView(),
            'departments' => $departments,
            'organisationform' => $organisationForm->createView(),
            'organisations' => $organisations,
            'organform' => $organForm->createView(),
            'organs' => $organs,
            'fractionform' => $fractionForm->createView(),
            'fractions' => $fractions,
            'organisation' => $organisation
        ));
    }

    /**
     * @Route("/admin/addUser", name="addUser")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function addUserAction(Request $request)
    {
        $roles = $this->get('session')->get('roles');
        if(!in_array('ROLE_SUPER_ADMIN', $roles) && !in_array('ROLE_ADMIN', $roles)){
            throw new AccessDeniedException('Unauthorised access!');
        }
        $organisation = $this->getDoctrine()->getRepository('AppBundle:Group')->findOneBy(array('id' => $this->get('session')->get('organisation')->getId()));
        $user = new User();
        $form = $this->createForm(new UserType(), $user, array(
            'action' => $this->generateUrl('addUser'),
            'label' => 'app.form.next',
            'organisation' => $organisation,
            'userData' => array()
        ));

        $form->handleRequest($request);
        if($form->isValid()){
            $conflict = false; // Used to check if user already exists

            $em = $this->getDoctrine()->getManager();
            $username = $form["firstName"]->getData().'.'.$form["lastName"]->getData();

            //$existingUser = $this->getDoctrine()->getRepository('AppBundle:User')->findOneBy(array('email' => $form["email"]->getData()));
            $userManager = $this->get('fos_user.user_manager');
            $existingUser1 = $userManager->findUserByEmail($form["email"]->getData());
            $existingUser2 = $userManager->findUserByUsername($username);
            if($existingUser1 || $existingUser2){
                $conflict = true;
                $username = $username."*";
            }
            $organisation = $this->get('session')->get('organisation');
            $organisation = $this->getDoctrine()->getRepository('AppBundle:Group')->findOneBy(array('id' => $organisation->getId()));
            $address = new Address();
            $em->persist($address);

            $groupInfo = new GroupInfo();
            $groupInfo->setAddress($address);
            $em->persist($groupInfo);

            /* Start User group */
            $group = new Group();
            $group->setGroupInfo($groupInfo);
            $group->setName($username);
            $group->setType('User');
            $group->setRoles(array());
            $group->addRole('ROLE_USER');
            $group->setRef($group->getRef()+1);
            $em->persist($group);
            $organisation->addGroup($group);
            $em->persist($group);
            $userGroup = new UserGroup();
            $userGroup->setUser($user);
            $userGroup->setGroup($group);
            $em->persist($userGroup);
            $user->addUserGroup($userGroup);
            /* End User group */

            foreach($form['groups']->getData() as $group) {
                $userGroup = new UserGroup();
                $userGroup->setUser($user);
                $userGroup->setGroup($group);
                $userGroup->setRoles(array("ROLE_USER"));
                $em->persist($userGroup);
                $user->addUserGroup($userGroup);
            }

            $userGroup = new UserGroup();
            $userGroup->setUser($user);
            $userGroup->setGroup($organisation);
            $em->persist($userGroup);

            $user->addUserGroup($userGroup);


            /*
             * Als in het formulier beslist wordt om toch afzonderlijke invoervelden voor departments, organisaties, ... te maken, moet deze code worden toegevoegd.
             * Want in het formulier moet dan 'mapped=>false' komen te staan om conflicten te voorkomen en dan worden de groepen niet meer automatisch toegevoegd.
             * */
            /*foreach($form['departments']->getData() as $department){
                $user->addGroups($department);
            }*/
            /*foreach($form['organs']->getData() as $department){
                $user->addGroups($department);
            }*/

            $password = $this->get('app.default')->generatePassword();
            $user->setPlainPassword($password);
            $user->setUsername($username);
            $user->setDateCreated(new DateTime());
            $user->setRoles(array());
            $user->addRole('ROLE_USER');

            $photo = new Photo();
            $photo->setPhoto(file_get_contents('https://cdn3.iconfinder.com/data/icons/internet-and-web-4/78/internt_web_technology-13-512.png'));
            $photo->setTitle($username);
            $em->persist($photo);

            $user->setPhoto($photo);

            $tokenGenerator = $this->container->get('fos_user.util.token_generator');
            $user->setConfirmationToken($tokenGenerator->generateToken());
            $em->persist($user);


            $userSettings = new UserSettings();
            $userSettings->setUser($user);
            $department = "";
            $organisation = "";

            foreach($user->getUserGroups() as $group){
                if($group->getGroup()->getType() == 'Department'){
                    $department = $group->getGroup();
                    break;
                }
            }

            foreach($user->getUserGroups() as $group){
                if($group->getGroup()->getType() == 'Organisation'){
                    $organisation = $group->getGroup();
                    break;
                }
            }
            if($department != null) {
                $userSettings->setDefaultDepartment($department);
            }
            if($organisation != null) {
                $userSettings->setDefaultOrganisation($organisation);
            }

            $em->persist($userSettings);

            $em->flush();

            if($conflict){
                return $this->redirect($this->generateUrl('conflict', array('username' => $username)));
            }

            $this->sendActivationEmail($user, $password);

            $message = $this->get('translator')->transChoice('app.entity.user', 1);
            $message = $message.' '.$this->get('translator')->trans('app.messages.successfully_created');
            $this->get('session')->getFlashBag()->add('notice', ucfirst(strtolower($message)));

            //return $this->redirect($this->generateUrl('admin'));
            return $this->redirect($this->generateUrl('addRoles', array('username' => $user->getUsername())));
        }
        $groups = $this->getDoctrine()->getRepository('AppBundle:Group')->findAll();
        return $this->render('AppBundle:Admin:addUser.html.twig', array('form' => $form->createView(), 'groups' => $groups));
    }

    /**
     * @Route("/admin/editUser/{id}", name="editUser")
     * @param Request $request
     * @param User $user
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function editUserAction(Request $request, User $user)
    {
        $roles = $this->get('session')->get('roles');
        if(!in_array('ROLE_SUPER_ADMIN', $roles) && !in_array('ROLE_ADMIN', $roles)){
            throw new AccessDeniedException('Unauthorised access!');
        }elseif(!$user->isChildOf($this->get('session')->get('organisation'))){
            throw new AccessDeniedException('Unauthorised access!');
        }
        $userData = new ArrayCollection();
        foreach ($user->getUserGroups() as $group) {
            $userData->add($group->getGroup());
        }

        $organisation = $this->getDoctrine()->getRepository('AppBundle:Group')->findOneBy(array('id' => $this->get('session')->get('organisation')->getId()));
        $form = $this->createForm(new UserType(), $user, array(
            'action' => $this->generateUrl('editUser', array('id' => $user->getId())),
            'label' => 'app.form.next',
            'organisation' => $organisation,
            'userData' => $userData
        ));
        $form->handleRequest($request);

        if ($form->isValid()) {
            // perform some action, such as saving the task to the database
            $em = $this->getDoctrine()->getManager();
            $user->setFirstName($form["firstName"]->getData());
            $user->setLastName($form["lastName"]->getData());
            $user->setUsername($form["firstName"]->getData().'.'.$form["lastName"]->getData());
            /*foreach($user->getGroups() as $group){
                if($group->getType() == 'Department') {
                    $user->removeGroups($group);
                }
            }*/
            foreach($form["groups"]->getData() as $group){
                $userGroup = new UserGroup();
                $userGroup->setUser($user);
                $userGroup->setGroup($group);
                $userGroup->setRoles(array("ROLE_USER"));
                $exists = false;
                foreach($user->getUserGroups() as $groups){
                    if($userGroup->getUser() == $groups->getUser() && $userGroup->getGroup() == $groups->getGroup()){
                        $exists = true;
                    }
                }
                if(!$exists){
                    $em->persist($userGroup);
                    $user->addUserGroup($userGroup);
                }

            }
            $em->persist($user);
            $em->flush();
            $message = $this->get('translator')->transChoice('app.entity.user', 1);
            $message = $message.' '.$this->get('translator')->trans('app.messages.successfully_edited');
            $this->get('session')->getFlashBag()->add('notice', ucfirst(strtolower($message)));
            //return $this->redirect($this->generateUrl('admin'));
            return $this->redirect($this->generateUrl('addRoles', array('username' => $user->getUsername())));
        }
        $groups = $this->getDoctrine()->getRepository('AppBundle:Group')->findAll();
        return $this->render('AppBundle:Admin:addUser.html.twig', array('form' => $form->createView(), 'groups' => $groups));
    }

    /**
     * @Route("/admin/roles/{username}", name="addRoles")
     * @param Request $request
     * @param User $user
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function rolesAction(Request $request, User $user)
    {
        $roles = $this->get('session')->get('roles');
        if(!in_array('ROLE_SUPER_ADMIN', $roles) && !in_array('ROLE_ADMIN', $roles)){
            throw new AccessDeniedException('Unauthorised access!');
        }elseif(!$user->isChildOf($this->get('session')->get('organisation'))){
            throw new AccessDeniedException('Unauthorised access!');
        }
        $department = $this->getDoctrine()->getManager()->getRepository('AppBundle:Group')->findOneBy(array('id' => $this->get('session')->get('department')->getId()));
        $organisation = $this->getDoctrine()->getManager()->getRepository('AppBundle:Group')->findOneBy(array('id' => $this->get('session')->get('organisation')->getId()));


        /*
         * Protection: Make sure user is part of current organisation
         * */
        $correctOrganisation = false;
        foreach ($user->getUserGroups() as $userGroup) {
            if($userGroup->getGroup() == $organisation){
                $correctOrganisation = true;
            }
        }


        if($correctOrganisation) {
            $userGroup = $this->getDoctrine()->getManager()->getRepository('AppBundle:UserGroup')->findOneBy(array('user' => $user, 'group' => $this->get('session')->get('department')->getId()));
            $roles = $userGroup->getRoles();
            if(in_array('ROLE_SUPER_ADMIN', $roles)){
                $data = 'ROLE_SUPER_ADMIN';
            }elseif(in_array('ROLE_ADMIN', $roles)){
                $data = 'ROLE_ADMIN';
            }elseif(in_array('ROLE_USER++', $roles)){
                $data = 'ROLE_USER++';
            }elseif(in_array('ROLE_USER+', $roles)){
                $data = 'ROLE_USER+';
            }elseif(in_array('ROLE_USER', $roles)){
                $data = 'ROLE_USER';
            }else{
                $data = '';
            }
            $rolesLabel = $this->get('translator')->transChoice('app.entity.role', 10);
            /*$form = $this->createFormBuilder()
                ->add(
                    'roles',
                    'choice',
                    array(
                        'choices' => $this->getAccessibleRoles(),
                        'expanded' => false,
                        'multiple' => false,
                        'required' => false,
                        'empty_value' => 'app.messages.select_role',
                        'data' => $data,
                        'label' => $rolesLabel
                    )
                )
                ->add(
                    'save',
                    'submit',
                    array(
                        'label' => 'app.action.save'
                    )
                )
                ->getForm();*/
            $organisation = $this->get('session')->get('organisation');
            $userId = $user->getId();
            $departmentId = $this->get('session')->get('department')->getId();
            $userGroup = $this->getDoctrine()->getManager()->getRepository('AppBundle:UserGroup')->findOneBy(
                array('user' => $userId, 'group' => $departmentId)
            );
            $defaultData = array();
            $form = $this->createForm(new RoleType(), $defaultData, array(
                'roles' => $userGroup->getRoles()
            ));

            $form->handleRequest($request);

            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $superAdmin = false;
                $admin = false;
                if(in_array('ROLE_SUPER_ADMIN', $userGroup->getRoles())){
                    $superAdmin = true;
                }elseif(in_array('ROLE_ADMIN', $userGroup->getRoles())){
                    $admin = true;
                }

                //$userGroup->setRoles(array());
                //$roles[] = array("ROLE_USER");
                /*$userGroup->setRoles(new ArrayCollection());
                $em->persist($userGroup);
                $em->flush();*/

                $roles = $form["roles"]->getData();
                //$userGroup = $userGroup->removeAllRoles();

                $em->persist($userGroup);
                $em->flush();
                /*foreach ($userGroup->getRoles() as $role) {
                    var_dump($role);
                    $userGroup->removeRole($role);
                    $userGroup->addRole(array(""));
                    $em->persist($userGroup);
                    $em->flush();
                }*/
                /*if($superAdmin){
                    foreach($user->getUserGroups() as $userGroups){
                        foreach ($userGroups->getRoles() as $role) {
                            $userGroups->removeRole($role);
                        }
                    }
                }elseif($admin){
                    foreach($user->getUserGroups() as $userGroups){
                        foreach($organisation->getGroups() as $organisationGroup){
                            if($userGroups->getGroup()->getId() == $organisationGroup->getId()){
                                foreach ($userGroups->getRoles() as $role) {
                                    $userGroups->removeRole($role);
                                }
                            }
                        }
                    }
                }*/


                if (in_array('ROLE_SUPER_ADMIN', array_values($roles)) || in_array('ROLE_ADMIN', array_values($roles))) {
                    // Wanneer aan een gebruiker de role ROLE_SUPER_ADMIN of ROLE_ADMIN wordt toegekend, moet deze rol worden toegekend aan alle userRoles van deze gebruiker in deze organisatie
                    // Wanneer een gebruiker ROLE_SUPER_ADMIN had en zijn rol wordt verlaagd, moeten alle userRoles van deze gebruiker verlaagt worden.

                    foreach ($user->getUserGroups() as $userGroups) { // Get all usergroups
                        foreach($organisation->getGroups() as $organisationGroup){
                            if($organisationGroup->getId() == $userGroups->getGroup()->getId() || $organisation->getId() == $userGroups->getGroup()->getId()){
                                if(in_array('ROLE_SUPER_ADMIN', array_values($roles))){
                                    $userGroups->addRole('ROLE_SUPER_ADMIN');
                                }else{
                                    $userGroups->addRole('ROLE_ADMIN');
                                }

                                $em->persist($userGroups);
                                $em->flush();
                            }
                        }
                    }
                }elseif ($superAdmin || $admin) {
                    // Wanneer aan een gebruiker de role ROLE_SUPER_ADMIN of ROLE_ADMIN wordt toegekend, moet deze rol worden toegekend aan alle userRoles van deze gebruiker in deze organisatie
                    // Wanneer een gebruiker ROLE_SUPER_ADMIN had en zijn rol wordt verlaagd, moeten alle userRoles van deze gebruiker verlaagt worden.
                    $organisation = $this->getDoctrine()->getManager()->getRepository('AppBundle:Group')->find($this->get('session')->get('organisation')->getId());
                    foreach ($user->getUserGroups() as $userGroups) {
                        if (in_array($userGroups->getGroup(), $organisation->getGroups()->toArray()) || $organisation->getId() == $userGroups->getGroup()->getId()) {
                            if($superAdmin){
                                $userGroups->removeRole('ROLE_SUPER_ADMIN');
                            }else{
                                $userGroups->removeRole('ROLE_ADMIN');
                            }
                            $em->persist($userGroups);
                            $em->flush();
                        }
                    }
                }

                $userGroup->setRoles($roles);
                $em->persist($userGroup);
                $em->flush();

                return $this->redirect($this->generateUrl('admin'));
            }

            return $this->render(
                'AppBundle:Admin:addRoles.html.twig',
                array('form' => $form->createView(), 'user' => $user)
            );
        }else{
            return $this->redirect($this->generateUrl('admin'));
        }


    }

    /**
     * @Route("/admin/add/{type}", name="addGroup")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function addGroupAction(Request $request, $type)
    {
        $roles = $this->get('session')->get('roles');
        if(!in_array('ROLE_SUPER_ADMIN', $roles) && !in_array('ROLE_ADMIN', $roles)){
            throw new AccessDeniedException('Unauthorised access!');
        }
        $group = new Group();
        $label = $this->get('translator')->transChoice('app.entity.'.strtolower($type), 1);
        $label = $label.' '.$this->get('translator')->trans('app.action.create');
        $form = $this->createForm(new GroupType(), $group, array(
            'action'    => $this->generateUrl('addGroup', array('type' => $type)),
            'label'     => ucfirst(strtolower($label)),
            'tel'       => '',
            'street'    => '',
            'nr'        => '',
            'zipcode'   => '',
            'city'      => '',
            'email'     => '',
            'photo'     => ''
        ));
        switch($type){
            case "Department":
                //$form->remove('groups');
                $form->remove('photo');
                break;
            case "Organ":
                //$form->remove('groups');
                $form->remove('tel');
                $form->remove('street');
                $form->remove('nr');
                $form->remove('zipcode');
                $form->remove('city');
                $form->remove('email');
                $form->remove('photo');
                break;
            case "Fraction":
                //$form->remove('groups');
                $form->remove('tel');
                $form->remove('street');
                $form->remove('nr');
                $form->remove('zipcode');
                $form->remove('city');
                $form->remove('email');
                break;
            case "Organisation":
                $form->remove('tel');
                $form->remove('street');
                $form->remove('nr');
                $form->remove('zipcode');
                $form->remove('city');
                $form->remove('email');
                $form->remove('photo');
                break;
        }

        $form->handleRequest($request);

        if($form->isValid()){
            // perform some action, such as saving the task to the database
            $em = $this->getDoctrine()->getManager();
            $groupInfo = new GroupInfo();
            $groupInfo->setShortName($form['shortname']->getData());
            if(isset($form['tel'])) {
                $groupInfo->setTel($form['tel']->getData());
            }
            if(isset($form['street'])) {
                $address = new Address();
                $address->setStreet($form['street']->getData());
                $address->setNr($form['nr']->getData());
                $address->setZipcode($form['zipcode']->getData());
                $address->setCity($form['city']->getData());
                $em->persist($address);
                $groupInfo->setAddress($address);
            }
            if(isset($form['email'])) {
                $groupInfo->setEmail($form['email']->getData());
            }
            if(isset($form['photo'])){
                $photo = new Photo();
                $photo->setPhoto(file_get_contents($form["photo"]->getData()));
                $photo->setTitle($form['name']->getData());
                $em->persist($photo);
                $groupInfo->setPhoto($photo);
            }
            $em->persist($groupInfo);
            $group->setGroupInfo($groupInfo);

            $group->setType($type);
            $group->setRef(1);
            $group->setRoles(array());
            $em->persist($group);

            if($type != 'Organisation'){
                $organisation = $this->getDoctrine()->getRepository('AppBundle:Group')->findOneBy(array('id' => $this->get('session')->get('organisation')));
                $organisation->addGroup($group);
                if($type == 'Department') {
                    foreach ($organisation->getGroups() as $organisationGroup) {  // Remove the 'start' department from organisation,
                        // which was created during the creation of the organisation
                        if ($organisationGroup->getRef() == -666 && $organisationGroup->getName() == 'Start') {
                            $organisation->removeGroup($organisationGroup);
                            $em->remove($organisationGroup);
                        }
                    }
                }


                $em->persist($organisation);
            }else{
                /*
                 * Here we are going to create a start-group.
                 * By adding this group to the department, we make sure that the administrator has access to the newly
                 * created organisation.
                 * */
                $setupGroup = new Group();
                $setupGroup->setName("Start");
                $setupGroup->setType("Department"); // This isn't actually a department, but we define the type as Department,
                // so it is available in the 'change-department' list.
                $setupGroup->setRef(-666);
                $em->persist($setupGroup);
                $group->addGroup($setupGroup);
                $em->persist($group);
            }

            /*
             * Add admin to very new organisation, so control is possible.
             * */
            /*if($group->getType() == 'Organisation' && $this->getUser()->hasRole('ROLE_SUPER_ADMIN')){
                $this->getUser()->addGroup($group);
            }*/

            $em->flush();
            $message = $this->get('translator')->transChoice('app.entity.'.strtolower($type), 1);
            $message = $message.' '.$this->get('translator')->trans('app.messages.successfully_created');
            $this->get('session')->getFlashBag()->add('notice', ucfirst(strtolower($message)));
            return $this->redirect($this->generateUrl('admin'));
        }
        $groups = $this->getDoctrine()->getRepository('AppBundle:Group')->findAll();
        return $this->render('AppBundle:Admin:addGroup.html.twig', array('form' => $form->createView(), 'groups' => $groups, 'type' => $type));
    }

    /**
     * @Route("/admin/edit/{type}/{id}", name="editGroup")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function editGroupAction(Request $request, $type, Group $group)
    {
        $roles = $this->get('session')->get('roles');
        if(!in_array('ROLE_SUPER_ADMIN', $roles) && !in_array('ROLE_ADMIN', $roles)){
            throw new AccessDeniedException('Unauthorised access!');
        }elseif(!$group->isChildOf($this->get('session')->get('organisation'))){
            throw new AccessDeniedException('Unauthorised access!');
        }
        $label = $this->get('translator')->transChoice('app.entity.'.strtolower($type), 1);
        $label = $label.' '.$this->get('translator')->trans('app.action.edit');

        $tel        = "";
        $street     = "";
        $nr         = "";
        $zipcode    = "";
        $city       = "";
        $email      = "";

        if($type == "Department"){
            $tel        = $group->getGroupInfo()->getTel();
            $street     = $group->getGroupInfo()->getAddress()->getStreet();
            $nr         = $group->getGroupInfo()->getAddress()->getNr();
            $zipcode    = $group->getGroupInfo()->getAddress()->getZipCode();
            $city       = $group->getGroupInfo()->getAddress()->getCity();
            $email      = $group->getGroupInfo()->getEmail();
        }
        $form = $this->createForm(new GroupType(), $group, array(
            'action'    => $this->generateUrl('editGroup', array('type' => $type, 'id' => $group->getId())),
            'label'     => ucfirst(strtolower($label)),
            'tel'       => $tel,
            'street'    => $street,
            'nr'        => $nr,
            'zipcode'   => $zipcode,
            'city'      => $city,
            'email'     => $email,
            'photo'     => ''
        ));
        $form->get('shortname')->setData($group->getGroupInfo()->getShortName());

        switch($type){
            case "Department":
                $form->remove('groups');
                $form->remove('photo');
                break;
            case "Organ":
                $form->remove('groups');
                $form->remove('tel');
                $form->remove('street');
                $form->remove('nr');
                $form->remove('zipcode');
                $form->remove('city');
                $form->remove('email');
                $form->remove('photo');
                break;
            case "Fraction":
                $form->remove('groups');
                $form->remove('tel');
                $form->remove('street');
                $form->remove('nr');
                $form->remove('zipcode');
                $form->remove('email');
                $form->remove('city');
                break;
            case "Organisation":
                $form->remove('tel');
                $form->remove('street');
                $form->remove('nr');
                $form->remove('zipcode');
                $form->remove('city');
                $form->remove('photo');
                break;
        }

        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $groupInfo = $group->getGroupInfo();
            $groupInfo->setShortName($form['shortname']->getData());
            if(isset($form['tel'])) {
                $groupInfo->setTel($form['tel']->getData());
            }
            if(isset($form['street'])) {
                $address = new Address();
                $address->setStreet($form['street']->getData());
                $address->setNr($form['nr']->getData());
                $address->setZipcode($form['zipcode']->getData());
                $address->setCity($form['city']->getData());
                $em->persist($address);
                $groupInfo->setAddress($address);
            }
            if(isset($form['email'])) {
                $groupInfo->setEmail($form['email']->getData());
            }
            if(isset($form['photo'])){
                if($form['photo']->getData() != '') {
                    if($group->getGroupInfo()->getPhoto() != null){
                        $photo = $group->getGroupInfo()->getPhoto();
                        $photo->setPhoto(file_get_contents($form["photo"]->getData()));
                    }else{
                        $photo = new Photo();
                        $photo->setPhoto(file_get_contents($form["photo"]->getData()));
                        $em->persist($photo);
                        $group->getGroupInfo()->setPhoto($photo);
                    }

                }
            }
            $em->persist($groupInfo);
            $group->setGroupInfo($groupInfo);
            $group->setName($form["name"]->getData());
            $em->persist($group);
            $em->flush();

            $message = $this->get('translator')->transChoice('app.entity.'.strtolower($type), 1);
            $message = $message.' '.$this->get('translator')->trans('app.messages.successfully_edited');
            $this->get('session')->getFlashBag()->add('notice', ucfirst(strtolower($message)));
            return $this->redirect($this->generateUrl('admin'));
        }
        $groups = $this->getDoctrine()->getRepository('AppBundle:Group')->findAll();
        return $this->render('AppBundle:Admin:addGroup.html.twig', array('form' => $form->createView(), 'groups' => $groups, 'type' => $type));


    }

    /**
     * @Route("/admin/conflict/{username}", name="conflict")
     * @param Request $request
     * @param User $user
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function conflictAction(Request $request, User $user)
    {
        $roles = $this->get('session')->get('roles');
        if(!in_array('ROLE_SUPER_ADMIN', $roles) && !in_array('ROLE_ADMIN', $roles)){
            throw new AccessDeniedException('Unauthorised access!');
        }elseif(!$user->isChildOf($this->get('session')->get('organisation'))){
            throw new AccessDeniedException('Unauthorised access!');
        }
        $userManager = $this->get('fos_user.user_manager');
        //$user = $userManager->findUserByUsername($request->get('user'));

        //$user = $this->getDoctrine()->getRepository('AppBundle:User')->findOneBy(array('username' => $request->get('user')));
        $existingUser = $this->getDoctrine()->getRepository('AppBundle:User')->findOneBy(array('username' => str_replace("*", "", $user)));
        if(!$existingUser){ // Existing user does not have same username, so they have same emailadress.
            $existingUser = $this->getDoctrine()->getRepository('AppBundle:User')->findOneBy(array('email' =>  $user->getEmail()));
        }
        $defaultData = array();
        $form = $this->createFormBuilder($defaultData)
            ->add('merge', 'submit', array(
                'attr' => array('class' => 'btn-success'),
                'label' => 'app.action.merge'
            ))
            ->add('no_merge', 'submit', array(
                'attr' => array('class' => 'btn-warning'),
                'label' => 'app.action.no_merge'
            ))
            ->add('abort', 'submit', array(
                'attr' => array('class' => 'btn-danger'),
                'label' => 'app.action.abort'
            ))
            ->getForm();

        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            if($form->get('merge')->isClicked()){
                //$existingUser = $this->getDoctrine()->getRepository('AppBundle:User')->findOneBy(array('username' => str_replace("*", "", $request->get('user'))));

                foreach($user->getUserGroups() as $group){
                    // $removeGroup is the group that has to be removed
                    $removeGroup = $this->getDoctrine()->getRepository('AppBundle:UserGroup')->findOneBy(array('user' => $user, 'group' => $group));
                    if($group->getType() != 'User'){
                        if(!in_array($group, $existingUser->getUserGroups()->toArray())){ // Add groups to existing user. Ignore duplicates
                            $userGroup = new UserGroup();
                            $userGroup->setGroup($group);
                            $userGroup->setUser($existingUser);
                            $existingUser->addUserGroup($userGroup);
                        }
                        $user->removeUserGroup($removeGroup);
                    }else{
                        $user->removeUserGroup($group);
                        $em->remove($removeGroup);
                        $em->flush();
                    }
                }
                $em->persist($existingUser);
                $em->remove($user);
                $em->flush();
                return $this->redirect($this->generateUrl('admin'));
            }
            elseif($form->get('no_merge')->isClicked()){
                $password = "";
                $success = false;
                $i = 0;
                while(!$success){
                    $username = str_replace("*", $i, $user->getUsername());
                    if(!$userManager->findUserByUsername($username)){
                        $user->setUsername($username);
                        $password = $this->get('default')->generatePassword();
                        $user->setPlainPassword($password);
                        foreach($user->getUserGroups() as $group){
                            if($group->getType() == 'User'){
                                $group->setName($username);
                            }
                        }
                        $success = true;
                    }
                    $i++;
                }

                $em->persist($user);
                $em->flush();

                $this->sendActivationEmail($user, $password);

                $message = $this->get('translator')->transChoice('app.entity.user', 1);
                $message = $message.' '.$this->get('translator')->trans('app.messages.successfully_created');
                $this->get('session')->getFlashBag()->add('notice', ucfirst(strtolower($message)));


                return $this->redirect($this->generateUrl('admin'));
            }
            elseif($form->get('abort')->isClicked()){
                $repository = $this->getDoctrine()->getRepository('AppBundle:Group');
                $groups = $repository->findBy(array('name' => $user->getUsername(), 'type' => 'User'));
                $userManager = $this->get('fos_user.user_manager');

                $userManager->deleteUser($user);

                foreach ($groups as $group) {
                    $em->remove($group);
                }
                $em->flush();
                return $this->redirect($this->generateUrl('admin'));
            }
        }
        return $this->render('AppBundle:Admin:conflict.html.twig', array('form' => $form->createView(), 'user' => $user, 'existingUser' => $existingUser));


    }

    public function sendActivationEmail(User $user, $password){
        $mailer = $this->get('mailer');
        $message = $mailer->createMessage()
            ->setSubject('CADSS User confirmation')
            ->setFrom('noreply@cadss.be')
            ->setTo($user->getEmail())
            ->setBody(
                $this->renderView(
                    'AppBundle:Emails:activation.html.twig',
                    array(
                        'firstName' => $user->getFirstName(),
                        'lastName'  => $user->getLastName(),
                        'username'  => $user->getUsername(),
                        'password'  => $password,
                        'url'       => $this->generateUrl('activate', array('token' => $user->getConfirmationToken()), true)
                    )
                ),
                'text/html'
            )
        ;
        $mailer->send($message);
    }

    /*
     * This function returns all the roles that can be given to a specific user.
     * The returned roles depend on the roles of the active user.
     * */
    public function getAccessibleRoles(){
        $userRoles = array();
        $user = $this->getUser();
        foreach($user->getUserGroups() as $userGroup){        // Get UserRoles of active user
            if($userGroup->getGroup() == $this->get('session')->get('department')) {
                foreach ($userGroup->getRoles() as $role) {  // Get roles of each UserRoles
                    $userRoles[] = $role;                   // Save role for further processing
                }
            }
        }

        $roles = array("ROLE_ADMIN" => "Beheerder",
            "ROLE_USER++" => "Gebruiker met toegang tot agendas en dossiers",
            "ROLE_USER+" => "Gebruiker met toegang tot dossiers",
            "ROLE_USER" => "Gebruiker",
        );
        if(in_array("ROLE_SUPER_ADMIN", $this->get('session')->get('roles'))){ // Only superadmin can give other users ROLE_SUPER_ADMIN
            $superAdminRole = array("ROLE_SUPER_ADMIN" =>  "Systeembeheerder");
            $roles["ROLE_SUPER_ADMIN"] = "Systeembeheerder";
        }

        return $roles;
    }

    public function getDepartmentRoles(User $user, Group $department){
        $role = "";
        foreach($user->getUserGroups() as $userGroup){
            if($userGroup->getGroup() == $department){
                $role = $userGroup->getRoles()->get(0);
            }
        }
        return $role;
    }

}