jQuery(document).ready(function() {
  Admin.setup_collection_buttons(document);
})

var Admin = {
  stopEvent: function(event) {
    // https://github.com/sonata-project/SonataAdminBundle/issues/151
    //if it is a standard browser use preventDefault otherwise it is IE then return false
    if (event.preventDefault) {
      event.preventDefault();
    } else {
      event.returnValue = false;
    }

    //if it is a standard browser get target otherwise it is IE then adapt syntax and get target
    if (typeof event.target != 'undefined') {
      targetElement = event.target;
    } else {
      targetElement = event.srcElement;
    }

    return targetElement;
  },
  setup_collection_buttons: function(subject) {
    jQuery(subject).on('click', '.collection-add', function(event) {
      Admin.stopEvent(event);

      var container = jQuery(this).closest('[data-prototype]');
      var newWidget = container.attr('data-prototype');
      newWidget = newWidget.replace(/__name__label__/g, container.children().length);
      newWidget = newWidget.replace(/__name__/g, container.children().length);
      jQuery(newWidget).insertBefore(jQuery(this).parent());
    });

    jQuery(subject).on('click', '.collection-delete', function(event) {
      Admin.stopEvent(event);

      jQuery(this).closest('.collection-row').remove();
    });
  }
}