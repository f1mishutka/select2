<?php

namespace Drupal\select2;

use Drupal\Component\Utility\Html;
use Drupal\Core\Entity\EntityReferenceSelection\SelectionPluginManagerInterface;

/**
 * Matcher class to get autocompletion results for entity reference.
 */
class EntityAutocompleteMatcher {

  /**
   * The entity reference selection handler plugin manager.
   *
   * @var \Drupal\Core\Entity\EntityReferenceSelection\SelectionPluginManagerInterface
   */
  protected $selectionManager;

  /**
   * Constructs a EntityAutocompleteMatcher object.
   *
   * @param \Drupal\Core\Entity\EntityReferenceSelection\SelectionPluginManagerInterface $selection_manager
   *   The entity reference selection handler plugin manager.
   */
  public function __construct(SelectionPluginManagerInterface $selection_manager) {
    $this->selectionManager = $selection_manager;
  }

  /**
   * Gets matched labels based on a given search string.
   *
   * @param string $target_type
   *   The ID of the target entity type.
   * @param string $selection_handler
   *   The plugin ID of the entity reference selection handler.
   * @param array $selection_settings
   *   An array of settings that will be passed to the selection handler.
   * @param string $string
   *   (optional) The label of the entity to query by.
   * @param int $page
   *   (optional) An offset for the results.
   *
   * @return array
   *   An array of matched entity labels, in the format required by the AJAX
   *   autocomplete API (e.g. array('value' => $value, 'label' => $label)).
   *
   * @throws \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException
   *   Thrown when the current user doesn't have access to the specified entity.
   *
   * @see \Drupal\system\Controller\EntityAutocompleteController
   */
  public function getMatches($target_type, $selection_handler, array $selection_settings, $string = '', $page = 0) {
    $matches = [];

    $options = $selection_settings + [
      'target_type' => $target_type,
      'handler' => $selection_handler,
    ];
    $handler = $this->selectionManager->getInstance($options);

    if (isset($string)) {
      // Get an array of matching entities.
      $match_operator = !empty($selection_settings['match_operator']) ? $selection_settings['match_operator'] : 'CONTAINS';
      $match_size = !empty($selection_settings['match_size']) ? $selection_settings['match_size'] : 10;
      $entity_labels = $handler->getReferenceableEntities($string, $match_operator, $match_size, $page * $match_size);

      // Loop through the entities and convert them into autocomplete output.
      foreach ($entity_labels as $values) {
        foreach ($values as $entity_id => $label) {
          $matches[] = ['id' => $entity_id, 'text' => Html::decodeEntities($label)];
        }
      }
    }

    return $matches;
  }

}
