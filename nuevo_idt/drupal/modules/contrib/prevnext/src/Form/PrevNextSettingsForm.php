<?php

namespace Drupal\prevnext\Form;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Form for the module settings.
 *
 * @package Drupal\prevnext\Form
 */
class PrevNextSettingsForm extends ConfigFormBase {

  /**
   * Returns the entity_type.manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Returns the entity_type.bundle.info service.
   *
   * @var \Drupal\Core\Entity\EntityTypeBundleInfoInterface
   */
  protected $entityTypeBundleInfo;

  /**
   * Constructs a PrevNextSettingsForm form.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Provides an interface for entity type managers.
   * @param \Drupal\Core\Entity\EntityTypeBundleInfoInterface $entity_type_bundle_info
   *   Provides an interface for an entity type bundle info.
   */
  public function __construct(ConfigFactoryInterface $config_factory, EntityTypeManagerInterface $entity_type_manager, EntityTypeBundleInfoInterface $entity_type_bundle_info) {
    parent::__construct($config_factory);

    $this->entityTypeManager = $entity_type_manager;
    $this->entityTypeBundleInfo = $entity_type_bundle_info;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('entity_type.manager'),
      $container->get('entity_type.bundle.info')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'prevnext_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['prevnext.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('prevnext.settings');

    $options = $bundles = [];
    foreach ($this->entityTypeManager->getDefinitions() as $definition) {
      if (in_array('Drupal\Core\Entity\FieldableEntityInterface', class_implements($definition->getOriginalClass())) &&
        $definition->getLinkTemplate('canonical')
      ) {
        $options[$definition->id()] = $definition->getLabel()->render();

        $bundles[$definition->id()] = [];
        foreach ($this->entityTypeBundleInfo->getBundleInfo($definition->id()) as $bundle => $info) {
          $bundles[$definition->id()][$bundle] = $info['label'];
        }
      }
    }

    $form['prevnext_enabled_entity_types'] = [
      '#title' => $this->t('Enabled Entity Types'),
      '#description' => $this->t('Check entity types enabled for PrevNext'),
      '#type' => 'checkboxes',
      '#options' => $options,
      '#default_value' => !empty($config->get('prevnext_enabled_entity_types')) ? $config->get('prevnext_enabled_entity_types') : [],
    ];

    foreach ($bundles as $key => $bundle) {
      $form["prevnext_enabled_entity_{$key}_type"] = [
        '#title' => $this->t('Enabled @entity bundles', ['@entity' => $options[$key]]),
        '#type' => 'checkboxes',
        '#options' => $bundle,
        '#default_value' => !empty($config->get('prevnext_enabled_entity_bundles')[$key]) ? $config->get('prevnext_enabled_entity_bundles')[$key] : [],
        '#states' => [
          'visible' => [
            ":input[name='prevnext_enabled_entity_types[{$key}]']" => ['checked' => TRUE],
          ],
        ],
      ];
    }

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Save the config values.
    $entity_types = array_filter($form_state->getValue('prevnext_enabled_entity_types'));

    $entity_bundles = [];
    foreach ($entity_types as $entity_type) {
      $entity_bundles[$entity_type] = array_filter($form_state->getValue("prevnext_enabled_entity_{$entity_type}_type"));
    }

    $this->config('prevnext.settings')
      ->set('prevnext_enabled_entity_types', $entity_types)
      ->set('prevnext_enabled_entity_bundles', $entity_bundles)
      ->save();

    Cache::invalidateTags(['entity_field_info']);
    parent::submitForm($form, $form_state);
  }

}
