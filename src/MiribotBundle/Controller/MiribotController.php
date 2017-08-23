<?php

namespace MiribotBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
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
        $answer = $this->get('miribot')->answer($request->get('input'));
        return $this->json(array(
            'answer' => $answer
        ));
    }
}
