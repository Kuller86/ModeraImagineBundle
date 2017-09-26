<?php
namespace Modera\ImagineBundle\Imagine\Cache\Resolver;

use Liip\ImagineBundle\Binary\BinaryInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Routing\RequestContext;
use Liip\ImagineBundle\Imagine\Cache\Resolver\WebPathResolver as LiipWebPathResolver;
use Liip\ImagineBundle\Imagine\Cache\Resolver\ResolverInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\HttpFoundation\File\File;

class WebPathResolver extends LiipWebPathResolver
{
    private $urlMode = UrlGeneratorInterface::ABSOLUTE_PATH;

    public function setUrlMode($urlMode)
    {
        $this->urlMode = $urlMode;
    }

    /**
     * {@inheritdoc}
     */
    public function resolve($path, $filter)
    {
        if ($this->urlMode == UrlGeneratorInterface::ABSOLUTE_PATH) {
            return '/' . $this->getFileUrl($path, $filter);
        }

        return sprintf('%s/%s',
            $this->getBaseUrl(),
            $this->getFileUrl($path, $filter)
        );
    }

    public function getStored($path, $filter)
    {
        $filePath = $this->getFilePath($path, $filter);
        $fileInfo = new File($filePath);

        return $fileInfo;
    }
}
