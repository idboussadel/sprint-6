<?php

namespace Drupal\module_cache\Controller;

use Drupal\Core\Cache\CacheableJsonResponse;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Controller\ControllerBase;
use Drupal\node\Entity\Node;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ArticlesController extends ControllerBase
{
    /**
     * The entity type manager.
     *
     * @var \Drupal\Core\Entity\EntityTypeManagerInterface
     */
    protected $entityTypeManager;

    /**
     * Constructs an ArticlesController object.
     *
     * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
     *   The entity type manager.
     */
    public function __construct(EntityTypeManagerInterface $entity_type_manager)
    {
        $this->entityTypeManager = $entity_type_manager;
    }

    /**
     * {@inheritdoc}
     */
    public static function create(ContainerInterface $container)
    {
        return new static(
            $container->get('entity_type.manager')
        );
    }

    public function getArticles()
    {
        $node_ids = [10, 223, 45];

        $query = $this->entityTypeManager->getStorage('node')->getQuery()
            ->condition('type', 'article')
            ->condition('nid', $node_ids, 'IN')
            ->accessCheck(FALSE);

        $result = $query->execute();

        $articles = [];
        foreach ($result as $nid) {
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
        $cache_metadata->setCacheMaxAge(3600);

        $cache_metadata->addCacheTags(['node_list']);

        foreach ($node_ids as $nid) {
            $cache_metadata->addCacheTags(['node:' . $nid]);
        }

        $response->addCacheableDependency($cache_metadata);
        return $response;
    }
}
