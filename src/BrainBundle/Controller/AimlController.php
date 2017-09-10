<?php

namespace BrainBundle\Controller;

use BrainBundle\Entity\Aiml;
use BrainBundle\Entity\AimlUploader;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * Aiml controller.
 *
 */
class AimlController extends Controller
{
    public function uploadAction(Request $request)
    {
        $uploader = new AimlUploader();
        $form = $this->createForm('BrainBundle\Form\AimlUploaderType', $uploader);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($uploader);
            $em->flush();
        }

        return $this->render('BrainBundle:aiml:upload.html.twig', array(
            'uploader' => $uploader,
            'form' => $form->createView(),
        ));
    }
}
