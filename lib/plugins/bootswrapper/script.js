/*!
 * DokuWiki Bootstrap Wrapper Plugin
 *
 * Home     http://dokuwiki.org/plugin:bootswrapper
 * Author   Giuseppe Di Terlizzi <giuseppe.diterlizzi@gmail.com>
 * License  GPL 2 (http://www.gnu.org/licenses/gpl.html)
 */

jQuery(document).ready(function() {

  setTimeout(function() {

  jQuery('.bs-wrap .fix-media-list-overlap').removeClass('fix-media-list-overlap');

  // Jumbotron
  jQuery('.bs-wrap-jumbotron .page-header').removeClass('page-header');

  // Tooltips
  jQuery('.bs-wrap-tooltip').tooltip();

  // Popovers
  jQuery('.bs-wrap-popover').popover();

  // Images
  jQuery('.bs-wrap-image').each(function() {

    var $img_wrap = jQuery(this),
        img_data  = $img_wrap.data();

    $img_wrap.find('img').addClass(['img-', img_data.imgShape].join(''));

  });


  // Nav
  jQuery('.bs-wrap-nav').each(function() {

    var $nav_wrap = jQuery(this),
        nav_data  = $nav_wrap.data(),
        nav_class = ['nav'];

    for (key in nav_data) {

      var value = nav_data[key];

      switch (key) {
        case 'navType':
          nav_class.push(['nav-', value].join(''));
          break;
        case 'navStacked':
          if (value) nav_class.push('nav-stacked');
          break;
        case 'navJustified':
          if (value) nav_class.push('nav-justified');
          break;
      }

    }

    $nav_wrap.find('ul:first').addClass(nav_class.join(' '));

    var $nav = $nav_wrap.find('.nav');

    $nav.find('div.li *').unwrap();
    $nav.find('li').attr('role', 'presentation');
    $nav.find('.curid').parent('li').addClass('active');

    // Drop-down menu
    $nav.find('li ul')
      .addClass('dropdown-menu')
      .parent('li')
      .addClass('dropdown');

    $nav.find('.dropdown div.li').replaceWith(function() {
      return jQuery('<a class="dropdown-toggle" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false" />')
        .html(jQuery(this).contents())
    });

    // Sidebar (Bootstrap3 template)
    $nav.find('li.dropdown').contents().filter(function() {
      return this.nodeType === 3  && this.data.trim().length > 0
    }).wrap('<a class="dropdown-toggle" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false" />');

    $nav.find('.dropdown-toggle').append(' <span class="caret"/>');

    // Tab panels
    if ($nav_wrap.find('.tab-pane').length) {

      if (! $nav_wrap.find('.tab-content').length) {
        $nav_wrap.find('.tab-pane').wrapAll(jQuery('<div class="tab-content"/>'));
      }

      $nav.find('a').attr('data-toggle', 'tab').attr('role', 'tab');

      if (nav_data.navFade) {
        $nav_wrap.find('.tab-content .tab-pane').addClass('fade');
      }

      $nav.find('a:first').tab('show');

    }

    jQuery(window).on('hashchange',function() { 
      jQuery('.bs-wrap-nav .nav a[href="'+location.hash+'"]').tab('show');
    });

  });


  // Buttons
  jQuery('.bs-wrap-button').each(function() {

    var $btn_wrap = jQuery(this),
        btn_data  = $btn_wrap.data(),
        $btn_link = $btn_wrap.find('a'),
        btn_class = ['btn'];

    // Add Fake link
    if (! $btn_link.length) {

      btn_label = $btn_wrap.html();
      $btn_wrap.html('');

      $btn_link  = jQuery('<a href="javascript:void(0)"/>').html(btn_label);
      jQuery(this).append($btn_link);

    }

    for (key in btn_data) {

      var value = btn_data[key];

      switch (key) {
        case 'btnType':
        case 'btnSize':
          btn_class.push(['btn-', value].join(''));
          break;
        case 'btnBlock':
          btn_class.push('btn-block');
          break;
        case 'btnDisabled':
          btn_class.push('disabled');
          break;
        case 'btnCollapse':
          $btn_link.attr('data-toggle', 'collapse');
          $btn_link.attr('data-target', '#' + value);
          $btn_link.on('click', function(e){ e.preventDefault(); });
          break;
        case 'btnModal':
          $btn_link.attr('data-toggle', 'modal');
          $btn_link.attr('data-target', '#' + value);
          $btn_link.on('click', function(e){ e.preventDefault(); });
          break;
        case 'btnIcon':
          var icon = ['<i class="', value, '"/> '].join('');
          $btn_link.prepend(icon);
          break;
      }

    }

    $btn_link.addClass(btn_class.join(' '));
    $btn_link.attr('role', 'button');

    if ($btn_link.hasClass('curid')) {
        $btn_link.addClass('active');
    }

    if ($btn_link.hasClass('urlextern')) {
      $btn_link.removeClass('urlextern').addClass('wikilink1');
    }

  });


  // List Group
  jQuery('.bs-wrap-list-group').each(function() {

      var $list_wrap = jQuery(this);

      var $icon_links = $list_wrap.find('li i + a');

      if ($icon_links.length) {
        jQuery.each($icon_links, function() {
          var $link = jQuery(this),
              $icon = $link.prev();
          $icon.prependTo($link);
          $icon.after(' ');
        });
      }

      $list_wrap.find('div.li').contents().unwrap();
      $list_wrap.find('ul').addClass('list-group');
      $list_wrap.find('ul > li').addClass('list-group-item');

      if ($list_wrap.find('a').length) {

          $list_wrap.find('a').parent().each(function() {

            var $list = jQuery(this);

            if ($list.children().length > 1) {

              $list.wrapInner('<p class="list-group-item-text"/>');

              var $link = $list.find('a');

              $link.wrapInner('<h4 class="list-group-item-heading"/>');
              $link.prependTo($list);
              $list.find('p').appendTo($link);

            }

          });

          $list_wrap.find('a').parent().contents().unwrap();
          $list_wrap.find('ul a').parent().contents().unwrap();
          $list_wrap.addClass('list-group');
          $list_wrap.find('a').addClass('list-group-item');
          $list_wrap.find('a.curid').removeClass('curid').addClass('active');

      }

      $list_wrap.removeClass('hide');


  });


  // Accordion
  jQuery('.bs-wrap-accordion').each(function() {

    var $accordion   = jQuery(this),
        accordion_id = Math.random().toString(36).substr(2, 9),
        is_collapsed = $accordion.hasClass('bs-wrap-accordion-collapsed');

    $accordion.find('.panel').each(function() {

      var $panel   = jQuery(this),
          panel_id = accordion_id + '_' + Math.random().toString(36).substr(2, 9);

      $panel.find('.panel-heading').wrapInner('<a role="button" data-toggle="collapse" data-parent="#'+ accordion_id +'" href="#'+ panel_id +'">');
      $panel.find('.panel-body').wrap('<div id="'+ panel_id +'" class="panel-collapse collapse" role="tabpanel">');

    });

    $accordion.attr('id', accordion_id);

    if ($accordion.find('.panel-collapse').length > 1 && ! is_collapsed) {
      $accordion.find('.panel-collapse').first().addClass('in');
    }

  });


  // Carousel
  jQuery('.bs-wrap-carousel').each(function() {

    var $carousel   = jQuery(this),
        carousel_id = Math.random().toString(36).substr(2, 9),
        $images     = $carousel.find('img'),
        $slides     = $carousel.find('.bs-wrap-slide'),
        $caption    = $carousel.find('.bs-wrap-caption'),
        $indicators = $carousel.find('ol');

    $carousel.attr('id', carousel_id);

    $images.removeClass('media')
            .removeClass('medialeft')
            .removeClass('mediaright')
            .removeClass('mediacenter');

    if (! $slides.length) {
      $images.wrap('<div class="item"/>');
    }

    if ($caption.length) {
      $caption.removeClass('caption').addClass('carousel-caption');
    }

    $carousel.find('.carousel-control').attr('href', '#' + carousel_id);

    for (var i = 0; i < $images.length; i++) {
      $indicators.append('<li data-target="#'+ carousel_id +'" data-slide-to="'+i+'"></li>');
    }

    $carousel.find('.item').first().addClass('active');
    $indicators.find('li').first().addClass('active');

  });


  // Panel
  jQuery('.bs-wrap-panel').each(function(){

    var $panel         = jQuery(this),
        $panel_body    = $panel.find('.panel-body'),
        $panel_heading = $panel.find('.panel-heading'),
        $first_title   = $panel_body.find('> h4:first');

    if ($first_title.length && ! $panel_heading.length) {

      var $panel_heading = jQuery('<div class="panel-heading"></div>');

      $first_title.addClass('panel-title');
      $panel_heading.append($first_title);
      $panel.prepend($panel_heading);

    }

  });


  //Modal
  jQuery('.bs-wrap-modal').each(function(){
    if (jQuery(this).attr('data-show') == true) {
      jQuery(this).modal('show');
    } else {
      jQuery(this).modal('hide');
    }
  });


  }, 0);

});
