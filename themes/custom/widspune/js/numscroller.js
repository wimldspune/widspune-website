/*$('.count').each(function () {alert('hi');
  $(this).prop('Counter',0).animate({
    Counter: $(this).text()
  }, {
    duration: 4000,
    easing: 'swing',
    step: function (now) {
      $(this).text(Math.ceil(now));
    }
  });
});
*/
(function ($) {
$('.count').each(function () {
  $(this).prop('Counter',0).animate({
    Counter: $(this).text()
  }, {
    duration: 4000,
    easing: 'swing',
    step: function (now) {
      $(this).text(Math.ceil(now));
    }
  });
});
}(jQuery));
/*

      <div class="wrapper">
        <div id="shiva"><span class="count">2000</span></div>
      </div>
*/