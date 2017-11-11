<?php

/**
 * @file
 * Provide views data for views_x_posed_filters module.
 */

/**
 * Implements hook_views_data().
 */
function views_x_posed_filters_views_data() {
  $data = [];

  $data['views']['views_x_posed_filters'] = [
    'title' => t('X-posed filters'),
    'help' => t('Add an area that will add a list of exposed filters being used in order to cancel them out.'),
    'area' => [
      'id' => 'views_x_posed_filters',
    ],
  ];

  return $data;
}
