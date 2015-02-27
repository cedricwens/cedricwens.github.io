<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Address;
use AppBundle\Entity\Group;
use AppBundle\Entity\User;
use AppBundle\Entity\UserSettings;
use AppBundle\Form\Type\GroupType;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class SettingsController extends Controller
{
    /**
     * @Route("/settings", name="settings")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(Request $request)
    {
        mb_internal_encoding('UTF-8');
        $user = $this->getDoctrine()->getRepository('AppBundle:User')->find($this->getUser()->getId());
        $group = new Group();
        foreach($user->getUserGroups() as $userGroup){
            if($userGroup->getGroup()->getType() == 'User'){
                $group = $userGroup->getGroup();
            }
        }

        $standardLabel = $this->get('translator')->transChoice('app.form.default', 1);
        $organisationLabel = $standardLabel.' '.strtolower($this->get('translator')->transChoice('app.entity.organisation', 1));
        $departmentLabel = $standardLabel.' '.mb_convert_case($this->get('translator')->transChoice('app.entity.department', 1), MB_CASE_LOWER, "UTF-8");
        $emptyOrganisationLabel = $this->get('translator')->trans('app.error.none_found', array('%group%' => strtolower($this->get('translator')->transChoice('app.entity.organisation', 10))));
        $emptyDepartmentLabel = $this->get('translator')->trans('app.error.none_found', array('%group%' => strtolower($this->get('translator')->transChoice('app.entity.department', 10))));
        $invalidMessage = $this->get('translator')->trans('app.error.passwords_do_not_match');

        $defaultData = array('message' => 'settingsAction');
        $settingsform = $this->get('form.factory')->createNamedBuilder('settingsform', 'form', $defaultData)
            ->add('email', 'text', array(
                'label' => 'app.form.email',
                'data' => $this->getUser()->getEmail()
            ))
            /*->add('address', 'entity', array(
                'class' => 'AppBundle\Entity\Address',
                'property'
            ))*/
            ->add('street', 'text', array(
                'label' => 'app.form.address',
                'attr' => array('placeholder' => 'app.entity.address.street', 'value' => $group->getGroupInfo()->getAddress()->getStreet()),
                'required' => false,

            ))
            ->add('nr', 'text', array(
                'attr' => array('placeholder' => 'app.entity.address.nr', 'value' => $group->getGroupInfo()->getAddress()->getNr()),
                'required' => false
            ))
            ->add('zipcode', 'text', array(
                'attr' => array('placeholder' => 'app.entity.address.zipcode', 'value' => $group->getGroupInfo()->getAddress()->getZipcode()),
                'required' => false
            ))
            ->add('city', 'text', array(
                'attr' => array('placeholder' => 'app.entity.address.city', 'value' => $group->getGroupInfo()->getAddress()->getCity()),
                'required' => false
            ))
            ->add('telnumber', 'text', array(
                'attr' => array('placeholder' => 'app.form.telnumber', 'value' => $group->getGroupInfo()->getTel()),
                'label' => 'app.form.telnumber',
                'required' => false
            ))
            ->add('password', 'password', array(
                'label' => 'app.form.password',
                'required' => true
            ))
            ->add('newpass', 'repeated', array(
                'type' => 'password',
                'required' => false,
                'invalid_message' => $invalidMessage,
                'options' => array('attr' => array('class' => 'password-field')),
                'first_options' => array('label' => 'app.form.newpass'),
                'second_options' => array('label' => 'app.form.repeatpass')
            ))
           /* ->add('repeatpass', 'password', array(
                'label' => 'app.form.repeatpass',
                'required' => false
            ))*/
            ->add('organisations', 'entity', array(
                'class' => 'AppBundle:Group',
                /*'query_builder' => function(EntityRepository $er) {
                    return $er->createQueryBuilder('d')
                        ->where('d.type = :type')
                        ->setParameter('type', 'Organisation')
                        ->orderBy('d.name', 'ASC');
                },*/
                'choices' => $this->getOrganisations($this->getUser()),
                'property' => 'name',
                'label' => $organisationLabel,
                'attr' => array('class' => ''),
                'data' => $this->getDoctrine()->getEntityManager()->getReference('AppBundle:Group',$this->get('session')->get('organisation')->getId()),
                'expanded' => false,
                'multiple' => false,
                'required' => false,
                'empty_value' => 'app.form.select_organisation',
            ))
            ->add('departments', 'entity', array(
                'class' => 'AppBundle:Group',
                /*'query_builder' => function(EntityRepository $er) {
                    return $er->createQueryBuilder('d')
                        ->where('d.type = :type')
                        ->setParameter('type', 'Department')
                        ->orderBy('d.name', 'ASC');
                },*/
                'choices' => $this->getDepartments($this->getUser()),
                'property' => 'name',
                'label' => $departmentLabel,
                'attr' => array('class' => ''),
                'data' => $this->getDoctrine()->getEntityManager()->getReference('AppBundle:Group',$this->get('session')->get('department')->getId()),
                'expanded' => false,
                'multiple' => false,
                'required' => false,
                'empty_value' => $emptyDepartmentLabel
            ))
            /*->add('language', 'choice', array(
                'choices' => array(
                    'en' => 'app.language.english',
                    'fr' => 'app.language.french',
                    'nl' => 'app.language.dutch'
                )

            ))*/
            ->add('save', 'submit', array(
                'label' => 'app.action.save'
            ))
            ->getForm();

        $settingsform->handleRequest($request);



        $photo = $this->getUser()->getPhoto()->getPhoto();
        $data = stream_get_contents($photo);
        if ($settingsform->isValid()) {
            $user = $this->getUser();
            $factory = $this->get('security.encoder_factory');
            $encoder = $factory->getEncoder($user);
            $bool = ($encoder->isPasswordValid($user->getPassword(),$settingsform['password']->getData(),$user->getSalt())) ? true : false;

            if($bool) {
                $this->getUser()->setEmail = $settingsform->get('email')->getData();
                $em = $this->getDoctrine()->getManager();
                $user = $this->getUser();
                $newPassword = $settingsform['newpass']['first']->getData();
                $repeatPassword = $settingsform['newpass']['second']->getData();
                if ($newPassword != null && $repeatPassword != null) {
                    if ($newPassword == $repeatPassword) {
                        $userManager = $this->get('fos_user.user_manager');
                        $user = $userManager->findUserBy(array('id' => $user->getId()));
                        $user->setPlainPassword($newPassword);
                        $userManager->updateUser($user);
                    } else {
                        $message = $this->get('translator')->trans('app.error.passwords_do_not_match');
                        $this->get('session')->getFlashBag()->add('alert', ucfirst(strtolower($message)));
                    }
                }
                $group->getGroupInfo()->getAddress()->setStreet($settingsform['street']->getData());
                $group->getGroupInfo()->getAddress()->setNr($settingsform['nr']->getData());
                $group->getGroupInfo()->getAddress()->setZipcode($settingsform['zipcode']->getData());
                $group->getGroupInfo()->getAddress()->setCity($settingsform['city']->getData());
                $group->getGroupInfo()->setTel($settingsform['telnumber']->getData());
                /*$user->setLanguage($settingsform['language']->getData());
                $request->setLocale($settingsform['language']->getData());*/
                $user->setEmail($settingsform->get('email')->getData());

                /*$allUserSettings = $this->getDoctrine()->getRepository('AppBundle:UserSettings')->findAll();
                $userSettings = new UserSettings();
                foreach($allUserSettings as $settings){

                    if($settings->getUser()->getId() == $user->getId()){
                        $userSettings = $settings;
                        $userSettings->setUser($this->getUser());
                        break;
                    }
                }*/
                if(!$this->getUser()->hasRole('ROLE_SUPER_ADMIN')){
                    $userSettings = $user->getUserSettings();
                    $userSettings->setDefaultDepartment($settingsform['departments']->getData());
                    $userSettings->setDefaultOrganisation($settingsform['organisations']->getData());
                    $em->persist($userSettings);
                }

                $em->persist($user);
                $em->flush();

                $message = $this->get('translator')->trans('app.messages.settings_saved');
                $this->get('session')->getFlashBag()->add('success', ucfirst(strtolower($message)));

                return $this->redirect($this->generateUrl('settings'));
            }else{
                $message = $this->get('translator')->trans('app.error.wrong_password');
                $this->get('session')->getFlashBag()->add('warning', ucfirst(strtolower($message)));
                return $this->redirect($this->generateUrl('settings'));
            }
        }
        return $this->render('AppBundle:Settings:settings.html.twig', array('settingsform' => $settingsform->createView(), 'user' => $this->getUser(), 'photo' => base64_encode($data)));
    }

    /*
     * Get user organisations
     * */
    public function getOrganisations(User $user){
        $organisations = new ArrayCollection();
        foreach($user->getGroups() as $group){
            if($group->getType() == 'Organisation'){
                $organisations->add($group);
            }
        }
        return $organisations;
    }

    /*
     * Get user departments
     * */
    public function getDepartments(User $user){
        $departments = new ArrayCollection();
        foreach($user->getGroups() as $group){
            if($group->getType() == 'Department'){
                $departments->add($group);
            }
        }
        return $departments;
    }

    /**
     * @Route("/settings/photo", name="photo")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function uploadAction(Request $request){
        $user = $this->getUser();
        $form = $this->createFormBuilder($user)
            ->add('photo', 'file', array(
                'required'    => true,
                'data_class' => null,
                'attr' => array('id' => 'uploadFile', 'class' => 'img', 'name' => 'image'),
                'mapped' => false
            ))
            ->add('Upload', 'submit', array(
                'label' => 'app.action.upload'
            ))
            ->getForm();

        $form->handleRequest($request);


        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $photo = $user->getPhoto();
            $url = 'destination .jpg';
            $this->compressImageAction($form["photo"]->getData(), $url, 80);
            $buffer = file_get_contents($url);
            $photo->setPhoto($buffer);
            //$photo->setPhoto(file_get_contents($form["photo"]->getData()));
            $em->persist($user);
            $em->flush();
            $this->get('session')->set('photo', base64_encode(file_get_contents($form["photo"]->getData())));
            $value= $request->get('path');
            if(isset($value)){
                return $this->redirect($this->generateUrl('settings'));
            }else {
                return $this->redirect($this->generateUrl('settings'));
            }
        }

        return $this->render('AppBundle:Settings:photo.html.twig', array('form' => $form->createView()));
    }

    public function compressImageAction($sourceUrl, $destinationUrl, $quality) {

        $info = getimagesize($sourceUrl);

        if ($info['mime'] == 'image/jpeg')
            $image = imagecreatefromjpeg($sourceUrl);

        elseif ($info['mime'] == 'image/gif')
            $image = imagecreatefromgif($sourceUrl);

        elseif ($info['mime'] == 'image/png')
            $image = imagecreatefrompng($sourceUrl);

        imagejpeg($image, $destinationUrl, $quality);
        return $destinationUrl;
    }
}