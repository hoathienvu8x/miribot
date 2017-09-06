<?php
/**
 * Created by PhpStorm.
 * User: Khue Quang Nguyen
 * Date: 05-Sep-17
 * Time: 20:37
 */

namespace BrainBundle\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class IndexController extends Controller
{
    public function indexAction()
    {
        return $this->render('BrainBundle:index.html.twig');
    }
}