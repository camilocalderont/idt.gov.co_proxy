<?php

namespace Drupal\prevnext;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides dynamic permissions for prevnext module.
 */
class PrevNextPermissions implements ContainerInjectionInterface {

  use StringTranslationTrait;

  /**
   * Returns the entity_type.manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a new PrevNextPermissions instance.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Provides an interface for entity type managers.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('entity_type.manager'));
  }

  /**
   * Returns an array of prevnext permissions.
   *
   * @return array
   *   An array of permissions for all plugins.
   */
  public function permissions() {
    $permissions = [];

    foreach ($this->entityTypeManager->getDefinitions() as $definition) {
      if (in_array('Drupal\Core\Entity\FieldableEntityInterface', class_implements($definition->getOriginalClass())) &&
        $definition->getLinkTemplate('canonical')
      ) {
        $permissions["view {$definition->id()} prevnext links"] = [
          'title' => $this->t('View @entity_type PrevNext links', ['@entity_type' => $definition->getLabel()->render()]),
        ];
      }
    }

    return $permissions;
  }

}
