<?php

namespace AppBundle\Controller;

use AppBundle\Entity\DossierType;
use AppBundle\Entity\Dossier;
use AppBundle\Entity\Log;
use AppBundle\Entity\Group;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use DateTime;

class DossierController extends Controller
{
    /**
     * @Route("/dossier", name="dossier")
     */
    public function indexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        //$dossiers = $this->getDoctrine()->getRepository('AppBundle:Group')->findBy(array('group' => $this->getUser()));
        $dossiers =  $this->getDoctrine()->getRepository('AppBundle:Dossier')->findAll();

        $formDossierAction = $this->createFormBuilder()
            ->add('dossiers', 'entity', array(
                    'multiple'=>true,
                    'expanded'=>true,
                    'class'=>'AppBundle:Dossier',
                    'property'=> 'id',
                    'choices' => $dossiers,
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

        $formDossierAction->handleRequest($request);

        if ($formDossierAction->isValid()) {
            if ($formDossierAction->get('add')->isClicked()) {
                return $this->redirect($this->generateUrl('dossierCreate'));
            } elseif ($formDossierAction->get('edit')->isClicked()) {
                if ($formDossierAction->get('dossiers')->getData() != null) {
                    $user = new User();
                    foreach ($formDossierAction->get('dossiers')->getData() as $selectedDossier) {
                        $dossier = $selectedDossier;
                        break;
                    }
                    return $this->redirect($this->generateUrl('editUser', array('id' => $dossier->getId())));
                } else {
                    $message = $this->get('translator')->trans('app.messages.select_dossier');
                    $this->get('session')->getFlashBag()->add('warning', $message);
                }
            }
        }

        return $this->render('AppBundle:Dossier:dossier.html.twig', array("dossiers"=> $dossiers, "form_action" => $formDossierAction->createView(), "form_my" => $formDossierAction->createView(), "form_others" => $formDossierAction->createView()));
    }

    /**
     * @Route("/dossier/create", name="dossierCreate")
     */
    public function createAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $form = $this->createFormBuilder()
            ->add('templates', 'entity', array(
                    'multiple'=>false,
                    'expanded'=>false,
                    'class'=>'AppBundle:DossierType',
                    'property'=> 'name',
                    'choices' => $this->getTemplateList(),
                    'label' => 'app.dossier.template',
                    'placeholder' => 'app.dossier.new_template',
                    'required' => false
                ))
            ->add('title', 'text', array(
                    'label' => 'app.dossier.title',
                    'attr' => array('placeholder' => 'app.dossier.title'),
                    'required'  => true
                ))
            ->add('description', 'textarea', array(
                    'label' => 'app.dossier.description',
                    'attr' => array('placeholder' => 'app.dossier.description'),
                    'required'  => true
                ))
            ->add('author', 'text', array(
                    'label' => 'app.dossier.author',
                    'attr' => array('placeholder' => 'app.dossier.author'),
                    'required'  => true
                ))
            ->add('save', 'submit', array(
                    'label' => 'app.dossier.create',
                    'attr' => array('class' => 'btn-primary')
                ))
            ->getform();

        $form->handleRequest($request);

        if($form->isValid()) {
            $dossierType = null;

            //Get dossier type
            if( $form["templates"]->getData() != null ){
                $dossierType = $this->getDoctrine()->getRepository('AppBundle:DossierType')->find($form["templates"]->getData());
            }

            $author = $this->getDoctrine()->getRepository('AppBundle:Group')->find($form["author"]->getData());
            //Search through database for the same  groups to reduce the database
            $group = new Group();
            $group->addGroup($author);
            $existingGroup = $this->findGroup($group);

            //Create new group
            if($existingGroup == null){
                $group->setType("System");
                $group->setRef(1);
                $group->setName("System");
                $group->setRoles(array());
            }

            //Use old group
            else{
                $group = $existingGroup;
                $group->setRef($group->getRef()+1);
            }
            $em->persist($group);

            //Create new log
            $log = new Log();
            $em->persist($log);

            //create new dossier
            $dossier = new Dossier();
            $dossier->setName($form["title"]->getData());
            $dossier->setDescription($form["description"]->getData());
            $dossier->setAuthor($group);
            $dossier->setDossierType($dossierType);
            $dossier->setDateCreated(new DateTime());
            $dossier->setLog($log);
            $dossier->setStatus(1);
            $em->persist($dossier);

            $em->flush();
            return $this->redirect($this->generateUrl('dossier'));
        }

        return $this->render('AppBundle:Dossier:createDossier.html.twig', array('form'=> $form->createView()));
    }

    //Generate List for combobox
    public function getTemplateList(){
        $list = array();
        return $this->getDoctrine()->getRepository('AppBundle:DossierType')->findAll();
    }

    //search for group with same childs as the given group, the function returned null if the group isn't found
    public function findGroup(Group $group){

        $systemGroups = $this->getDoctrine()->getRepository('AppBundle:Group')->findBy(array('type'=>'System'));
        foreach($systemGroups as $systemGroup) {
            if ($systemGroup->getGroups()->toArray() === $group->getGroups()->toArray())
                return $systemGroup;
        }
        return null;
    }
}