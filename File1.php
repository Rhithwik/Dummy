<?

namespace Drupal\my_module\Plugin;

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
    $route_name = \Drupal::routeMatch()->getRouteName();
    $node = \Drupal::routeMatch()->getParameter('node');
    $placed_layouts = [];

    foreach ($sections as $section) {
      $placed_layouts[] = $section->getLayoutId();
    }

    // New detail page: allow all layouts if no sections are placed.
    if ($route_name === 'entity.node.add_form' && empty($placed_layouts)) {
      return $this->getAllLayouts();
    }

    // Node edit page: restrict to current layouts or reset if none exist.
    if ($node && $route_name === 'entity.node.edit_form') {
      if (empty($placed_layouts)) {
        return $this->getAllLayouts();
      }
      $allowed_layout = reset($placed_layouts);
      return array_filter($this->getAllLayouts(), function ($layout) use ($allowed_layout) {
        return $layout->getPluginId() === $allowed_layout;
      });
    }

    // General case: restrict to the layout already placed.
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
   * Returns all available layouts.
   *
   * @return \Drupal\Core\Layout\LayoutInterface[]
   *   An array of all layout plugins.
   */
  protected function getAllLayouts() {
    return \Drupal::service('plugin.manager.core.layout')->getDefinitions();
  }
}