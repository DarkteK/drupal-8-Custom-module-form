<?php

namespace Drupal\custom_forms\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides the 'Benefits Request form' block.
 *
 * @Block(
 *   id = "benefits_request_form_block",
 *   admin_label = @Translation("Benefits Request form block"),
 *   category = @Translation("Legalshield Forms")
 * )
 */
class BenefitsRequestFormBlock extends BlockBase implements ContainerFactoryPluginInterface {    

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $instance = new static($configuration, $plugin_id, $plugin_definition);
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    return \Drupal::formBuilder()->getForm(
      'Drupal\custom_forms\Form\BenefitsRequestForm'
    );
  }

}
