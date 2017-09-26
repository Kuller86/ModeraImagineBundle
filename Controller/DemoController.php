<?php

namespace Modera\ImagineBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Modera\DirectBundle\Annotation\Remote;
use Doctrine\ORM\EntityManager;

/**
 * @author    Alexander Ivanitsa <alexander.ivanitsa@modera.net>
 * @copyright 2017 Modera Foundation
 */
class DemoController extends Controller
{

    public function demoAction(Request $request)
    {
        return $this->render('ModeraImagineBundle:Demo:image_list.html.twig', array());
    }
}