$(document).ready(function () {

  $(document).on("scroll", onScroll);

      // Smooth Scrolling Effect
      $('a[href^="#"]').on('click', function (e) {
          e.preventDefault();

          $(document).off("scroll");

          $('a').each(function () {
              $(this).removeClass('active');
          })

          $(this).addClass('active');

          var target = this.hash;
          $target = $(target);
          $('html, body').stop().animate({
              'scrollTop': $target.offset().top - 100
              }, 800, 'swing', function () {
              $(document).on("scroll", onScroll);
          });

      });

  });

  function onScroll(event){
      var scrollPos = $(document).scrollTop();
      $('.no-more-posts a').each(function () {
          var currLink = $(this);
          var refElement = $(currLink.attr("href"));
          if ((refElement.position().top - 100) <= scrollPos && (refElement.position().top - 100) + refElement.height() > scrollPos) {
              $('no-more-posts a').removeClass("active");
              currLink.addClass("active");
          }
          else {
              currLink.removeClass("active");
          }
      });

  }
