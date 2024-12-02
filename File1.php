<?


function demo_new{


echo "Hello world";
}

/*Testing the sfuffs*/

?>


namespace Drupal\custom_module\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\TempStore\PrivateTempStoreFactory;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Provides restricted layouts based on the first section.
 */
class LayoutRestrictionController extends ControllerBase {

  /**
   * The tempstore.private service.
   *
   * @var \Drupal\Core\TempStore\PrivateTempStoreFactory
   */
  protected $tempStore;

  /**
   * Constructs the LayoutRestrictionController.
   *
   * @param \Drupal\Core\TempStore\PrivateTempStoreFactory $temp_store
   *   The tempstore.private service.
   */
  public function __construct(PrivateTempStoreFactory $temp_store) {
    $this->tempStore = $temp_store->get('layout_builder');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('tempstore.private')
    );
  }

  /**
   * Fetch restricted layouts based on the first section.
   */
  public function getRestrictedLayouts() {
    $request = \Drupal::request();
    $bundle = $request->query->get('bundle');

    if ($bundle) {
      // Get all tempstore keys.
      $temp_keys = $this->tempStore->getAll();

      // Find the relevant tempstore key.
      foreach ($temp_keys as $key => $data) {
        if (strpos($key, "node:$bundle:") === 0) {
          if (!empty($data['sections'])) {
            // Get the layout ID of the first section.
            $first_section = reset($data['sections']);
            $layout_id = $first_section['layout_id'] ?? NULL;

            // Return the restricted layouts.
            if ($layout_id) {
              return new JsonResponse(['restricted_layouts' => $layout_id]);
            }
          }
        }
      }
    }

    return new JsonResponse(['restricted_layouts' => NULL]);
  }
}