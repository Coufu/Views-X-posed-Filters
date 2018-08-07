/**
 * @param {*} $
 * Table WYSIWYG formatting
 */
const viewsXPosedFilters = ($) => {

  /**
   * Entry point.
   */
  function init() {
    if (viewsAjaxEnabled()) {
      $('.xposed-filters a.filter-cancel').click(e => {
        e.preventDefault();

        // TODO: Account for text-boxes too.
        let filter = $(e.target).closest('a').data('filter');
        $('.views-exposed-form [name=' + filter + ']').val('All').change();
      });
    }
  }

  /**
   * Check if Views AJAX has been enabled on this page.
   * @returns {boolean}
   */
  function viewsAjaxEnabled() {
    if ($._data($('.views-exposed-form .js-form-submit')[0], 'events').click) {
      return true;
    }
    else {
      return false;
    }
  }

  Drupal.behaviors.viewsXPosedFilters = {
    attach(context) {
      $(document, context).ready(init);
    },
  };
};

(viewsXPosedFilters(jQuery));
