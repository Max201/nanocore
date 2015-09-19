// Generated by LiveScript 1.4.0
/**
 * @package    CleverStyle CMS
 * @subpackage System module
 * @category   modules
 * @author     Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright  Copyright (c) 2015, Nazar Mokrynskyi
 * @license    MIT License, see license.txt
 */
(function(){
  var L, behaviors;
  L = cs.Language;
  behaviors = cs.Polymer.behaviors;
  behaviors.admin = behaviors.admin || {};
  behaviors.admin.System = {
    components: {
      _disable_component: function(component, component_type){
        var component_type_s, translation_key, title, message, this$ = this;
        component_type_s = component_type + 's';
        translation_key = component_type === 'module' ? 'disabling_of_module' : 'disabling_of_plugin';
        title = "<h3>" + L[translation_key](component) + "</h3>";
        message = '';
        $.getJSON("api/System/admin/" + component_type_s + "/" + component + "/dependent_packages", function(dependent_packages){
          var type, packages, translation_key, i$, len$, _package, modal;
          if (Object.keys(dependent_packages).length) {
            for (type in dependent_packages) {
              packages = dependent_packages[type];
              translation_key = type === 'modules' ? 'this_package_is_used_by_module' : 'this_package_is_used_by_plugin';
              for (i$ = 0, len$ = packages.length; i$ < len$; ++i$) {
                _package = packages[i$];
                message += "<p>" + L[translation_key](_package) + "</p>";
              }
            }
            message += "<p>" + L.dependencies_not_satisfied + "</p>";
            if (cs.simple_admin_mode) {
              cs.ui.notify(message, 'error', 5);
              return;
            }
          }
          modal = cs.ui.confirm(title + "" + message, function(){
            cs.Event.fire("admin/System/components/" + component_type_s + "/disable/before", {
              name: component
            }).then(function(){
              $.ajax({
                url: "api/System/admin/" + component_type_s + "/" + component,
                type: 'disable',
                success: function(){
                  this$.reload();
                  cs.ui.notify(L.changes_saved, 'success', 5);
                  cs.Event.fire("admin/System/components/" + component_type_s + "/disable/after", {
                    name: component
                  });
                }
              });
            });
          });
          modal.ok.innerHTML = L[!message ? 'disable' : 'force_disable_not_recommended'];
          modal.ok.primary = !message;
          modal.cancel.primary = !modal.ok.primary;
          $(modal).find('p').addClass('cs-text-error cs-block-error');
        });
      },
      _remove_completely_component: function(component, component_type){
        var component_type_s, translation_key, this$ = this;
        component_type_s = component_type + 's';
        translation_key = component_type === 'module' ? 'completely_remove_module' : 'completely_remove_plugin';
        cs.ui.confirm(L[translation_key](component), function(){
          $.ajax({
            url: "api/System/admin/" + component_type_s + "/" + component,
            type: 'delete',
            success: function(){
              this$.reload();
              cs.ui.notify(L.changes_saved, 'success', 5);
            }
          });
        });
      }
    }
  };
}).call(this);
