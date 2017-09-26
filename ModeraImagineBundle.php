<?php

namespace Modera\ImagineBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Sli\ExpanderBundle\Ext\ExtensionPoint;

/**
 * @author  Alexander Ivnitsa <alexander.ivanitsa@modera.net>
 * @copyright 2017 Modera Foundation
 */
class ModeraImagineBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        $providers = new ExtensionPoint('modera_imagine.interceptor_url');
        $providers->setDescription('Intercepts the URL and processes it');
        $container->addCompilerPass($providers->createCompilerPass());
    }
}
