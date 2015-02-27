<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use FOS\UserBundle\Controller\SecurityController as BaseController;
use AppBundle\Entity\User;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Form\Type\LoginFormType;

class SecurityController extends BaseController
{
    /**
     * @Route("/activate/{token}", name="activate")
     * @param $token
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function indexAction($token)
    {
        $em = $this->getDoctrine()->getManager();
        $userManager = $this->get('fos_user.user_manager');
        $user = $userManager->findUserByConfirmationToken($token);
        $user->setEnabled(true);
        $user->setStatus(2);
        $user->setConfirmationToken($this->container->get('fos_user.util.token_generator')->generateToken());
        $userManager->updateUser($user);
        $em->flush();

        $message = $this->get('translator')->trans('app.messages.successfully_activated');
        $this->get('session')->getFlashBag()->add('success', $message);

        return $this->redirect($this->generateUrl('homepage'));
    }

    /**
     * Overriding the FOS default method so that we can choose a template
     *
     */
    protected function renderLogin(array $data)
    {
        //$form = $this->get('form.factory')->create(new LoginFormType(), null);

        return $this->container->get('templating')->renderResponse( 'AppBundle:Security:login.html.twig', $data);
    }
    /**
     * Overriding the FOS default method
     *
     */
    public function loginAction(Request $request)
    {
        /** @var $session \Symfony\Component\HttpFoundation\Session\Session */
        $session = $request->getSession();

        // get the error if any (works with forward and redirect -- see below)
        if ($request->attributes->has(SecurityContextInterface::AUTHENTICATION_ERROR)) {
            $error = $request->attributes->get(SecurityContextInterface::AUTHENTICATION_ERROR);
        } elseif (null !== $session && $session->has(SecurityContextInterface::AUTHENTICATION_ERROR)) {
            $error = $session->get(SecurityContextInterface::AUTHENTICATION_ERROR);
            $session->remove(SecurityContextInterface::AUTHENTICATION_ERROR);
        } else {
            $error = null;
        }
        if (!$error instanceof AuthenticationException) {
            $error = null; // The value does not come from the security component.
        }
        // last username entered by the user
        $lastUsername = (null === $session) ? '' : $session->get(SecurityContextInterface::LAST_USERNAME);
        $csrfToken = $this->has('form.csrf_provider')
            ? $this->get('form.csrf_provider')->generateCsrfToken('authenticate')
            : null;
        return $this->renderLogin(array(
                'last_username' => $lastUsername,
                'error'         => $error,
                'csrf_token' => $csrfToken,
            ));
    }
}