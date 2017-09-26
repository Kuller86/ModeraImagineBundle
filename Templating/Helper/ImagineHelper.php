<?php
namespace Modera\ImagineBundle\Templating\Helper;

use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Sli\ExpanderBundle\Ext\ContributorInterface;
use Symfony\Component\Templating\Helper\Helper;

/**
 * @author  Alexander Ivanitsa <alexander.ivanitsa@modera.net>
 * @copyright 2017 Modera Foundation
 */
class ImagineHelper extends Helper
{
    /** @var ContributorInterface  $interceptorUrl */
    protected $interceptorUrl;

    /** @var CacheManager $cm */
    protected $cm;

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
