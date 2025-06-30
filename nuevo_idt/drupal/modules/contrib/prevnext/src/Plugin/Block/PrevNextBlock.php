<?php

namespace Drupal\prevnext\Plugin\Block;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Routing\CurrentRouteMatch;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Url;
use Drupal\prevnext\PrevNextServiceInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Block with the Previous and Next links.
 *
 * @Block(
 *   id = "prevnext_block",
 *   admin_label = @Translation("PrevNext links"),
 *   category = @Translation("Other"),
 * )
 */
class PrevNextBlock extends BlockBase implements ContainerFactoryPluginInterface {

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
   * Returns the current_route_match service.
   *
   * @var \Drupal\Core\Routing\CurrentRouteMatch
   */
  protected $currentRoute;

  /**
   * Constructs a PrevNextBlock block.
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
   * @param \Drupal\Core\Routing\CurrentRouteMatch $current_route
   *   Default object for current_route_match service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, PrevNextServiceInterface $prevnext, ConfigFactoryInterface $config_factory, CurrentRouteMatch $current_route) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->prevnext = $prevnext;
    $this->configFactory = $config_factory;
    $this->currentRoute = $current_route;
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
      $container->get('current_route_match')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [];

    $config = $this->configFactory->get('prevnext.settings');
    $entity_types = $config->get('prevnext_enabled_entity_types');

    $entity = NULL;
    foreach ($this->currentRoute->getParameters() as $parameter => $data) {
      if (in_array($parameter, $entity_types) && $data instanceof EntityInterface) {
        $entity_bundles = $config->get('prevnext_enabled_entity_bundles');

        if (!empty($entity_bundles[$parameter]) && in_array($data->bundle(), $entity_bundles[$parameter])) {
          $entity = $data;
        }
      }
    }

    if (!$entity || !empty($entity->in_preview)) {
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

  /**
   * {@inheritdoc}
   */
  public function blockAccess(AccountInterface $account) {
    $access = $account->hasPermission('view prevnext links');

    if (!$access) {
      $config = $this->configFactory->get('prevnext.settings');
      $entity_types = $config->get('prevnext_enabled_entity_types');

      foreach ($this->currentRoute->getParameters() as $parameter => $data) {
        if (in_array($parameter, $entity_types) &&
          $data instanceof EntityInterface &&
          $account->hasPermission("view {$parameter} prevnext links")
        ) {
          $access = TRUE;
          break;
        }
      }
    }

    return AccessResult::allowedIf($access);
  }

}
