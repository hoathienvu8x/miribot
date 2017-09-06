<?php

namespace BrainBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;


class WernickeController extends Controller
{
    public function markovAction(Request $request)
    {

        $form = $this->createFormBuilder()
            ->add('input_text', TextareaType::class, array(
                'required' => true,
                'label' => 'Input Text',
                'attr' => array(
                    'cols' => '100',
                    'rows' => '25',
                    'placeholder' => 'Enter text input here...'
                )
            ))
            ->add('length', TextType::class, array(
                'required' => true,
                'label' => 'Length',
                'data' => '300',
                'attr' => array(
                    'placeholder' => 'Enter length here...'
                )
            ))
            ->add('submit', SubmitType::class, array('label' => 'Submit'))
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted()) {

            if (!$form->isValid()) {
                $error = $form->getErrors();
                return $this->render('BrainBundle:Wernicke:markov.html.twig', array(
                    'form' => $form->createView(),
                    'error' => $error
                ));
            }

            $data = $form->getData();
            $inputText = $data['input_text'];
            $length = $data['length'];

            if ($inputText) {
                if (!$length) {
                    $length = 300;
                }
                $markov = $this->get('brain.components.string')->markovGenerator($inputText, $length, 3);
            } else {
                $form->addError(new FormError('Input text must be provided'));
                $error = $form->getErrors();
                return $this->render('BrainBundle:Wernicke:markov.html.twig', array(
                    'form' => $form->createView(),
                    'error' => $error
                ));
            }

            return $this->render('BrainBundle:Wernicke:markov.html.twig', array(
                'form' => $form->createView(),
                'output' => $markov
            ));
        }

        return $this->render('BrainBundle:Wernicke:markov.html.twig', array(
            'form' => $form->createView(),
        ));
    }
}
