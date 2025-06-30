<?php

namespace Drupal\prevnext\Plugin\views\field;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Url;
use Drupal\prevnext\PrevNextServiceInterface;
use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * A handler to provide display for the Previous and Next links.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("prevnext_links_field")
 */
class PrevNextLinks extends FieldPluginBase {

  /**
   * Returns the prevnext.service service.
   *
   * @var \Drupal\prevnext\PrevNextServiceInterface
   */
  protected $prevnext;

  /**
   * Returns the config.factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Returns the current_user service.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * Constructs a new PrevNextLinks instance.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin ID for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\prevnext\PrevNextServiceInterface $prevnext
   *   Interface for the main PrevNext service file.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Defines the interface for a configuration object factory.
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   Defines an account interface which represents the current user.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, PrevNextServiceInterface $prevnext, ConfigFactoryInterface $config_factory, AccountInterface $current_user) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->prevnext = $prevnext;
    $this->configFactory = $config_factory;
    $this->currentUser = $current_user;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('prevnext.service'),
      $container->get('config.factory'),
      $container->get('current_user')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function query() {}

  /**
   * {@inheritdoc}
   */
  public function render(ResultRow $values) {
    $build = [];
    $entity = $this->getEntity($values);

    if (!$entity || !is_object($entity) || !$entity instanceof EntityInterface) {
      return $build;
    }

    $config = $this->configFactory->get('prevnext.settings');
    $entity_types = $config->get('prevnext_enabled_entity_types');

    if (empty($entity_types[$entity->getEntityTypeId()])) {
      return $build;
    }

    $entity_bundles = $config->get('prevnext_enabled_entity_bundles');
    if (empty($entity_bundles[$entity->getEntityTypeId()]) || !in_array($entity->bundle(), $entity_bundles[$entity->getEntityTypeId()])) {
      return $build;
    }

    if (!$this->currentUser->hasPermission('view prevnext links') &&
      !$this->currentUser->hasPermission("view {$entity->getEntityTypeId()} prevnext links")
    ) {
      return $build;
    }

    $previous_next = $this->prevnext->getPreviousNext($entity);
    $cache = [
      'contexts' => [
        'url',
        'user.permissions',
      ],
      'tags' => [
        'config:prevnext.settings',
        "{$entity->getEntityTypeId()}_list",
        "{$entity->getEntityTypeId()}_view",
      ],
    ];

    $items = [
      [
        'key' => 'prev',
        'direction' => 'previous',
        'text' => $this->t('Previous'),
      ],
      [
        'key' => 'next',
        'direction' => 'next',
        'text' => $this->t('Next'),
      ],
    ];

    foreach ($items as $item) {
      if ($previous_next[$item['key']]) {
        $path = '';
        try {
          // Try to build canonical URL for the entity type.
          $path = Url::fromRoute("entity.{$entity->getEntityTypeId()}.canonical", [$entity->getEntityTypeId() => $previous_next[$item['key']]])->toString();
        }
        catch (\Exception $e) {
        }

        if ($path) {
          $build["prevnext_{$item['direction']}"] = [
            '#theme' => 'prevnext',
            '#direction' => $item['direction'],
            '#text' => $item['text'],
            '#id' => $item['key'],
            '#url' => $path,
            '#cache' => $cache,
          ];
        }
      }
    }

    $build['#cache']['tags'][] = "prevnext-{$entity->getEntityTypeId()}-{$entity->bundle()}";

    return $build;
  }

}
