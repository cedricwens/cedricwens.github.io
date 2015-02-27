<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Group;
use AppBundle\Entity\Report;
use AppBundle\Entity\User;
use Doctrine\ORM\EntityRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     *
     */
    public function indexAction()
    {
        $user = $this->getUser();
        $this->get('session')->set('photo', base64_encode(stream_get_contents($user->getPhoto()->getPhoto())));
        $defaultOrganisation = $user->getUserSettings()->getDefaultOrganisation();
        //even aangepast
        $userLockedOrganisations = $user->getLockedOrganisations();
        if($this->get('session')->get('organisation') == ""){
            if($user->getUserSettings()->getDefaultOrganisation() == ""){ // If no default organisation is set
                foreach($user->getOrganisations() as $organisation){
                    if(!in_array($organisation->getId(), $userLockedOrganisations)){ // If user isn't locked in $organisation
                        $this->get('session')->set('organisation', $organisation);
                        break;
                    }
                }
            }else {
                if(!in_array($defaultOrganisation->getId(), $userLockedOrganisations)) { // If user isn't locked in defaultOrganisation
                    $this->get('session')->set('organisation', $user->getUserSettings()->getDefaultOrganisation());
                }else{ // User is locked in defaultOrganisation
                    foreach($user->getOrganisations() as $organisation){
                        if(!in_array($organisation->getId(), $userLockedOrganisations)){ // If user isn't locked in $organisation
                            $this->get('session')->set('organisation', $organisation);
                            break;
                        }
                    }
                }
            }
        }
        if($this->get('session')->get('department') == ""){
            if($user->getUserSettings()->getDefaultDepartment() == ""){
                $this->get('session')->set('department', $user->getDepartments()->get(0));
            }else {
                $this->get('session')->set('department', $user->getUserSettings()->getDefaultDepartment());
            }
        }
        foreach($user->getUserGroups() as $userGroup){
            if($userGroup->getGroup()->getId() == $this->get('session')->get('department')->getId()){
                $this->get('session')->set('roles', $userGroup->getRoles());
                break;
            }
        }

        return $this->render('AppBundle::index.html.twig', array('user' => $user));
    }

    public function photoAction()
    {
        /*$user = $this->getUser();
        $photo = $user->getPhoto()->getPhoto();
        $data = stream_get_contents($photo);*/
        $data = $this->get('session')->get('photo');
        //$data = file_get_contents('https://cdn3.iconfinder.com/data/icons/internet-and-web-4/78/internt_web_technology-13-512.png');
        return $this->render('AppBundle:Default:photo.html.twig', array('photo' => base64_encode($data)));
    }

    /**
     * @Route("/change_organisation", name="organisation")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function organisationAction(Request $request)
    {
        $data = array();
        $form = $this->createFormBuilder($data)
            ->setAction($this->generateUrl('organisation'))
            ->getForm();

        if($this->get('session')->get('roles')!=null && in_array('ROLE_SUPER_ADMIN', array_keys($this->get('session')->get('roles')))){
            $form->add(
                'groups',
                'entity',
                array(
                    'class' => 'AppBundle:Group',
                    /*'query_builder' => function(EntityRepository $er) {
                        return $er->createQueryBuilder('g')
                            ->where('g.type = :type')
                            ->setParameter('type', 'Organisation')
                            ->orderBy('g.name', 'ASC');
                    },*/
                    'choices' => $this->getAllOrganisationDepartments(),
                    'property' => 'name',
                    'label' => 'app.form.organisation',
                    'attr' => array('class' => ''),
                    'expanded' => false,
                    'multiple' => false,
                    'mapped' => true,
                    'empty_value' => 'app.form.select_department',
                    'data' => $this->getDoctrine()->getManager()->getReference(
                        'AppBundle:Group',
                        $this->get('session')->get('department')->getId()
                    ),
                )
            );
        }else{
            $form->add(
                'groups',
                'entity',
                array(
                    'class' => 'AppBundle:Group',
                    'choices' => $this->getUserOrganisationDepartments($this->getUser()),
                    'property' => 'name',
                    'label' => 'app.form.organisation',
                    'attr' => array('class' => ''),
                    'expanded' => false,
                    'multiple' => false,
                    'mapped' => true,
                    'empty_value' => 'app.form.select_organisation',
                    'data' => $this->getDoctrine()->getRepository('AppBundle:Group')->findBy(array('id' => $this->get('session')->get('department')->getId())
                    ),
                )
            );
        }
        $form->add('save', 'submit', array(
                'label' => 'app.menu.change_department'
            )
        );
        $form->handleRequest($request);
        if($form->isValid()){
            $session = $this->get('session');
            $group = $form['groups']->getData();
            $department = $form['groups']->getData();
            $session->set('department', $department);
            $organisations = $this->getDoctrine()->getRepository('AppBundle:Group')->findBy(array('type' => 'Organisation'));
            foreach($organisations as $organisation){
                if(in_array($department, $organisation->getGroups()->toArray())){
                    $session->set('organisation', $organisation);
                    break;
                }
            }
            return $this->redirect($this->generateUrl('homepage'));
            //return $this->forward('AppBundle:Default:index');
        }

        // If user is logged in in locked organisation: log user out.
        // Code is placed here, because organisationAction is last piece of code that is rendered on every page
        $user = $this->getUser();
        if(count($user->getLockedOrganisations()) > 0){
            if (in_array($this->get('session')->get('organisation')->getId(), $user->getLockedOrganisations())) {
                $this->get('security.context')->setToken(null);
                $this->get('request')->getSession()->invalidate();
                //return $this->forward($this->generateUrl('fos_user_security_logout'));
            }
        }

        return $this->render('AppBundle:Default:organisations.html.twig', array('organisation_form' => $form->createView()));
    }

    /**
     * @Route("/report", name="report")
     *
     */
    public function reportAction(Request $request)
    {
        $report = new Report();
        $form = $this->createFormBuilder($report)
            ->add('description', 'ckeditor', array(
                'config_name' => 'report',
                'required' => true,
                'label' => 'app.form.description'
            ))
            ->add('photo', 'file', array(
                'required'    => true,
                'label' => 'app.form.image',
                'attr' => array('id' => 'uploadFile', 'class' => 'img', 'name' => 'image')
            ))
            ->add('Upload', 'submit', array(
                'label' => 'app.action.send'
            ))
            ->getForm();

        $form->handleRequest($request);

        if ($form->isValid()) {

            $em = $this->getDoctrine()->getManager();
            $report->setPhoto(file_get_contents($form["photo"]->getData()));
            $em->persist($report);
            $em->flush();
            $message = $this->get('translator')->trans('app.messages.report_successfully_send');
            $this->get('session')->getFlashBag()->add('notice', ucfirst(strtolower($message)));
            return $this->redirect($this->generateUrl('report'));
        }

        $reports = $this->getDoctrine()->getRepository('AppBundle:Report')->findAll();
        return $this->render('AppBundle:Default:report.html.twig', array('form' => $form->createView(), 'reports' => $reports));
    }

    public function displayPhotoAction(Request $request){
        $photo = base64_encode(stream_get_contents($request->get('photo')));
        return $this->render('AppBundle:Default:reportPhoto.html.twig', array('photo' => $photo));

    }

    private function getUserOrganisationDepartments(User $user){
        $userGroups = $user->getUserGroups();
        $list = array();
        foreach($userGroups as $userGroup){
            $group = $userGroup->getGroup();
            if (!in_array($group->getId(), $user->getLockedOrganisations())) {
                $list[$group->getName()] = array();
                foreach ($group->getGroups() as $department) {
                    if (in_array($department, $this->getUser()->getDepartments()->toArray())) {
                        $list[$group->getName()][$department->getName()] = $department;
                    }
                }
            }
        }
        return $list;
    }

    private function getAllOrganisationDepartments(){
        $organisations = $this->getDoctrine()->getRepository('AppBundle:Group')->findBy(array('type' => 'Organisation'));
        $list = array();
        foreach($organisations as $group){
            $list[$group->getName()] = array();
            foreach($group->getGroups() as $department){
                if($department->getType() == 'Department') {
                    $list[$group->getName()][$department->getName()] = $department;
                }
            }
        }
        return $list;
    }


}