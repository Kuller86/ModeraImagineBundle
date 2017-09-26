<?php

namespace Modera\ImagineBundle\Tests\Unit\Templating;

use Modera\ImagineBundle\Templating\ImagineExtension;
use Modera\ImagineBundle\Imagine\Cache\CacheManager;
use Sli\ExpanderBundle\Ext\ContributorInterface;
use Modera\ImagineBundle\Templating\InterceptorUrlInterface;

class ImagineExtensionTest extends \PHPUnit_Framework_TestCase
{

    public function testImagineFilter()
    {
        $interceptorUrlInterface = \Phake::mock(InterceptorUrlInterface::class);
        $contributorInterface = \Phake::mock(ContributorInterface::class);
        $cacheManager = \Phake::mock(CacheManager::class);
        $pathWithoutResolver = '/path-without-resolver';
        $filter = 'abc';

        $ife = new ImagineExtension($contributorInterface, $cacheManager);

        \Phake::when($contributorInterface)->getItems()->thenReturn([]);
        \Phake::when($cacheManager)->getBrowserPath($pathWithoutResolver, $filter, array(), null)->thenReturn('/complete' . $pathWithoutResolver);
        $result = $ife->imagineFilter($pathWithoutResolver, $filter);
        $this->assertInternalType('string', $result);
        $this->assertEquals('/complete'.$pathWithoutResolver, $result);

        $pathResolverIn = '/path-resolver-in';
        $pathResolverOut = '/path-resolver-out';

        \Phake::when($interceptorUrlInterface)->resolve($pathResolverIn)->thenReturn($pathResolverOut);
        \Phake::when($contributorInterface)->getItems()->thenReturn([
            $interceptorUrlInterface
        ]);
        \Phake::when($cacheManager)->getBrowserPath($pathResolverOut, $filter, array(), null)->thenReturn('/resolver'.$pathResolverOut);

        $result = $ife->imagineFilter($pathResolverIn, $filter);
        $this->assertEquals('/resolver'.$pathResolverOut, $result);

        $interceptorUrlInterface2 = \Phake::mock(InterceptorUrlInterface::class);

        \Phake::when($interceptorUrlInterface)->resolve($pathResolverIn)->thenReturn(null);
        \Phake::when($interceptorUrlInterface2)->resolve($pathResolverIn)->thenReturn($pathResolverOut);
        \Phake::when($contributorInterface)->getItems()->thenReturn([
            $interceptorUrlInterface,
            $interceptorUrlInterface2,
        ]);
        \Phake::when($cacheManager)->getBrowserPath($pathResolverOut, $filter, array(), null)->thenReturn('/resolver'.$pathResolverOut);

        $result = $ife->imagineFilter($pathResolverIn, $filter);
        $this->assertEquals('/resolver'.$pathResolverOut, $result);
    }
}