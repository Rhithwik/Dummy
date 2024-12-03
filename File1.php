<?php

services:
  my_module.custom_section_storage:
    class: Drupal\my_module\Plugin\CustomSectionStorage
    decorates: layout_builder.default_section_storage
    decoration_priority: 10
    arguments: ['@layout_builder.default_section_storage']




namespace Drupal\my_module\Plugin;

use Drupal\layout_builder\SectionStorage\DefaultsSectionStorage;

/**
 * Custom Section Storage for Layout Builder.
 */
class CustomSectionStorage extends DefaultsSectionStorage {

  /**
   * {@inheritdoc}
   */
  public function getAvailableLayouts() {
    // Fetch the current sections and determine allowed layouts.
    $sections = $this->getSections();
    $route_name = \Drupal::routeMatch()->getRouteName();
    $node = \Drupal::routeMatch()->getParameter('node');
    $placed_layouts = [];

    foreach ($sections as $section) {
      $placed_layouts[] = $section->getLayoutId();
    }

    // Node editing scenario.
    if ($node && $route_name === 'entity.node.edit_form') {
      if (empty($placed_layouts)) {
        // No sections placed, return all layouts.
        return $this->getAllLayouts();
      }

      // Restrict to the layout currently in use.
      $allowed_layout = reset($placed_layouts);
      return array_filter($this->getAllLayouts(), function ($layout) use ($allowed_layout) {
        return $layout->getPluginId() === $allowed_layout;
      });
    }

    // Default behavior: restrict to the layout already placed.
    if (!empty($placed_layouts)) {
      $allowed_layout = reset($placed_layouts);
      return array_filter($this->getAllLayouts(), function ($layout) use ($allowed_layout) {
        return $layout->getPluginId() === $allowed_layout;
      });
    }

    // Default to all layouts.
    return $this->getAllLayouts();
  }

  /**
   * Get all layouts.
   *
   * @return array
   *   All available layout definitions.
   */
  protected function getAllLayouts() {
    $layout_manager = \Drupal::service('plugin.manager.core.layout');
    return $layout_manager->getDefinitions();
  }
}