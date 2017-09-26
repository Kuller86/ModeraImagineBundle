<?php

namespace Modera\ImagineBundle\Controller;

use Imagine\Exception\RuntimeException;
use Liip\ImagineBundle\Exception\Binary\Loader\NotLoadableException;
use Liip\ImagineBundle\Exception\Imagine\Filter\NonExistingFilterException;
use Liip\ImagineBundle\Imagine\Cache\SignerInterface;
use Liip\ImagineBundle\Imagine\Data\DataManager;
use Liip\ImagineBundle\Imagine\Filter\FilterManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\Response;
use Modera\ImagineBundle\Imagine\Cache\CacheManager;
use Symfony\Component\HttpFoundation\File\File;

class ImagineController
{
    /**
     * @var DataManager
     */
    protected $dataManager;

    /**
     * @var FilterManager
     */
    protected $filterManager;

    /**
     * @var CacheManager
     */
    protected $cacheManager;

    /**
     * @var SignerInterface
     */
    protected $signer;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param DataManager          $dataManager
     * @param FilterManager        $filterManager
     * @param CacheManager   $cacheManager
     * @param SignerInterface      $signer
     * @param LoggerInterface|null $logger
     */
    public function __construct(
        DataManager $dataManager,
        FilterManager $filterManager,
        CacheManager $cacheManager,
        SignerInterface $signer,
        LoggerInterface $logger = null
    ) {
        $this->dataManager = $dataManager;
        $this->filterManager = $filterManager;
        $this->cacheManager = $cacheManager;
        $this->signer = $signer;
        $this->logger = $logger;
    }

    /**
     * This action applies a given filter to a given image, optionally saves the image and outputs it to the browser at the same time.
     *
     * @param Request $request
     * @param string  $path
     * @param string  $filter
     *
     * @throws \RuntimeException
     * @throws BadRequestHttpException
     *
     * @return Response
     */
    public function filterAction(Request $request, $hash, $path, $filter)
    {
        // decoding special characters and whitespaces from path obtained from url
        $path = urldecode($path);
        $resolver = $request->get('resolver');

        try {
            if (true !== $this->signer->check($hash, $path)) {
                throw new BadRequestHttpException(sprintf(
                    'Signed url does not pass the sign check for path "%s" and filter "%s"',
                    $path,
                    $filter
                ));
            }

            if (!$this->cacheManager->isStored($path, $filter, $resolver)) {
                try {
                    $binary = $this->dataManager->find($filter, $path);
                } catch (NotLoadableException $e) {
                    if ($defaultImageUrl = $this->dataManager->getDefaultImageUrl($filter)) {
                        return new RedirectResponse($defaultImageUrl);
                    }

                    throw new NotFoundHttpException('Source image could not be found', $e);
                }

                $this->cacheManager->store(
                    $this->filterManager->applyFilter($binary, $filter),
                    $path,
                    $filter,
                    $resolver
                );
            }

            /** @var File $storedFile */
            $storedFile = $this->cacheManager->getStored($path, $filter, $resolver);

            $content = @file_get_contents($storedFile->getRealPath());

            if (empty($content)) {
                throw new FileNotFoundException(sprintf('File %s not found', $storedFile->getFilename()));
            }
            $headers = [
                'Content-Type' => $storedFile->getMimeType(),
            ];

            $response = new Response($content, 200, $headers);
            return $response;
        } catch (NonExistingFilterException $e) {
            $message = sprintf('Could not locate filter "%s" for path "%s". Message was "%s"', $filter, $hash . $path, $e->getMessage());

            if (null !== $this->logger) {
                $this->logger->debug($message);
            }

            throw new NotFoundHttpException($message, $e);
        } catch (RuntimeException $e) {
            throw new \RuntimeException(sprintf('Unable to create image for path "%s" and filter "%s". Message was "%s"', $hash . '/' . $path, $filter, $e->getMessage()), 0, $e);
        }
    }


    /**
     * This action applies a given filter to a given image, optionally saves the image and outputs it to the browser at the same time.
     *
     * @param Request $request
     * @param string  $hash
     * @param string  $path
     * @param string  $filter
     *
     * @throws \RuntimeException
     * @throws BadRequestHttpException
     *
     * @return Response
     */
    public function filterRuntimeAction(Request $request, $hash, $path, $filter)
    {
        $resolver = $request->get('resolver');

        try {
            $filters = $request->query->get('filters', array());

            if (!is_array($filters)) {
                throw new NotFoundHttpException(sprintf('Filters must be an array. Value was "%s"', $filters));
            }

            if (true !== $this->signer->check($hash, $path, $filters)) {
                throw new BadRequestHttpException(sprintf(
                    'Signed url does not pass the sign check for path "%s" and filter "%s" and runtime config %s',
                    $path,
                    $filter,
                    json_encode($filters)
                ));
            }

            try {
                $binary = $this->dataManager->find($filter, $path);
            } catch (NotLoadableException $e) {
                if ($defaultImageUrl = $this->dataManager->getDefaultImageUrl($filter)) {
                    return new RedirectResponse($defaultImageUrl);
                }

                throw new NotFoundHttpException(sprintf('Source image could not be found for path "%s" and filter "%s"', $path, $filter), $e);
            }

            $rcPath = $this->cacheManager->getRuntimePath($path, $filters);

            $this->cacheManager->store(
                $this->filterManager->applyFilter($binary, $filter, array(
                    'filters' => $filters,
                )),
                $rcPath,
                $filter,
                $resolver
            );

            /** @var File $storedFile */
            $storedFile = $this->cacheManager->getStored($rcPath, $filter, $resolver);

            $content = @file_get_contents($storedFile->getRealPath());

            if (empty($content)) {
                throw new FileNotFoundException(sprintf('File %s not found', $storedFile->getFilename()));
            }
            $headers = [
                'Content-Type' => $storedFile->getMimeType(),
            ];

            $response = new Response($content, 200, $headers);
            return $response;
        } catch (NonExistingFilterException $e) {
            $message = sprintf('Could not locate filter "%s" for path "%s". Message was "%s"', $filter, $hash.'/'.$path, $e->getMessage());

            if (null !== $this->logger) {
                $this->logger->debug($message);
            }

            throw new NotFoundHttpException($message, $e);
        } catch (RuntimeException $e) {
            throw new \RuntimeException(sprintf('Unable to create image for path "%s" and filter "%s". Message was "%s"', $hash.'/'.$path, $filter, $e->getMessage()), 0, $e);
        }
    }
}
