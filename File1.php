<?php

services:
  my_module.section_storage_manager:
    class: Drupal\my_module\Plugin\CustomSectionStorageManager
    arguments: ['@entity_type.manager', '@config.factory']
    tags:
      - { name: service_subscriber }
    decorates: layout_builder.section_storage_manager
    decoration_priority: 10

----
customSectionManager.php

namespace Drupal\my_module\Plugin;

use Drupal\layout_builder\SectionStorage\SectionStorageManager;

/**
 * Custom Section Storage Manager to control layout availability.
 */
class CustomSectionStorageManager extends SectionStorageManager {

  /**
   * {@inheritdoc}
   */
  public function getSectionStorage($storage_id) {
    $section_storage = parent::getSectionStorage($storage_id);

    // Wrap the section storage with custom logic.
    return new CustomSectionStorage($section_storage);
  }


----


namespace Drupal\my_module\Plugin;

use Drupal\layout_builder\SectionStorage\SectionStorageInterface;

/**
 * Custom Section Storage to dynamically restrict layouts.
 */
class CustomSectionStorage implements SectionStorageInterface {

  /**
   * The original section storage.
   *
   * @var \Drupal\layout_builder\SectionStorage\SectionStorageInterface
   */
  protected $originalSectionStorage;

  /**
   * Constructs a CustomSectionStorage object.
   *
   * @param \Drupal\layout_builder\SectionStorage\SectionStorageInterface $section_storage
   *   The original section storage.
   */
  public function __construct(SectionStorageInterface $section_storage) {
    $this->originalSectionStorage = $section_storage;
  }

  /**
   * {@inheritdoc}
   */
  public function getSections() {
    return $this->originalSectionStorage->getSections();
  }

  /**
   * {@inheritdoc}
   */
  public function getAvailableLayouts() {
    // Get placed layouts from current sections.
    $sections = $this->getSections();
    $placed_layouts = [];

    foreach ($sections as $section) {
      $placed_layouts[] = $section->getLayoutId();
    }

    // New detail page or no sections placed: allow all layouts.
    if (empty($placed_layouts)) {
      return $this->getAllLayouts();
    }

    // Restrict to the layout already placed.
    $allowed_layout = reset($placed_layouts);
    return array_filter($this->getAllLayouts(), function ($layout) use ($allowed_layout) {
      return $layout->getPluginId() === $allowed_layout;
    });
  }

  /**
   * {@inheritdoc}
   */
  public function getAllLayouts() {
    // Fetch all layouts from the plugin manager.
    return \Drupal::service('plugin.manager.core.layout')->getDefinitions();
  }

  /**
   * Other methods required by the SectionStorageInterface can delegate
   * to the original storage if no custom logic is required.
   */
  public function getStorageId() {
    return $this->originalSectionStorage->getStorageId();
  }

  public function getContext() {
    return $this->originalSectionStorage->getContext();
  }

  public function getEntity() {
    return $this->originalSectionStorage->getEntity();
  }

  // Add other required methods...
}