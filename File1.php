<?php
module


/**
 * Implements hook_layout_builder_alter().
 */
function my_module_layout_builder_alter(array &$build, \Drupal\Core\Entity\EntityInterface $entity, string $display_id, array $context) {
  // Check if the context is a node.
  if ($entity->getEntityTypeId() === 'node') {
    // Modify the available layouts dynamically.
    $layout_definitions = \Drupal::service('plugin.manager.core.layout')->getDefinitions();

    // Get the sections already placed.
    $sections = $entity->get('layout_builder__layout')->getValue();

    $allowed_layouts = [];
    if (!empty($sections)) {
      // Restrict layouts to the one already placed.
      $placed_layout = $sections[0]['section_storage']->getLayoutId();
      foreach ($layout_definitions as $id => $definition) {
        if ($id === $placed_layout) {
          $allowed_layouts[$id] = $definition;
        }
      }
    }
    else {
      // No layouts placed: allow all layouts.
      $allowed_layouts = $layout_definitions;
    }

    // Replace the layout options in the context.
    $context['layout_builder']['available_layouts'] = $allowed_layouts;
  }
}


---------

service 

services:
  my_module.layout_manager:
    class: Drupal\my_module\Plugin\CustomLayoutManager
    arguments: ['@plugin.manager.core.layout']
    tags:
      - { name: layout_builder.plugin_manager }



----

customLayoutManager.php

namespace Drupal\my_module\Plugin;

use Drupal\Core\Layout\LayoutPluginManager;

/**
 * Custom layout plugin manager.
 */
class CustomLayoutManager extends LayoutPluginManager {

  /**
   * Restrict available layouts dynamically.
   *
   * @return array
   *   Filtered layout definitions.
   */
  public function getDefinitions() {
    $layouts = parent::getDefinitions();

    // Restrict layouts based on custom logic.
    // Example: Filter to only certain layout IDs.
    $allowed_layouts = [];
    foreach ($layouts as $id => $definition) {
      if (in_array($id, ['layout_one', 'layout_two'])) {
        $allowed_layouts[$id] = $definition;
      }
    }

    return $allowed_layouts;
  }
}

----------
