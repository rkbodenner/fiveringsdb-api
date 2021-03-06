<?php

namespace AppBundle\Service;

use AppBundle\Behavior\Entity\Timestampable;
use AppBundle\Search\PaginatedSearchInterface;
use AppBundle\Search\SearchInterface;
use JMS\Serializer\SerializationContext;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;

/**
 * Description of ApiService
 *
 * @author Alsciende <alsciende@icloud.com>
 */
class ApiService
{
    const USE_LAST_UPDATED = false;

    /**
     *
     * @var RequestStack
     */
    private $requestStack;

    /**
     *
     * @var \JMS\Serializer\Serializer
     */
    private $serializer;

    /**
     *
     * @var integer
     */
    private $httpCacheMaxAge;

    public function __construct (RequestStack $requestStack, \JMS\Serializer\Serializer $serializer, $httpCacheMaxAge)
    {
        $this->requestStack = $requestStack;
        $this->serializer = $serializer;
        $this->httpCacheMaxAge = $httpCacheMaxAge;
    }

    public function buildResponse ($data = null, $groups = [])
    {
        $request = $this->requestStack->getCurrentRequest();
        $response = $this->getEmptyResponse();

        if ($this->isPublic($request)) {
            // make response public and cacheable
            $response->setPublic();
            $response->setMaxAge($this->httpCacheMaxAge);
            $dateUpdate = $this->getDateUpdate($data);
            if (self::USE_LAST_UPDATED) { //!\\ issue with sub-entities
                // find last update of data
                $response->setLastModified($dateUpdate);
                // compare to request header
                if ($response->isNotModified($request)) {
                    return $response;
                }
            }
        }

        $content = $this->buildContent($data, $groups);
        $content['success'] = true;
        $content['last_updated'] = isset($dateUpdate) ? $dateUpdate->format('c') : null;

        $serialized = $this->serializer->serialize($content, 'json', SerializationContext::create()->setGroups($groups));

        $response->setContent($serialized);

        return $response;
    }

    public function buildContent ($data = null, $groups = [])
    {
        $content = [];
        if ($data instanceof SearchInterface) {
            $content['records'] = $data->getRecords();
            $content['size'] = $data->getTotal();
            if ($data instanceof PaginatedSearchInterface) {
                $content['page'] = $data->getPage();
                $content['limit'] = $data->getLimit();
            }
        } else if (is_array($data)) {
            $content['records'] = $data;
            $content['size'] = count($content['records']);
        } else {
            $content['record'] = $data;
        }

        return $content;
    }

    public function getDateUpdate ($data)
    {
        if (is_array($data) === false) {
            $data = [$data];
        }

        return array_reduce(
            $data, function ($carry, Timestampable $item) {
            if ($carry && $item->getUpdatedAt() < $carry) {
                return $carry;
            } else {
                return $item->getUpdatedAt();
            }
        }
        );
    }

    public function getEmptyResponse ()
    {
        $response = new Response();
//        $response->headers->set('Access-Control-Allow-Origin', '*');
        $response->headers->set('Content-Type', 'application/json; charset=UTF-8');

        return $response;
    }

    public function isPublic (Request $request): bool
    {
        return $request->attributes->get('public') ?? false;
    }

    public function setPublic (Request $request, bool $public = true)
    {
        $request->attributes->set('public', $public);
    }
}
