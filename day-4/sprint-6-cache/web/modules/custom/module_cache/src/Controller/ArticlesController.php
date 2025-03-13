<?php

namespace Drupal\module_cache\Controller;

use Drupal\Core\Cache\CacheableJsonResponse;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Controller\ControllerBase;
use Drupal\node\Entity\Node;

class ArticlesController extends ControllerBase
{

    public function getArticles()
    {
        $node_ids = [10, 223, 45];
        $articles = [];

        foreach ($node_ids as $nid) {
            $node = Node::load($nid);
            if ($node) {
                $articles[] = [
                    'nid' => $node->id(),
                    'title' => $node->getTitle(),
                ];
            }
        }

        $response = new CacheableJsonResponse($articles);
        $cache_metadata = new CacheableMetadata();
        $cache_metadata->setCacheMaxAge(60);
        $cache_metadata->addCacheTags(['node_list']);

        $response->addCacheableDependency($cache_metadata);
        return $response;
    }
}
