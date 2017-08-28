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
        ini_set('max_execution_time', '900');
        $answer = $this->get('miribot')->answer($request->get('input'));
        return $this->json($answer);
    }

    public function wikiAction(Request $request)
    {
        $keyword = $request->get('keyword');
        $text = $this->get('helper')->template->searchWikipedia($keyword, "en", true);
        return new Response($text);
    }
}
