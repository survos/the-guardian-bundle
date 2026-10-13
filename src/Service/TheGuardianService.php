<?php

// load and translate news

declare(strict_types=1);

namespace Survos\TheGuardianBundle\Service;

use Guardian\Entity\APIEntity;
use Guardian\Entity\Content;
use Guardian\Entity\Tags;
use Guardian\GuardianAPI;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

class TheGuardianService
{
    public function __construct(
        private ?string $apiKey = null,
        private int $cacheTimeout = 0,
        private ?GuardianAPI $guardianAPI = null,
        private ?CacheInterface $cache = null,
    ) {
        $this->guardianAPI = new GuardianAPI($this->apiKey);
    }

    public function fetch(APIEntity $apiEntity)
    {
        ($r = new \ReflectionMethod($apiEntity, 'buildUrl'))
            ->setAccessible(true);
        $url = $r->invoke($apiEntity);
        $key = hash('xxh3', $url);
        $response = $this->cache->get($key, function (ItemInterface $item) use ($url, $apiEntity) {
            $item->expiresAfter($this->cacheTimeout);
            $results = $apiEntity->makeApiCall($url);

            return json_decode($results)->response;
        });

        return $response;
    }

    public function content(?string $q = null)
    {
    }

    public function contentApi(): Content
    {
        return $this->guardianAPI->content();
    }

    public function tagsApi(): Tags
    {
        return $this->guardianAPI->tags();
    }
}
