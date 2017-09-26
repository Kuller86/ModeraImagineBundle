<?php

namespace Modera\ImagineBundle\Imagine\Cache;

use Liip\ImagineBundle\Binary\BinaryInterface;
use Liip\ImagineBundle\Events\CacheResolveEvent;
use Liip\ImagineBundle\Imagine\Cache\Resolver\ResolverInterface;
use Liip\ImagineBundle\Imagine\Cache\SignerInterface;
use Liip\ImagineBundle\Imagine\Filter\FilterConfiguration;
use Liip\ImagineBundle\ImagineEvents;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Liip\ImagineBundle\Imagine\Cache\CacheManager as LiipCacheManager;

class CacheManager extends LiipCacheManager
{
    private $urlMode = UrlGeneratorInterface::ABSOLUTE_PATH;

    public function setUrlMode($urlMode)
    {
        $this->urlMode = $urlMode;
    }

    /**
     * Returns a web accessible URL.
     *
     * @param string $path          The path where the resolved file is expected
     * @param string $filter        The name of the imagine filter in effect
     * @param array  $runtimeConfig
     * @param string $resolver
     *
     * @return string
     */
    public function generateUrl($path, $filter, array $runtimeConfig = array(), $resolver = null)
    {
        $params = array(
            'path' => ltrim($path, '/'),
            'filter' => $filter,
        );

        if ($resolver) {
            $params['resolver'] = $resolver;
        }

        if (empty($runtimeConfig)) {
            $params['hash'] = $this->signer->sign($path);
            $filterUrl = $this->router->generate('modera_imagine_filter', $params, $this->urlMode);
        } else {
            $params['filters'] = $runtimeConfig;
            $params['hash'] = $this->signer->sign($path, $runtimeConfig);

            $filterUrl = $this->router->generate('modera_imagine_filter_runtime', $params, $this->urlMode);
        }

        return $filterUrl;
    }

    public function getStored($path, $filter, $resolver = null)
    {
        return $this->getResolver($filter, $resolver)->getStored($path, $filter);
    }
}
