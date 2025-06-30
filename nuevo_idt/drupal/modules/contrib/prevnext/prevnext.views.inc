<?php

/**
 * @file
 * Contains prevnext.views.inc.
 */

/**
 * Implements hook_views_data_alter().
 */
function prevnext_views_data_alter(array &$data) {
  $data['views']['prevnext_links_field'] = [
    'title' => t('Previous/Next links'),
    'field' => [
      'id' => 'prevnext_links_field',
    ],
  ];
}
