<?php

/**
 * @file
 * Contains \Drupal\block\Plugin\views\area\Block.
 */

namespace Drupal\views_x_posed_filters\Plugin\views\area;

use Drupal\Core\Form\FormStateInterface;
use Drupal\node\Entity\Node;
use Drupal\taxonomy\Entity\Term;
use Drupal\views\Plugin\views\area\AreaPluginBase;

/**
 * Provides an area handler which renders bubbles to reset filters.
 *
 * @ingroup views_area_handlers
 *
 * @ViewsArea("views_x_posed_filters")
 */
class ViewsXPosedFilters extends AreaPluginBase {

  private $exposedInput;
  private $exposedNames;
  private $exposedTypes;

  /**
   * {@inheritdoc}
   */
  protected function defineOptions() {
    $options = parent::defineOptions();
    $options['label'] = [
      'contains' => [
        'value' => ['default' => ''],
      ],
    ];
    $options['label_element'] = [
      'contains' => [
        'value' => ['default' => 'h3'],
      ],
    ];
    $options['label_classes'] = [
      'contains' => [
        'value' => ['default' => ''],
      ],
    ];
    $options['filters'] = [
      'contains' => [
        'value' => ['default' => ''],
      ],
    ];
    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    parent::buildOptionsForm($form, $form_state);

    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#default_value' => $this->options['label'],
      '#description' => $this->t('Optional text to show before the list of exposed filters in use.'),
    ];

    $form['label_element'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label HTML element'),
      '#default_value' => $this->options['label_element'],
      '#description' => $this->t('HTML element for label.'),
    ];

    $form['label_classes'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label classes'),
      '#default_value' => $this->options['label_classes'],
      '#description' => $this->t('Class(es) to add to label HTML element. "visually-hidden" is a popular one for accessibility if you do not want the label to show.'),
    ];

    $form['filters'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Exposed filters'),
      '#default_value' => $this->options['filters'],
      '#description' => $this->t('Filters to activate on (use query string label), separated by spaces. Leave blank to activate on all exposed filters.'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function render($empty = FALSE) {
    $this->buildExposedData();

    if (!empty($this->exposedInput)) {
      $filters = [];
      $label_element = $this->options['label_element'];
      $label_classes = $this->options['label_classes'] ? ' class="' . $this->options['label_classes'] . '"' : '';
      $exposed_filters = array_filter(explode(' ', $this->options['filters']));
      $markup = '<div class="xposed-filters-wrapper">';
      $markup .= '<' . $label_element . $label_classes . '>' . $this->t($this->options['label']) . '</' . $label_element . '>';
      $markup .= '<ul class="xposed-filters">';

      // Get the value(s) of the filters.
      $build_count = 0;
      foreach ($this->exposedInput as $exposed_name => $exposed_value) {
        // Ignore any query string operators that have no input value.
        if (empty($this->exposedInput[$exposed_name])) {
          continue;
        }

        // Ignore any query string that is not related to exposed filters.
        if (empty($this->exposedTypes[$exposed_name])) {
          continue;
        }

        // Ignore any exposed filters that are not listed by the user.
        if (!empty($exposed_filters) && array_search($exposed_name, $exposed_filters) === FALSE) {
          continue;
        }

        $field_name = $this->exposedNames[$exposed_name];
        $filter = $this->view->filter[$field_name];

        // Build link text based on filter type.
        switch ($this->exposedTypes[$exposed_name]) {
          case 'Drupal\taxonomy\Plugin\views\filter\TaxonomyIndexTid':
            $link_text = Term::load($exposed_value)->getName();
            break;

          case 'Drupal\options\Plugin\views\filter\ListField':
            $value_options = $filter->getValueOptions();
            $link_text = $value_options[$exposed_value];
            break;

          case 'Drupal\views\Plugin\views\filter\BooleanOperator':
            $exposed_input = $this->exposedInput[$exposed_name];

            if ($exposed_input == 'All') {
              continue 2;
            }

            // Handle grouped filters.
            if ($filter->isAGroup()) {
              foreach ($exposed_input as $value) {
                $link_text = $filter->options['group_info']['group_items'][$value]['title'];
              }
            }
            // Handle single filters.
            else {
              if ($exposed_input == '1') {
                $link_text = ucfirst($exposed_name);
              }
              else {
                $link_text = 'Not ' . $exposed_name;
              }
            }
            break;

          case 'Drupal\entity_reference_exposed_filters\Plugin\views\filter\EREFNodeTitles':
            $link_text = Node::load($this->exposedInput[$exposed_name])->getTitle();
            break;

          case 'Drupal\geolocation\Plugin\views\filter\ProximityFilter':
            $filter_values = $filter->value;
            foreach ($filter_values as $value) {
              if (empty($value)) {
                continue 3;
              }
            }
            $units = $this->view->filter[$field_name]->options['proximity_units'];
            $link_text = $this->exposedInput['proximity'] . ' ' . $this->formatPlural($this->exposedInput['proximity'], $units, $units . 's');
            break;

          default:
            $link_text = $exposed_value;
            break;
        }

        // Rebuild URI starting with the bare path (without query string).
        $link_url = strtok($_SERVER['REQUEST_URI'], '?');

        // Parse the query string.
        parse_str($_SERVER['QUERY_STRING'], $qarray);

        // Only append a new query string if there's more than 1 item.
        if (count($qarray) > 1) {
          $link_url .= '?';

          // Unset this filter in its own link.
          $qkeys = array_keys($qarray);
          if (($key = array_search($exposed_name, $qkeys)) !== FALSE) {
            unset($qarray[$qkeys[$key]]);
            array_values($qarray);
          }

          // Append the query string to the link.
          $link_url .= http_build_query($qarray);
        }

        // Finally, add to filters array.
        $filters[] = [
          'url' => $link_url,
          'text' => $link_text,
        ];

        $build_count++;
      }

      // Return plain URL if there is only 1 filter.
      if (count($filters) == 1) {
        $markup .= '<li><a href="' . strtok($_SERVER['REQUEST_URI'], '?') . '" class="filter-cancel"><span class="filter-name">' . $filters[0]['text'] . '</span><span class="visually-hidden">' . $this->t('Clear filter') . '</span></a>';
      }
      else {
        foreach ($filters as $filter) {
          $markup .= '<li class="filter-wrapper"><a href="' . $filter['url'] . '"><span class="filter-name">' . $filter['text'] . '</span><span class="visually-hidden">' .  $this->t('Clear filter') . ' </span></a></li>';
        }
      }

      // End the list and wrapper.
      $markup .= '</ul>';
      $markup .= '</div>';

      // Return markup.
      if ($build_count > 0) {
        return [
          '#markup' => $markup,
        ];
      }
    }

    return [];
  }

  /**
   * Build data this class uses based on exposed data. We cannot do this in the
   * constructor because $this->view is not available until later.
   */
  private function buildExposedData() {
    // Set variables.
    $this->exposedInput = $this->view->getExposedInput();
    $this->exposedNames = [];
    $this->exposedTypes = [];

    foreach ($this->view->filter as $field => $filter) {
      if ($filter->isExposed()) {
        $field_name = $filter->exposedInfo()['value'];
        $this->exposedNames[$field_name] = $field;
        $this->exposedTypes[$field_name] = get_class($filter);
      }
    }

    // Remove exposed input that actually contains no data (like taxonomy "All")
    foreach ($this->exposedNames as $key => $val) {
      if (empty($this->view->filter[$val]->value)) {
        unset($this->exposedInput[$key]);
      }
    }
  }

}
