<?php
namespace Modera\ImagineBundle\Templating;

use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Sli\ExpanderBundle\Ext\ContributorInterface;

/**
 * @author  Alexander Ivanitsa <alexander.ivanitsa@modera.net>
 * @copyright 2017 Modera Foundation
 */
class ImagineExtension extends \Twig_Extension
{
    /** @var ContributorInterface  $interceptorUrl */
    private $interceptorUrl;

    /** @var CacheManager $cm */
    private $cm;

    /**
     * @param ContributorInterface $interceptorUrl
     * @param CacheManager $cacheManager
     */
    public function __construct(ContributorInterface $interceptorUrl, CacheManager $cacheManager)
    {
        $this->interceptorUrl = $interceptorUrl;
        $this->cm = $cacheManager;
    }

    /**
     * {@inheritdoc}
     */
    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('imagine', array($this, 'imagineFilter')),
        );
    }

    /**
     * Gets the browser path for the image and filter to apply.
     *
     * @param string $path
     * @param string $filter
     * @param array $runtimeConfig
     * @param null $resolver
     *
     * @return string
     */
    public function imagineFilter($path, $filter, array $runtimeConfig = array(), $resolver = null)
    {
        foreach ($this->interceptorUrl->getItems() as $interceptor) {
            $newPath = $interceptor->resolve($path);

            if (!is_null($newPath)) {
                $path = $newPath;
                break;
            }
        }

        return $this->cm->getBrowserPath($path, $filter, $runtimeConfig, $resolver);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'modera_imagine';
    }
}
