jQuery(document).ready(function ($) {
  let $tmpRow = $('<tr class="custom-page__grid__row custom-page__grid__row--after"></tr>');
  let $currentClickedElement;
  let remoteContent = [];

  $('[data-ajax-html-enabled="true"]').click(function (e) {
    e.preventDefault();

    // https://css-tricks.com/snippets/jquery/compare-jquery-objects/
    if ($currentClickedElement && $currentClickedElement[0] === $(this)[0]) {
      $tmpRow.slideToggle(300);
    } else {
      if ($currentClickedElement) {
        $currentClickedElement.removeClass('active');
      }
      let $currentRow = $(this).parents('.custom-page__grid__row');

      $tmpRow.html('<td colspan="' + $currentRow.find('> td').length + '"></td>');
      $currentRow.after($tmpRow);

      $tmpRow.addClass('processing').removeClass('error');
      $tmpRow.slideDown(300);

      let ajaxUrl = $(this).data('ajax-html-url');
      $currentClickedElement = $(this);
      $currentClickedElement.addClass('active');

      if (remoteContent[ajaxUrl]) {
        $('.custom-page__grid__row--after').removeClass('processing');
        $('.custom-page__grid__row--after > td').html(remoteContent[ajaxUrl]);
      } else {
        $.ajax({
          url: ajaxUrl,
          dataType: 'html',
          context: $(this),
        }).success(function (htmlData) {
          let ajaxUrl = $(this).data('ajax-html-url');
          remoteContent[ajaxUrl] = htmlData;
          $('.custom-page__grid__row--after').removeClass('processing');
          $('.custom-page__grid__row--after > td').html(htmlData);
        }).error(function () {
          $('.custom-page__grid__row--after').removeClass('processing').addClass('error');
          $('.custom-page__grid__row--after > td').html($(this).data('ajax-html-error-message'));
        });
      }
    }
  });
}(jQuery));
