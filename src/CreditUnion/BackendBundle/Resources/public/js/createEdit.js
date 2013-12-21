$(function() {
  $("#sortableColumn").sortable({
    update: function(event, ui) {
      $('#sortableColumn').find('.checkbox').each(function(index) {
        $(this).find('input.sortOrder').val(index + 1);
        $(this).find('span.sortOrder').html(index + 1);
      });
    },
  });
  $("#sortableColumn").disableSelection();

  $(".checkMatch").on('click', function() {
    $(this).closest('.panel-select').toggleClass('disabled');
  });
});

$(document).ready(function() {
  $(".checkMatch:checked").each(function() {
    $(this).closest('.panel-select').removeClass('disabled');
  });
});