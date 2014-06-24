// Generated by CoffeeScript 1.4.0

/**
 * @package		CleverStyle CMS
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2014, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
*/


(function() {

  $(function() {
    cs.async_call([
      function() {
        if (cs.in_admin) {
          $('.cs-reload-button').click(function() {
            return location.reload();
          });
          $('#change_theme, #change_color_scheme, #change_language').click(function() {
            return $('#apply_settings').click();
          });
          $('#change_active_languages').change(function() {
            return $(this).find("option[value='" + $('#change_language').val() + "']").prop('selected', true);
          });
          $('#cs-system-license-open').click(function() {
            return $('#cs-system-license').cs().modal('show');
          });
          $('.cs-permissions-invert').click(function() {
            return $(this).parentsUntil('div').find(':radio:not(:checked)[value!=-1]').prop('checked', true).change();
          });
          $('.cs-permissions-allow-all').click(function() {
            return $(this).parentsUntil('div').find(':radio[value=1]').prop('checked', true).change();
          });
          $('.cs-permissions-deny-all').click(function() {
            return $(this).parentsUntil('div').find(':radio[value=0]').prop('checked', true).change();
          });
          $('#cs-users-search-columns li').click(function() {
            var $this;
            $this = $(this);
            if ($this.hasClass('uk-button-primary')) {
              $this.removeClass('uk-button-primary');
            } else {
              $this.addClass('uk-button-primary');
            }
            return $('#cs-users-search-selected-columns').val($this.parent().children('.uk-button-primary').map(function() {
              return $.trim(this.innerHTML);
            }).get().join(';'));
          });
          $('#block_users_search').keyup(function(event) {
            if (event.which !== 13) {
              return;
            }
            $('.cs-block-users-changed').removeClass('cs-block-users-changed').appendTo('#cs-block-users-changed-permissions').each(function() {
              var found, id;
              id = $(this).find(':radio:first').attr('name');
              found = $('#cs-block-users-search-found');
              return found.val(found.val() + ',' + id.substring(6, id.length - 1));
            });
            return $.ajax({
              url: "" + cs.current_base_url + "/" + cs.route[0] + "/" + cs.route[1] + "/search_users",
              data: {
                found_users: $('#cs-block-users-search-found').val(),
                permission: $(this).attr('permission'),
                search_phrase: $(this).val()
              },
              success: function(result) {
                return $('#block_users_search_results').html(result).find(':radio').cs().radio().change(function() {
                  return $(this).parentsUntil('tr').parent().addClass('cs-block-users-changed');
                });
              }
            });
          }).keydown(function(event) {
            return event.which !== 13;
          });
          $('#cs-top-blocks-items, #cs-left-blocks-items, #cs-floating-blocks-items, #cs-right-blocks-items, #cs-bottom-blocks-items').sortable({
            connectWith: '.cs-blocks-items',
            items: 'li:not(:first)'
          }).on('sortupdate', function() {
            return $('#cs-blocks-position').val(JSON.stringify({
              top: $('#cs-top-blocks-items li:not(:first)').map(function() {
                return $(this).data('id');
              }).get(),
              left: $('#cs-left-blocks-items li:not(:first)').map(function() {
                return $(this).data('id');
              }).get(),
              floating: $('#cs-floating-blocks-items li:not(:first)').map(function() {
                return $(this).data('id');
              }).get(),
              right: $('#cs-right-blocks-items li:not(:first)').map(function() {
                return $(this).data('id');
              }).get(),
              bottom: $('#cs-bottom-blocks-items li:not(:first)').map(function() {
                return $(this).data('id');
              }).get()
            }));
          });
          return $('#cs-users-groups-list, #cs-users-groups-list-selected').sortable({
            connectWith: '#cs-users-groups-list, #cs-users-groups-list-selected',
            items: 'li:not(:first)'
          }).on('sortupdate', function() {
            var selected;
            $('#cs-users-groups-list').find('.uk-alert-success').removeClass('uk-alert-success').addClass('uk-alert-warning');
            selected = $('#cs-users-groups-list-selected');
            selected.find('.uk-alert-warning').removeClass('uk-alert-warning').addClass('uk-alert-success');
            return $('#cs-user-groups').val(JSON.stringify(selected.children('li:not(:first)').map(function() {
              return $(this).data('id');
            }).get()));
          });
        }
      }
    ]);
  });

}).call(this);
