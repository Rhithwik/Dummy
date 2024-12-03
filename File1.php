<?


function demo_new{


echo "Hello world";
}

/*Testing the sfuffs*/

?>



use Drupal\Core\TempStore\PrivateTempStoreFactory;
use Drupal\Core\Entity\EntityTypeManagerInterface;

class CustomSectionController extends ControllerBase {

  protected $tempStore;
  protected $entityTypeManager;

  public function __construct(PrivateTempStoreFactory $temp_store, EntityTypeManagerInterface $entity_type_manager) {
    $this->tempStore = $temp_store->get('layout_builder');
    $this->entityTypeManager = $entity_type_manager;
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('tempstore.private'),
      $container->get('entity_type.manager')
    );
  }

  public function build($entity, $view_mode, array $contexts) {
    $layout_data = [];

    // Check if the node is saved or unsaved.
    if ($entity->isNew()) {
      // Unsaved node: Retrieve layout from tempstore.
      $bundle = $entity->bundle();
      $tempstore_key = "node:{$bundle}:{$entity->uuid()}";

      $temp_data = $this->tempStore->get($tempstore_key);

      if ($temp_data && !empty($temp_data['sections'])) {
        foreach ($temp_data['sections'] as $section) {
          $layout_data[] = $section['layout_id'];
        }
      }
    }
    else {
      // Saved node: Retrieve layout from the database.
      $sections = $entity->get('layout_builder__sections')->getValue();
      foreach ($sections as $section) {
        $section_data = $section['section'];
        if (!empty($section_data['layout_id'])) {
          $layout_data[] = $section_data['layout_id'];
        }
      }
    }

    // Now you have the layout data, proceed with your custom logic.
    return [
      '#markup' => $this->t('Layouts: @layouts', ['@layouts' => implode(', ', $layout_data)]),
    ];
  }
}