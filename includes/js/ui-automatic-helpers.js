// Generated by CoffeeScript 1.9.3

/**
 * @package		UI automatic helpers
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2014-2015, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */

(function() {
  $(function() {
    var ui_automatic_helpers_update;
    window.no_ui_selector = '.cs-no-ui';
    ui_automatic_helpers_update = function(element) {
      var $element;
      $element = $(element);
      $element.filter('.cs-tabs:not(.uk-tab)').cs().tabs();
      $element.find('.cs-tabs:not(.uk-tab)').cs().tabs();
      if ($element.is(no_ui_selector) || $element.closest(no_ui_selector).length) {
        return;
      }
      return autosize($element.filter('textarea:not(.cs-no-resize, [data-autosize-on])').add($element.find("textarea:not(" + no_ui_selector + ", .cs-no-resize, [data-autosize-on])")));
    };
    return (function() {
      var body;
      body = document.querySelector('body');
      ui_automatic_helpers_update(body);
      return cs.observe_inserts_on(body, ui_automatic_helpers_update);
    })();
  });

}).call(this);
