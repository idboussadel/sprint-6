# Drupal Advanced Sprint 6

### Table of Contents
- [Day 3: Work with configuration & features](#day-3-work-with-configuration-features)
  - [Practice Configuration Sync](#practice-configuration-sync)
  - [Practice Features](#practice-features)
- [Day 4: Work with Cache](#day-4-work-with-cache)
- [Day 5: Work with Data Migration](#day-5-work-with-data-migration)

---  

## Day 3: Work with configuration & features

### Practice Configuration Sync

Let's create a new split for developement configuration :

<img width="1360" alt="image" src="https://github.com/user-attachments/assets/4d32db9c-b0a2-4071-97f4-257306236587" />

<img width="1375" alt="image" src="https://github.com/user-attachments/assets/e66d5319-8132-4231-8b30-89233f65b218" />

Then export it using :

```bash
 ./vendor/bin/drush config:export
```

<img width="997" alt="image" src="https://github.com/user-attachments/assets/ec49d4b7-1aad-4dcb-80ca-5310a570833a" />

Here is the result of the split :

<img width="370" alt="image" src="https://github.com/user-attachments/assets/cadd84d7-5448-4731-b440-bd36edfd9a27" />

You can import it using the UI:

<img width="1440" alt="image" src="https://github.com/user-attachments/assets/2b70285b-5413-4071-9525-ce3a29b4f88e" />


Or past the folder in your production repo ( make sure you change the uuid and config_sync_directory hash ) , and update the config_sync_directory and add DRUPAL_ENV to see if it's a production env or dev. then add in your settings.php :

```php
$settings['config_sync_directory'] = 'sites/default/files/config_mXIfz1dmgBfSa28m8yZ5UXMHVvNI36a1ocM3TqL_jUqSTouexaeTh4yADe-BsVMLigTq8OyMmg/sync';

if (getenv('DRUPAL_ENV') === 'production') {
  $config['config_split.config_split.development_configuration']['status'] = FALSE;
}
```
Then run :

```bash
./vendor/bin/drush config:import
```

<img width="1440" alt="image" src="https://github.com/user-attachments/assets/8d218288-13ef-4175-844d-5817e9b20f30" />

---

### Practice Features

Let's install the featture module ,i found an error when installing feature latest verision with drupal 10 , here is the fix :

```bash
composer require drupal/config_update:^2.0@alpha
composer require 'drupal/features:^3.14'
```

i added a new feature called Blog :

<img width="1438" alt="image" src="https://github.com/user-attachments/assets/93fc9ec8-5e21-493a-8814-1ecdaa350bce" />

<img width="1440" alt="image" src="https://github.com/user-attachments/assets/8ca45714-56eb-46e3-ad36-b2cf68619b32" />

<img width="433" alt="image" src="https://github.com/user-attachments/assets/cb0fb203-bdf7-4052-a946-2b86c09cb470" />

```bash
./vendor/bin/drush en blog
```

<img width="868" alt="image" src="https://github.com/user-attachments/assets/36a51617-7cca-4459-81db-0fd3880d7af2" />

Blog content type is added successfully :

<img width="1439" alt="image" src="https://github.com/user-attachments/assets/0efa4034-c938-44ef-b777-983715b6eddd" />


- **Was their any conflicts that needed to be resolved ?**

I changed the content type name in production, then added a new field, Subtitle, to the content type in development. I copied the feature back to production (the feature detected that something had changed).

<img width="1431" alt="image" src="https://github.com/user-attachments/assets/654754f0-3571-4655-93fc-73abbbb76071" />


<img width="1351" alt="image" src="https://github.com/user-attachments/assets/9fa9d40e-c478-4d1e-b710-d25f7ed36f6f" />


✅ I didn’t encounter any issues. When I uninstalled and reinstalled the Blog module, it renamed the content type back to Blog and added the new Subtitle field.

<img width="1435" alt="image" src="https://github.com/user-attachments/assets/95e1aedb-9479-4394-9873-2e8f3b836033" />

---

### **🚀 Key Takeaways**

#### **✅ Configuration Synchronization (Config Sync)**

**Why?**

- Ensures the entire site configuration is identical across environments.
- Ideal for full-site deployments and strict version control.

**When to Use?**

- Setting up a new environment (e.g., dev → staging → production).
- Deploying global changes (roles, permissions, views, etc.).

---

#### **✅ Features Module**

**Why?**

- Allows selective deployment of specific configurations.
- Prevents unwanted global overrides.

**When to Use?**

- Deploying specific features (e.g., a new content type) without affecting the whole site.
- Managing modular updates in a multi-developer workflow.

---

## Day 4: Work with Cache

1. **Create a new route `/api/articles` :**

```yaml
custom_articles.api_articles:
  path: '/api/articles'
  defaults:
    _controller: '\Drupal\custom_articles\Controller\ArticlesController::view'
    _title: 'Article API'
  requirements:
    _permission: 'access content'
```

---

2. **This controller route should use entityQuery to list 3 articles nodes with hardcoded node ids (Yes hardcoded 10, 223, 45, of your choice). Make sure to have at least 10 nodes created and (10, 223, 45) are of them :**

Lets create the articles first using the drush commande :

```bash
./vendor/bin/drush php:eval "\Drupal\node\Entity\Node::create(['type' => 'article', 'title' => 'Article 10', 'nid' => 10])->save();"
```

<img width="972" alt="image" src="https://github.com/user-attachments/assets/c35e45c2-f50f-4ff0-85eb-2911dfe77fb5" />

<img width="1438" alt="image" src="https://github.com/user-attachments/assets/0350c8db-ba4d-41f8-ab85-14aaa91a705f" />


Now lets create our controller :
- The endpoint should responde with a JSON format: `[{nid: integer, title: string}]`.

```php
<?php

namespace Drupal\module_cache\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\node\Entity\Node;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

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

        $response = new JsonResponse($articles);
        return $response;
    }
}
```

Without caching it takes around 200ms to 300ms to get the json response :

<img width="1439" alt="image" src="https://github.com/user-attachments/assets/bda9ad81-9f9e-409c-97e3-6f9a471a4bf5" />

After implementing the caching mechanism:

```php
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

        $response->addCacheableDependency($cache_metadata);
        return $response;
    }
}

```

After caching now its 40 - 50 ms :

<img width="1440" alt="image" src="https://github.com/user-attachments/assets/1edd4dbc-8029-44f2-821c-f51582f719e0" />

and we got a cache `HIT` :

<img width="1440" alt="image" src="https://github.com/user-attachments/assets/a911d93c-abd8-42a4-8209-9369432a11cb" />

But now, after changing or updating, for example, the title of article 10, the cache doesn't invalidate and display the new value. To solve this issue, we need to add cache tags.

```php
$response = new CacheableJsonResponse($articles);
$cache_metadata = new CacheableMetadata();
$cache_metadata->setCacheMaxAge(3600);

// you cann add 
$cache_metadata->addCacheTags(['node_list']);
// or add 
foreach ($node_ids as $nid) {
    $cache_metadata->addCacheTags(['node:' . $nid]);
}

$response->addCacheableDependency($cache_metadata);
return $response;
```

Adding the node_list cache tag ensures that the cached response depends on the node list.
If any content (node) changes, Drupal invalidates all caches with the `node_list` tag.

✅ The data will refresh instantly because the cache tags invalidate the cache when the node is updated.

---

3. **Given that you configured your drupal instance using drupal disabling-and-debugging-caching enable-render-cache-debugging. How do you inspect your cache tags using the response headers ?**

<img width="1084" alt="image" src="https://github.com/user-attachments/assets/a2e317cb-7e96-4740-ab77-63123c8b5a8d" />

Add to change this in settings.php (to use this `default.services.yml` instead of `services.yml`) :
```php
$settings['container_yamls'][] = $app_root . '/' . $site_path . '/default.services.yml';
```

<img width="1433" alt="image" src="https://github.com/user-attachments/assets/59355f7f-0bd4-4b6d-9c58-f4b6392c9231" />

And add this to see the (X-Drupal-Cache-Tags and the other tags) :

```php
$settings['container_yamls'][] = $app_root . '/' . $site_path . '/../development.services.yml';
```

<img width="1087" alt="image" src="https://github.com/user-attachments/assets/922da919-2a2f-46ca-8b31-fad830587b98" />

---

### Day 5: Work with Data Migration

I set up the following directory structure for the migration module:

```bash
migration_module/
│── migration_module.info.yml
│── migration_module.migrations.yml
│── migration_module.services.yml
│── config/install/
│   ├── migrate_plus.migration.tasks.yml
│   ├── migrate_plus.migration.task_taxonomy.yml
│── migrations/
│   ├── tasks.csv
│   ├── taxonomy.json
```

I created two migrations:

1. **Task Taxonomy Migration – Imports task categories (taxonomy terms)**

```yaml
langcode: en
status: true
dependencies:
  enforced:
    module:
      - migration_module
id: task_taxonomy
class: null
field_plugin_method: null
cck_plugin_method: null
migration_tags:
  - 'Task Taxonomy Migration'
migration_group: tasks
label: 'Import Task Taxonomy Terms'
source:
  plugin: url
  data_fetcher_plugin: file
  data_parser_plugin: json
  urls: 'modules/custom/migration_module/migrations/taxonomy.json'
  item_selector: '/terms'
  fields:
    -
      name: id
      label: 'Term ID'
      selector: id
    -
      name: name
      label: 'Term Name'
      selector: name
  ids:
    id:
      type: integer
process:
  tid: id
  name: name
destination:
  plugin: entity:taxonomy_term
  default_bundle: categories
```

2. **Task Nodes Migration – Imports task nodes and assigns them to the appropriate migration group**

```yaml
langcode: en
status: true
dependencies:
  enforced:
    module:
      - migration_module
id: tasks
class: null
field_plugin_method: null
cck_plugin_method: null
migration_tags:
  - 'Task Migration'
migration_group: tasks
label: 'Task Nodes'
source:
  plugin: csv
  path: 'modules/custom/migration_module/migrations/tasks.csv'
  header_row_count: 1
  ids:
    - id
  fields:
    -
      name: id
      label: 'Task ID'  
    -
      name: name
      label: 'Task Name'
    -
      name: due_date
      label: 'Due Date'
    -
      name: status
      label: 'Task Status'
    -
      name: category
      label: 'Task Category ID'
process:
  type:
    plugin: default_value
    default_value: task
  title: name
  field_status: status
  field_due_date: due_date
  field_category:
    plugin: migration_lookup
    migration: task_taxonomy
    source: category
destination:
  plugin: 'entity:node'
migration_dependencies:
  required:
    - task_taxonomy
```

<img width="979" alt="image" src="https://github.com/user-attachments/assets/0fdccac0-cd78-4d69-acbc-6b4944ff6580" />


<img width="1440" alt="image" src="https://github.com/user-attachments/assets/86d81c4a-6fb4-4711-8074-6e19500c95ce" />

Taxonomy (category) :

<img width="1440" alt="image" src="https://github.com/user-attachments/assets/4ecc29a8-9e72-435f-b2e9-ffd39c187bf4" />

<img width="976" alt="image" src="https://github.com/user-attachments/assets/1cb2032e-d428-4036-bfaf-7d576afe70bc" />


---

1. **What's the role of migration_lookup**

`migration_lookup` is a Drupal Migrate API plugin that helps establish relationships between source and destination entities. It is commonly used when migrating related data where a referenced entity (e.g., taxonomy terms, users, nodes) has already been migrated. Instead of duplicating records, `migration_lookup` fetches the corresponding destination ID for an already-migrated entity.

For example, if you migrate articles and their authors separately, you can use `migration_lookup` to link an article to the correct author by retrieving the author's new ID.

---

2. **What would you do if you needed to import from a different data source other than CSV, say MySQL database ?**

If you need to import from a MySQL database instead of a CSV file, you should use the Migrate Source SQL plugin. Here’s what you would do:

Define your source in a migration YAML file using the source plugin as embedded_data.
Use source: plugin: sql and define the database connection.
Configure the source query to fetch the required data.

```yaml
source:
  plugin: sql
  query: SELECT * FROM park
  key: id
  target: default
  database:
    driver: mysql
    database: park
    username: root
    password: your_password
    host: localhost
    port: 3306
```

This will enable Drupal to pull data from the MySQL database.

---

3. **Rolling Back a Migration**

```sh
drush migrate:rollback migration_id
```

This will remove previously imported data for the specified migration while keeping the migration records intact.

If you want to rollback all migrations, use:

```sh
drush migrate:rollback --all
```

---

4. **How would you process a field data source before importing it? Say trim the length of a string to 10 characters?**
To process field data before importing, you can use the process pipeline in your migration YAML file. Drupal provides various process plugins to manipulate data, such as callback, substr, explode, etc.

To trim a string to 10 characters for example :

Use the substr process plugin:

```yaml
process:
  my_field:
    - plugin: substr
      source: my_source_field
      start: 0
      length: 10
```

Alternatively, use the callback plugin to apply a custom PHP function:

```yaml
process:
  my_field:
    - plugin: callback
      callable: substr
      source: my_source_field
      unpack_source: true
      parameters:
        - 0
        - 10
```

You can also chain multiple process plugins to perform more complex transformations, like trimming whitespace before truncating:

```yaml
process:
  field_example:
    -
      plugin: trim
      source: source_field
    -
      plugin: substr
      start: 0
      length: 10
```