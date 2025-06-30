<?php

namespace Drupal\prevnext;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Main service file.
 *
 * @package Drupal\prevnext
 */
class PrevNextService implements PrevNextServiceInterface {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Previous / Next ids.
   *
   * @var array
   */
  public $prevnext;

  /**
   * Constructs a new PrevNextService instance.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager instance.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function getPreviousNext(EntityInterface $entity) {
    $entities = $this->getEntitiesOfType($entity);
    $id = $entity->id();

    $key = array_search($id, $entities);
    $this->prevnext['prev'] = ($key == 0) ? '' : $entities[$key - 1];
    $this->prevnext['next'] = ($key == count($entities) - 1) ? '' : $entities[$key + 1];

    return $this->prevnext;
  }

  /**
   * Retrieves all entities of the same type and language of given.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity.
   *
   * @return array
   *   An array of entities filtered by type, status and language.
   */
  protected function getEntitiesOfType(EntityInterface $entity) {
    $definition = $this->entityTypeManager->getDefinition($entity->getEntityTypeId());
    $query = $this->entityTypeManager->getStorage($entity->getEntityTypeId())->getQuery();

    $query->condition('status', 1);
    $query->accessCheck(TRUE);
    $query->addTag("prev_next_{$entity->getEntityTypeId()}_type");

    $bundle = $entity->bundle();
    if ($type = $definition->getKey('bundle')) {
      $query->condition($type, $bundle);
      $query->addMetaData($type, $bundle);
    }

    $langcode = $entity->language()->getId();
    if ($lang = $definition->getKey('langcode')) {
      $query->condition($lang, $langcode);
      $query->addMetaData($lang, $langcode);
    }

    return array_values($query->execute());
  }

}
