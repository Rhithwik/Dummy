<?

services:
  my_module.layout_builder_section_storage:
    class: Drupal\my_module\Plugin\CustomSectionStorage
    arguments: ['@layout_builder.section_storage_manager']
    tags:
      - { name: layout_builder.section_storage }

-----

src/plugin/CustoSectionStorage.yml

namespace Drupal\my_module\Plugin;

use Drupal\layout_builder\SectionStorageInterface;
use Drupal\layout_builder\SectionStorage\DefaultsSectionStorage;

/**
 * Custom section storage to restrict layout options dynamically.
 */
class CustomSectionStorage extends DefaultsSectionStorage {

  /**
   * {@inheritdoc}
   */
  public function getAvailableLayouts() {
    // Fetch current sections from storage.
    $sections = $this->getSections();
    $placed_layouts = [];

    foreach ($sections as $section) {
      $placed_layouts[] = $section->getLayoutId();
    }

    // No layouts placed: allow all layouts.
    if (empty($placed_layouts)) {
      return $this->getAllLayouts();
    }

    // Restrict layouts to the one already placed.
    $allowed_layout = reset($placed_layouts);
    return array_filter($this->getAllLayouts(), function ($layout) use ($allowed_layout) {
      return $layout->getPluginId() === $allowed_layout;
    });
  }

  /**
   * Returns all available layouts.
   *
   * @return \Drupal\Core\Layout\LayoutInterface[]
   *   An array of all layout plugins.
   */
  protected function getAllLayouts() {
    return \Drupal::service('plugin.manager.core.layout')->getDefinitions();
  }
}

-------

public function getAvailableLayouts() {
  $sections = $this->getSections();

  // If no sections exist, return all layouts.
  if (empty($sections)) {
    return $this->getAllLayouts();
  }

  $placed_layouts = [];
  foreach ($sections as $section) {
    $placed_layouts[] = $section->getLayoutId();
  }

  // Restrict layouts to the one already placed.
  $allowed_layout = reset($placed_layouts);
  return array_filter($this->getAllLayouts(), function ($layout) use ($allowed_layout) {
    return $layout->getPluginId() === $allowed_layout;
  });
}

------

public function getAvailableLayouts() {
  $sections = $this->getSections();
  $route_name = \Drupal::routeMatch()->getRouteName();
  $node = \Drupal::routeMatch()->getParameter('node');

  // Node editing: restrict to current layouts.
  if ($node && $route_name === 'entity.node.edit_form') {
    $placed_layouts = [];
    foreach ($sections as $section) {
      $placed_layouts[] = $section->getLayoutId();
    }

    // Allow all layouts if none are placed.
    if (empty($placed_layouts)) {
      return $this->getAllLayouts();
    }

    $allowed_layout = reset($placed_layouts);
    return array_filter($this->getAllLayouts(), function ($layout) use ($allowed_layout) {
      return $layout->getPluginId() === $allowed_layout;
    });
  }

  // Default behavior.
  return parent::getAvailableLayouts();
}