<?php

/**
 * @file
 * Contains \Drupal\entity_embed\EntityEmbedDisplay\FileFieldFormatter.
 */

namespace Drupal\entity_embed\EntityEmbedDisplay;

use Drupal\Core\Field\FieldDefinition;
use Drupal\Core\Session\AccountInterface;

/**
 * Embed entity displays for file field formatters.
 *
 * @EntityEmbedDisplay(
 *   id = "file",
 *   label = @Translation("File"),
 *   entity_types = {"file"},
 *   derivative = "Drupal\entity_embed\Plugin\Derivative\FieldFormatterDeriver",
 *   field_type = "file",
 *   provider = "file"
 * )
 */
class FileFieldFormatter extends EntityReferenceFieldFormatter {

  /**
   * {@inheritdoc}
   */
  public function getFieldDefinition() {
    $field = FieldDefinition::create('file');
    return $field;
  }

  /**
   * {@inheritdoc}
   */
  public function getFieldValue(FieldDefinition $definition) {
    $value = parent::getFieldValue($definition);
    $value += array_intersect_key($this->getAttributeValues(), array('description' => ''));
    return $value;
  }

  /**
   * {@inheritdoc}
   */
  public function access(AccountInterface $account = NULL) {
    if (!$this->isValidEntityType()) {
      return FALSE;
    }

    // Due to issues with access checking with file entities in core, we cannot
    // actually use Entity::access() which would have been called by
    // parent::access().
    // @see https://drupal.org/node/2128791
    // @see https://drupal.org/node/2148353
    // @see https://drupal.org/node/2078473
    switch (file_uri_scheme($this->getContextValue('entity')->getFileUri())) {
      case 'public':
        return TRUE;

      case 'private':
      case 'temporary':
        $headers = $this->moduleHandler()->invokeAll('file_download', array($uri));
        foreach ($headers as $result) {
          if ($result == -1) {
            return FALSE;
          }
        }

        if (count($headers)) {
          return TRUE;
        }
        break;
    }

    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    $defaults = parent::defaultConfiguration();
    // Add support to store file description.
    $defaults['description'] = '';
    return $defaults;
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, array &$form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);

    // Description is stored in the configuration since it doesn't map to an
    // actual HTML attribute.
    // @todo Ensure these fields work properly and map to the attributes
    $form['description'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Description'),
      '#default_value' => $this->getConfigurationValue('description'),
      '#description' => $this->t('The description may be used as the label of the link to the file.'),
    );

    return $form;
  }

}
