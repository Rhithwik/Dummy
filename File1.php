<?


use Drupal\layout_builder\SectionStorageInterface;
use Drupal\Core\TempStore\PrivateTempStoreFactory;
use Symfony\Component\DependencyInjection\ContainerInterface;

class CustomSectionController {

  protected $tempStore;

  /**
   * Constructor.
   *
   * @param \Drupal\Core\TempStore\PrivateTempStoreFactory $temp_store
   *   The tempstore.private service.
   */
  public function __construct(PrivateTempStoreFactory $temp_store) {
    $this->tempStore = $temp_store->get('layout_builder');
  }

  /**
   * Create method for dependency injection.
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('tempstore.private')
    );
  }

  /**
   * Build method.
   *
   * @param \Drupal\layout_builder\SectionStorageInterface $section_storage
   *   The section storage interface.
   * @param int $delta
   *   The index of the section.
   *
   * @return array
   *   A renderable array.
   */
  public function build(SectionStorageInterface $section_storage, int $delta) {
    $layouts = [];

    // Check if the entity is saved or unsaved.
    if ($section_storage->isOverridable()) {
      // Unsaved node: Retrieve layout from tempstore.
      $entity = $section_storage->getContextValue('entity');
      $bundle = $entity->bundle();
      $tempstore_key = "node:{$bundle}:{$entity->uuid()}";

      $temp_data = $this->tempStore->get($tempstore_key);

      if ($temp_data && !empty($temp_data['sections'])) {
        foreach ($temp_data['sections'] as $section) {
          $layouts[] = $section['layout_id'] ?? 'unknown';
        }
      }
    }
    else {
      // Saved node: Get layout data directly from the section storage.
      foreach ($section_storage->getSections() as $section_index => $section) {
        $layouts[] = $section->getLayoutId();
      }
    }

    // Access the layout of the current section using $delta.
    $current_layout = $layouts[$delta] ?? 'none';

    // Return a renderable array or use the layout data.
    return [
      '#theme' => 'item_list',
      '#title' => $this->t('Layouts in Section Storage'),
      '#items' => $layouts,
      '#footer' => $this->t('Current Section Layout: @layout', ['@layout' => $current_layout]),
    ];
  }
}

-----------

use Drupal\Core\TempStore\PrivateTempStoreFactory;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Custom service to retrieve layout information.
 */
class LayoutHelper {

  /**
   * The tempstore.private service.
   *
   * @var \Drupal\Core\TempStore\PrivateTempStoreFactory
   */
  protected $tempStore;

  /**
   * Constructs the layout helper.
   *
   * @param \Drupal\Core\TempStore\PrivateTempStoreFactory $temp_store
   *   The tempstore.private service.
   */
  public function __construct(PrivateTempStoreFactory $temp_store) {
    $this->tempStore = $temp_store->get('layout_builder');
  }

  /**
   * Get the layout of the first section for an unsaved node.
   *
   * @param string $bundle
   *   The content type of the node.
   *
   * @return string|null
   *   The layout ID of the first section, or NULL if not found.
   */
  public function getFirstSectionLayout($bundle) {
    // Retrieve all tempstore keys.
    $temp_keys = $this->tempStore->getAll();

    // Find the tempstore key for the current bundle.
    foreach ($temp_keys as $key => $data) {
      if (strpos($key, "node:$bundle:") === 0) {
        // Check if sections exist.
        if (!empty($data['sections'])) {
          // Return the layout ID of the first section.
          $first_section = reset($data['sections']);
          return $first_section['layout_id'] ?? NULL;
        }
      }
    }

    return NULL;
  }
}