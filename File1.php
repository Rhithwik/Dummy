<?


function demo_new{


echo "Hello world";
}

/*Testing the sfuffs*/

?>
name: 'Custom Layout Restriction'
type: module
core_version_requirement: ^10
description: 'Restricts layout selection after the first section in Layout Builder.'
dependencies:
  - drupal:layout_builder



-------

src/EventSubscriber/LayoutRestrictionSubscriber.php


<?php

namespace Drupal\custom_layout_restriction\EventSubscriber;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\layout_builder\Event\SectionEvents;
use Drupal\layout_builder\Event\SectionFormAlterEvent;

/**
 * Event subscriber for layout restriction.
 */
class LayoutRestrictionSubscriber implements EventSubscriberInterface {
  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a new LayoutRestrictionSubscriber object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager service.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [
      SectionEvents::SECTION_FORM_ALTER => 'onSectionFormAlter',
    ];
  }

  /**
   * Alters the section form to restrict layout options.
   *
   * @param \Drupal\layout_builder\Event\SectionFormAlterEvent $event
   *   The section form alter event.
   */
  public function onSectionFormAlter(SectionFormAlterEvent $event) {
    $form = &$event->getForm();
    $node = $event->getContext()->getContextValue('node');

    // Proceed only if this is a node entity.
    if ($node && $node->hasField('layout_sections')) {
      $sections = $node->get('layout_sections')->getValue();

      // If there are no sections, allow all layouts (Scenario 1 and 4).
      if (empty($sections)) {
        return;
      }

      // Restrict layouts to the first section's layout ID (Scenario 2 and 3).
      $first_layout_id = $sections[0]['layout_id'];
      $form['layout_id']['#options'] = [
        $first_layout_id => $form['layout_id']['#options'][$first_layout_id],
      ];
    }
  }
}


service.yml

services:
  custom_layout_restriction.layout_restriction_subscriber:
    class: Drupal\custom_layout_restriction\EventSubscriber\LayoutRestrictionSubscriber
    arguments: ['@entity_type.manager']
    tags:
      - { name: event_subscriber }


---

<?php

use Drupal\Core\Form\FormStateInterface;

/**
 * Implements hook_form_alter().
 */
function custom_layout_restriction_form_alter(&$form, FormStateInterface $form_state, $form_id) {
  // Target the Layout Builder's "Add Section" form.
  if ($form_id === 'layout_builder_add_section_form') {
    // Get the node from the route.
    $route_match = \Drupal::routeMatch();
    $node = $route_match->getParameter('node');

    if ($node && $node->hasField('layout_sections')) {
      $sections = $node->get('layout_sections')->getValue();

      // Scenario 1 and 4: No sections exist or all sections removed.
      if (empty($sections)) {
        return; // Allow all layouts.
      }

      // Scenario 2 and 3: Restrict to the layout of the first section.
      $first_layout_id = $sections[0]['layout_id'];
      if (isset($form['layout_id']['#options'][$first_layout_id])) {
        $form['layout_id']['#options'] = [
          $first_layout_id => $form['layout_id']['#options'][$first_layout_id],
        ];
      }
    }
  }
}

‐-------

public function listAvailableLayouts(Request $request, $node_id) {
  $node = Node::load($node_id);

  if ($node && $node->hasField('layout_sections')) {
    $sections = $node->get('layout_sections')->getValue();
    $layouts = array_column($sections, 'layout_id');

    if (empty($layouts)) {
      // Allow all layouts since no sections exist.
      return $this->getAllLayouts();
    }

    // Restrict to the first layout.
    $allowed_layout = $layouts[0];
    return $this->getLayoutsById([$allowed_layout]);
  }

  // Default behavior if no sections or node is invalid.
  return $this->getAllLayouts();
}