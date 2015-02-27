<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Group;
use AppBundle\Entity\Workflow;
use AppBundle\Entity\WorkflowFunction;
use AppBundle\Entity\WorkflowRelation;
use AppBundle\Entity\WorkflowStep;
use AppBundle\Form\Type\WorkflowType;
use Doctrine\Common\Collections\ArrayCollection;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class WorkflowController extends Controller
{
    /**
     * @Route("/workflow", name="workflow")
     */
    public function indexAction(Request $request){
        $user = $this->getUser();
        $userGroup = $this->getDoctrine()->getRepository('AppBundle:Group')->findOneBy(array('name' => $user->getUserName()));
        $allWorkflows = $this->getDoctrine()->getRepository('AppBundle:Workflow')->findAll();
        $workflows = new ArrayCollection();
        foreach($allWorkflows as $workflow){
            //echo 'workflowGroupid:'.$workflow->getGroup()->getId().'<br />';
            //echo 'sizeof($workflow->getGroup()->getGroups()):'.sizeof($workflow->getGroup()->getGroups()).'<br />';
            foreach($workflow->getGroup()->getGroups() as $group){
                //echo 'sizeof($groups):'.sizeof($group).'<br />';
                //foreach($groups as $group){
                    //echo '$group->getId():'.$group->getId().'<br />';
                    //echo '$userGroup->getId():'.$userGroup->getId().'<br />';
                    if($userGroup->isChildOf($group) || $userGroup == $group){
                        //echo 'groupname:'.$group->getName().'<br />';
                        $workflows->add($workflow);
                    }elseif($user->isChildOf($group)){
                        //echo 'GroupName'.$group->getName().'<br />';
                        $workflows->add($workflow);
                    }
                //}

            }
            //echo '<br /><br />';
        }

        $defaultData = array();
        $form = $this->createFormBuilder($defaultData)
            ->add('workflows', 'entity', array(
                'multiple'  =>true,
                'expanded'  =>true,
                'class'     =>'AppBundle:Workflow',
                'property'  =>'id',
                'choices'   => $workflows,
                'label'     =>false
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
            ->getForm();

        $form->handleRequest($request);
        if($form->isValid()){
            print_r($form->getData());
            if($form->get('add')->isClicked()){
                return $this->redirect($this->generateUrl('addWorkflow', array('type' => 'case')));
            }else{
                return $this->redirect($this->generateUrl('addWorkflow', array('type' => 'case')));
            }
        }

        //echo 'Aantal workflows:'.sizeof($workflows);
        return $this->render('@App/Workflow/workflow.html.twig', array('workflows' => $workflows, 'form' => $form->createView()));
    }


    /**
     * @Route("/workflow/add/{type}", name="addWorkflow")
     */
    public function addWorkflowAction(Request $request, $type)
    {
        $workflow = new Workflow();

        $users = $this->getDoctrine()->getRepository('AppBundle:User')->findAll();
        $groups = $this->getDoctrine()->getRepository('AppBundle:Group')->findAll();
        $array = array();
        foreach($users as $user){
            array_push($array, $user->getUserName());
        }
        foreach($groups as $group){
            array_push($array, $group->getName());
        }

        $form = $this->createForm(new WorkflowType($this->get('session')->get('organisation')->getId()), $workflow);

        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            // TODO use findGroup function instead of immediately creating a new group

            /*
             * Combine all groups that have access to workflow in new group.
             * Add new group to workflow
             * */
            $workflowGroup = new Group();
            $workflowGroup->setName($form["name"]->getData());
            $workflowGroup->setType("System");
            $workflowGroup->setRef(1);
            foreach($form->get('groups')->getData() as $group){
                $workflowGroup->addGroup($group);
                $em->persist($workflowGroup);
            }
            $em->persist($workflowGroup);
            $workflow->setGroup($workflowGroup);
            foreach($form->get('steps') as $key => $step){
                $workflowStep = $step->getData();
                $workflowRelation = new WorkflowRelation();
                $function = new WorkflowFunction();

                $function->setStep($workflowStep);
                $function->setFunction($step->get('function')->getData());

                $workflowStep->setStatus(0); // 0: Not-active, 1: Active, 2: Finished
                $workflowStep->setStep($key);
                $workflowStep->setName($step->get('name')->getData());
                $workflowStep->setDuration($step->get('duration')->getData());
                // TODO use findGroup function instead of immediately creating a new group
                $stepGroup = new Group();
                $stepGroup->setName($step->get('name')->getData());
                $stepGroup->setType("System");
                $stepGroup->setRef(1);
                foreach($step->get('groups') as $group){
                    $stepGroup->addGroup($group);
                }
                $em->persist($stepGroup);
                $workflowStep->setGroup($stepGroup);

                //$workflowStep->setGroup($step->get('group')->getData());
                $workflowStep->setFunction($function);
                $workflowStep->setWorkflow($workflow);
                $workflowStep->setRelation($workflowRelation);

                $workflowRelation->setStep($workflowStep);

                $em->persist($function);
                $em->persist($workflowRelation);
                $em->persist($workflowStep);
                $workflow->addStep($workflowStep);
            }

            $workflow->setType($type);
            $em->persist($workflow);



            foreach($form->get('steps') as $step) {
                $workflowStep = $step->getData();
                if ($step->get('previous')->getData() == -1) {
                    $workflowStep->getRelation()->setPreviousStep(null);
                } else {
                    $previous = $this->getDoctrine()->getRepository('AppBundle:WorkflowStep')->findOneBy(array('workflow' => $workflow->getId(), 'step' => $step->get('previous')->getData()));
                    $workflowStep->getRelation()->setPreviousStep($previous);
                }
                $em->persist($workflowStep);
            }
            /*$formData = $form->getData();
            foreach ($formData['steps'] as $key => $collectionRow) {
                var_dump(explode(",",$collectionRow["group"]));
            }*/

            //var_dump($form['function']->getData());
            //$workflow->setType($type);
            $em->persist($workflow);
            $em->flush();
            return $this->redirect($this->generateUrl('workflow'));
        }

        return $this->render('@App/Workflow/addWorkflow.html.twig', array(
            'form' => $form->createView(),
            //'users' => $array
        ));
    }

    /**
     * @Route("/workflow/view/{id}", name="viewWorkflow")
     */
    public function viewWorkflowAction(Request $request, $id){
        $workflow = $this->getDoctrine()->getRepository("AppBundle:Workflow")->find($id);

        return $this->render('@App/Workflow/viewWorkflow.html.twig', array(
            'workflow' => $workflow
        ));

    }
}