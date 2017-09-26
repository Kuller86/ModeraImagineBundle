<?php
namespace Modera\ImagineBundle\Templating;

/**
 * @author  Alexander Ivanitsa <alexander.ivanitsa@modera.net>
 * @copyright 2017 Modera Foundation
 */
interface InterceptorUrlInterface
{
    public function resolve($path);
}