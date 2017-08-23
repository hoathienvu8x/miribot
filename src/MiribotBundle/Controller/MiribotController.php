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
        ini_set('memory_limit', '2G');
        $answer = $this->get('miribot')->answer($request->get('input'));
        return $this->json(array(
            'answer' => $answer
        ));
    }
}
