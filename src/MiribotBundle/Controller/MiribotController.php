<?php

namespace MiribotBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class MiribotController extends Controller
{
    public function indexAction()
    {
        return $this->render('MiribotBundle:Miribot:index.html.twig');
    }

    public function answerAction(Request $request)
    {
        //ini_set('memory_limit', '2G');
        //ini_set('max_execution_time', '900');
        $answer = $this->get('miribot')->answer($request->get('input'));
        return new JsonResponse($answer);
    }

    /**
     * Check if user data exist
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function userdataexistAction()
    {
        $userData = $this->get('session')->get('userdata');

        return new JsonResponse(array(
            'has_user_data' => ($userData) ? 1 : 0
        ));
    }

    /**
     * Save user data to session
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function saveuserdataAction(Request $request)
    {
        $session = $this->get('session');
        $userData = $request->get('userdata');

        if (empty($userData)) {
            return new JsonResponse(array('done' => 0));
        }

        $session->set('userdata', $userData);
        return new JsonResponse(array('done' => 1));
    }
}
