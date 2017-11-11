# Views X-posed Filters

A Drupal 8 Views area plugin that displays a list of linked active exposed filters. Clicking a link will cancel out that exposed filter.

Let's say you have two exposed filters: First Name and Last Name. If they search first name John and last name Doe, two links will appear: John and Doe. If they click the John link, the views page will refresh with the First Name exposed filter cleared out.

## Getting Started

### Installation
- Download and place directory into modules folder.
- Enable the module

### Usage
- Add "X-posed Filters" to your existing Views' header/footer (which should have some exposed filters)
- Go back to your view and experiment by using some exposed filters.
- Revel in awe of the simple yet awesome functionality

## Additional Dev Notes

### Supported Exposed Filters
There is a catch-all for all simple text-type filters. There is also customized functionality for the filters listed below. Request more by contacting me, or fork this and make your own, then submit a pull request.

You can add more handlers in ViewsXPosedFilters::render() in the switch() statement.

Core
- Drupal\taxonomy\Plugin\views\filters\TaxonomyIndexTid
- Drupal\options\Plugin\views\filter\ListField
- Drupal\views\Plugin\views\filter\BooleanOperator (grouped filters supported as well)

Contrib
- Drupal\entity_reference_exposed_filters\Plugin\views\filter\EREFNodeTitles
- Drupal\geolocation\Plugin\views\filter\ProximityFilter

### Other Notes
- There is no CSS applied. The label is customizable in the Views UI, and the list of links is just contained as an HTML unordered list. It will require you to style it on your own.
- This plugin *should* be accessibility-friendly. If there are any problems/suggestions to improve this, please contact me.