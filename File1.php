<?php

services:
  my_module.custom_section_storage_manager:
    class: Drupal\my_module\Plugin\CustomSectionStorageManager
    decorates: layout_builder.section_storage_manager
    arguments: ['@layout_builder.section_storage_manager', '@plugin.manager.core.layout']
    decoration_priority: 10




namespace Drupal\my_module\Plugin;

use Drupal\layout_builder\SectionStorage\SectionStorageManager;
use Drupal\layout_builder\SectionStorage\SectionStorageInterface;

/**
 * Custom Section Storage Manager for Layout Builder.
 */
class CustomSectionStorageManager extends SectionStorageManager {

  /**
   * The layout plugin manager.
   *
   * @var \Drupal\Core\Layout\LayoutPluginManager
   */
  protected $layoutPluginManager;

  /**
   * Constructs a CustomSectionStorageManager object.
   *
   * @param \Drupal\layout_builder\SectionStorage\SectionStorageManager $section_storage_manager
   *   The original section storage manager.
   * @param \Drupal\Core\Layout\LayoutPluginManager $layout_plugin_manager
   *   The layout plugin manager.
   */
  public function __construct(SectionStorageManager $section_storage_manager, $layout_plugin_manager) {
    parent::__construct($section_storage_manager->getEntityTypeManager());
    $this->layoutPluginManager = $layout_plugin_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function getSectionStorage($storage_id) {
    $section_storage = parent::getSectionStorage($storage_id);

    // Wrap the section storage with custom logic.
    return new CustomSectionStorage($section_storage, $this->layoutPluginManager);
  }
}






-----



namespace Drupal\my_module\Plugin;

use Drupal\layout_builder\SectionStorage\SectionStorageInterface;

/**
 * Custom Section Storage for Layout Builder.
 */
class CustomSectionStorage implements SectionStorageInterface {

  /**
   * The original section storage.
   *
   * @var \Drupal\layout_builder\SectionStorage\SectionStorageInterface
   */
  protected $originalSectionStorage;

  /**
   * The layout plugin manager.
   *
   * @var \Drupal\Core\Layout\LayoutPluginManager
   */
  protected $layoutPluginManager;

  /**
   * Constructs a CustomSectionStorage object.
   *
   * @param \Drupal\layout_builder\SectionStorage\SectionStorageInterface $section_storage
   *   The original section storage.
   * @param \Drupal\Core\Layout\LayoutPluginManager $layout_plugin_manager
   *   The layout plugin manager.
   */
  public function __construct(SectionStorageInterface $section_storage, $layoutPluginManager) {
    $this->originalSectionStorage = $section_storage;
    $this->layoutPluginManager = $layoutPluginManager;
  }

  /**
   * {@inheritdoc}
   */
  public function getAvailableLayouts() {
    // Get current sections and their layouts.
    $sections = $this->originalSectionStorage->getSections();
    $placed_layouts = [];

    foreach ($sections as $section) {
      $placed_layouts[] = $section->getLayoutId();
    }

    // If no layouts are placed, return all layouts.
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
   * Get all available layouts.
   *
   * @return array
   *   All available layout definitions.
   */
  protected function getAllLayouts() {
    return $this->layoutPluginManager->getDefinitions();
  }

  /**
   * Delegate other methods to the original storage.
   */
  public function getSections() {
    return $this->originalSectionStorage->getSections();
  }

  public function getStorageId() {
    return $this->originalSectionStorage->getStorageId();
  }

  public function getContext() {
    return $this->originalSectionStorage->getContext();
  }

  public function getEntity() {
    return $this->originalSectionStorage->getEntity();
  }

  // Implement other methods as needed...
}