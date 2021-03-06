<?php

/**
 * @file
 * Contains \Drupal\entity_embed\Plugin\Derivative\FieldFormatterDeriver.
 */

namespace Drupal\entity_embed\Plugin\Derivative;

use Drupal\Component\Plugin\Derivative\DerivativeBase;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Plugin\Discovery\ContainerDerivativeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Field\FormatterPluginManager;

/**
 * Provides entity embed display plugin definitions for field formatters.
 *
 * @see \Drupal\entity_embed\FieldFormatterEntityEmbedDisplayBase
 */
class FieldFormatterDeriver extends DerivativeBase implements ContainerDerivativeInterface {

  /**
   * The manager for formatter plugins.
   *
   * @var \Drupal\Core\Field\FormatterPluginManager.
   */
  protected $formatterManager;

  /**
   * Constructs new FieldFormatterEntityEmbedDisplayBase.
   *
   * @param \Drupal\Core\Field\FormatterPluginManager $formatterManager
   *   The field formatter plugin manager.
   */
  public function __construct(FormatterPluginManager $formatterManager) {
    $this->formatterManager = $formatterManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, $base_plugin_id) {
    return new static(
      $container->get('plugin.manager.field.formatter')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($base_plugin_definition) {
    // The field type must be defined in the annotation of the display plugin.
    if (!isset($base_plugin_definition['field_type'])) {
      throw new \LogicException("Undefined field_type definition in plugin {$base_plugin_defintion['id']}.");
    }
    foreach ($this->formatterManager->getOptions($base_plugin_definition['field_type']) as $formatter => $label) {
      $this->derivatives[$formatter] = $base_plugin_definition;
      $this->derivatives[$formatter]['label'] = $label;
    }
    return $this->derivatives;
  }

}
