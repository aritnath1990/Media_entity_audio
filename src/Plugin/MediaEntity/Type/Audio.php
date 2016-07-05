<?php

namespace Drupal\media_entity_audio\Plugin\MediaEntity\Type;

use Drupal\media_entity\MediaInterface;
use Drupal\media_entity\MediaTypeBase;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides media type plugin for Audio.
 *
 * @MediaType(
 *   id = "audio",
 *   label = @Translation("Audio"),
 *   description = @Translation("Provides business logic and metadata for Audio Files.")
 * )
 */
class Audio extends MediaTypeBase {

  
  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    /** @var \Drupal\media_entity\MediaBundleInterface $bundle */
    $bundle = $form_state->getFormObject()->getEntity();
    $options = [];
    $allowed_field_types = ['file', 'image'];
    foreach ($this->entityFieldManager->getFieldDefinitions('media', $bundle->id()) as $field_name => $field) {
      if (in_array($field->getType(), $allowed_field_types) && !$field->getFieldStorageDefinition()->isBaseField()) {
        $options[$field_name] = $field->getLabel();
      }
    }

    $form['source_field'] = [
      '#type' => 'select',
      '#title' => t('Field with source information'),
      '#description' => t('Field on media entity that stores Image file. You can create a bundle without selecting a value for this dropdown initially. This dropdown can be populated after adding fields to the bundle.'),
      '#default_value' => empty($this->configuration['source_field']) ? NULL : $this->configuration['source_field'],
      '#options' => $options,
    ];

    $form['gather_exif'] = [
      '#type' => 'select',
      '#title' => t('Whether to gather exif data.'),
      '#description' => t('Gather exif data using exif_read_data().'),
      '#default_value' => empty($this->configuration['gather_exif']) || !function_exists('exif_read_data') ? 0 : $this->configuration['gather_exif'],
      '#options' => [
        0 => t('No'),
        1 => t('Yes'),
      ],
      '#disabled' => (function_exists('exif_read_data')) ? FALSE : TRUE,
    ];

    return $form;
  }


  /**
   * {@inheritdoc}
   */
  public function providedFields() {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function getField(MediaInterface $media , $name) {
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function getDefaultThumbnail() {
    return $this->config->get('icon_base') . '/image.png';
  }

  /**
   * {@inheritdoc}
   */
  public function thumbnail(MediaInterface $media) {
    $source_field = $this->configuration['source_field'];

    /** @var \Drupal\file\FileInterface $file */
    if ($file = $this->entityTypeManager->getStorage('file')->load($media->{$source_field}->target_id)) {
      return $this->config->get('icon_base') . '/image.png';
    }

    return $this->getDefaultThumbnail();
  }

}
