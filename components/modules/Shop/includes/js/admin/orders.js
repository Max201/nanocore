// Generated by CoffeeScript 1.4.0

/**
 * @package   Shop
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2014, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
*/


(function() {

  $(function() {
    var L, make_modal;
    L = cs.Language;
    make_modal = function(shipping_types, order_statuses, title, action) {
      var modal, shipping_types_list;
      shipping_types = (function() {
        var shipping_type, shipping_types_;
        shipping_types_ = {};
        for (shipping_type in shipping_types) {
          shipping_type = shipping_types[shipping_type];
          shipping_types_[shipping_type.id] = shipping_type;
        }
        return shipping_types_;
      })();
      shipping_types_list = (function() {
        var key, keys, shipping_type, shipping_types_list_, _i, _len, _results;
        shipping_types_list_ = {};
        keys = [];
        for (shipping_type in shipping_types) {
          shipping_type = shipping_types[shipping_type];
          shipping_types_list_[shipping_type.title] = "<option value=\"" + shipping_type.id + "\">" + shipping_type.title + "</option>";
          keys.push(shipping_type.title);
        }
        keys.sort();
        _results = [];
        for (_i = 0, _len = keys.length; _i < _len; _i++) {
          key = keys[_i];
          _results.push(shipping_types_list_[key]);
        }
        return _results;
      })();
      shipping_types_list = shipping_types_list.join('');
      order_statuses = (function() {
        var key, keys, order_status, order_statuses_, _i, _len, _results;
        order_statuses_ = {};
        keys = [];
        for (order_status in order_statuses) {
          order_status = order_statuses[order_status];
          order_statuses_[order_status.title] = "<option value=\"" + order_status.id + "\">" + order_status.title + "</option>";
          keys.push(order_status.title);
        }
        keys.sort();
        _results = [];
        for (_i = 0, _len = keys.length; _i < _len; _i++) {
          key = keys[_i];
          _results.push(order_statuses_[key]);
        }
        return _results;
      })();
      order_statuses = order_statuses.join('');
      modal = $.cs.simple_modal("<form>\n	<h3 class=\"cs-center\">" + title + "</h3>\n	<p class=\"uk-hidden\">\n		" + L.shop_datetime + ": <span class=\"date\"></span>\n	</p>\n	<p>\n		" + L.shop_user + ": <span class=\"username\"></span>, id: <input name=\"user\" required>\n	</p>\n	<p>\n		<div class=\"items\"></div>\n		<button type=\"button\" class=\"add-item uk-button\">" + L.shop_add_item + "</button>\n	</p>\n	<p>\n		" + L.shop_shipping_type + ": <select name=\"shipping_type\" required>" + shipping_types_list + "</select>\n	</p>\n	<p>\n		" + L.shop_shipping_cost + ": <input name=\"shipping_cost\"> (<span id=\"shipping_cost\"></span>)\n	</p>\n	<p>\n		" + L.shop_shipping_username + ": <input name=\"shipping_username\">\n	</p>\n	<p>\n		" + L.shop_shipping_phone + ": <input name=\"shipping_phone\">\n	</p>\n	<p>\n		" + L.shop_shipping_address + ": <textarea name=\"shipping_address\"></textarea>\n	</p>\n	<p>\n		" + L.shop_status + ": <select name=\"status\" required>" + order_statuses + "</select>\n	</p>\n	<p>\n		" + L.shop_comment + ": <textarea name=\"comment\"></textarea>\n	</p>\n	<p>\n		<button class=\"uk-button\" type=\"submit\">" + action + "</button>\n	</p>\n</form>", false, 1200);
      (function() {
        var timeout;
        timeout = 0;
        return modal.find('[name=user]').keyup(function() {
          var _this = this;
          clearTimeout(timeout);
          return timeout = setTimeout((function() {
            return $.getJSON('api/System/profiles/' + $(_this).val(), function(profile) {
              return modal.find('.username').html(profile.username || profile.login);
            });
          }), 300);
        });
      })();
      (function() {
        var shipping_type_select;
        shipping_type_select = modal.find('[name=shipping_type]');
        shipping_type_select.change(function() {
          var shipping_type;
          shipping_type = shipping_types[$(this).val()];
          modal.find('[name=shipping_phone]').parent()[parseInt(shipping_type.phone_needed) ? 'show' : 'hide']();
          modal.find('[name=shipping_address]').parent()[parseInt(shipping_type.address_needed) ? 'show' : 'hide']();
          return modal.find('#shipping_cost').html(shipping_type.price);
        });
        return shipping_type_select.change();
      })();
      (function() {
        var items_container, timeout;
        items_container = modal.find('.items');
        modal.add_item = function(item) {
          var callback;
          callback = function(item_data) {
            var total_price;
            total_price = item_data.price * item.units;
            items_container.append("<p>\n	" + L.shop_item + ": <input value=\"-\" class=\"title uk-form-blank\" readonly>\n	id: <input name=\"items[item][]\" value=\"" + item.item + "\" class=\"uk-form-width-small\" required>\n	" + L.shop_unit_price + " <input name=\"items[unit_price][]\" value=\"" + item.unit_price + "\" class=\"uk-form-width-small\" required> (<span class=\"unit-price\">" + item_data.price + "</span>)\n	" + L.shop_units + " <input name=\"items[units][]\" value=\"" + item.units + "\" class=\"uk-form-width-mini\" required>\n	" + L.shop_total_price + " <input name=\"items[price][]\" value=\"" + item.price + "\" class=\"uk-form-width-small\" required> (<span class=\"item-price\" data-original-price=\"" + item_data.price + "\">" + total_price + "</span>)\n	<button type=\"button\" class=\"delete-item uk-button\"><i class=\"uk-icon-close\"></i></button>\n</p>");
            return items_container.children(':last').find('.title').val(item_data.title);
          };
          if (item.item) {
            return $.getJSON("api/Shop/admin/items/" + item.item, callback);
          } else {
            return callback({
              title: '-',
              price: 0
            });
          }
        };
        timeout = 0;
        return items_container.on('keyup change', "[name='items[units][]']", function() {
          var $this, item_price_container;
          $this = $(this);
          item_price_container = $this.parent().find('.item-price');
          return item_price_container.html(item_price_container.data('original-price') * $this.val());
        }).on('keyup', "[name='items[item][]']", function() {
          var _this = this;
          clearTimeout(timeout);
          return timeout = setTimeout((function() {
            var $this, container;
            $this = $(_this);
            container = $this.parent();
            return $.ajax({
              url: 'api/Shop/admin/items/' + $this.val(),
              type: 'get',
              success: function(item) {
                container.find('.title').val(item.title);
                container.find('.unit-price').html(item.price);
                container.find('.item-price').data('original-price', item.price);
                return container.find("[name='items[units][]']").change();
              },
              error: function() {
                container.find('.title').val('-');
                container.find('.unit-price').html(0);
                container.find('.item-price').data('original-price', 0);
                return container.find("[name='items[units][]']").change();
              }
            });
          }), 300);
        }).on('click', '.delete-item', function() {
          return $(this).parent().remove();
        });
      })();
      return modal.on('click', '.add-item', function() {
        return modal.add_item({
          item: '',
          unit_price: '',
          units: '',
          price: ''
        });
      });
    };
    return $('html').on('mousedown', '.cs-shop-order-add', function() {
      return $.when($.getJSON('api/Shop/admin/shipping_types'), $.getJSON('api/Shop/admin/order_statuses')).done(function(shipping_types, order_statuses) {
        var modal;
        modal = make_modal(shipping_types[0], order_statuses[0], L.shop_order_addition, L.shop_add);
        return modal.find('form').submit(function() {
          var data;
          data = $(this).serialize();
          $.ajax({
            url: 'api/Shop/admin/orders',
            type: 'post',
            data: data,
            success: function(url) {
              url = url.split('/');
              return $.ajax({
                url: 'api/Shop/admin/orders/' + url.pop() + '/items',
                type: 'put',
                data: data,
                success: function() {
                  alert(L.shop_added_successfully);
                  return location.reload();
                }
              });
            }
          });
          return false;
        });
      });
    }).on('mousedown', '.cs-shop-order-edit', function() {
      var $this, date, id, username;
      $this = $(this);
      id = $this.data('id');
      username = $this.data('username');
      date = $this.data('date');
      return $.when($.getJSON('api/Shop/admin/shipping_types'), $.getJSON('api/Shop/admin/order_statuses'), $.getJSON("api/Shop/admin/orders/" + id), $.getJSON("api/Shop/admin/orders/" + id + "/items")).done(function(shipping_types, order_statuses, order, items) {
        var modal;
        modal = make_modal(shipping_types[0], order_statuses[0], L.shop_order_edition, L.shop_edit);
        modal.find('form').submit(function() {
          var data;
          data = $(this).serialize();
          $.ajax({
            url: "api/Shop/admin/orders/" + id,
            type: 'put',
            data: data,
            success: function() {
              return $.ajax({
                url: "api/Shop/admin/orders/" + id + "/items",
                type: 'put',
                data: data,
                success: function() {
                  alert(L.shop_edited_successfully);
                  return location.reload();
                }
              });
            }
          });
          return false;
        });
        order = order[0];
        modal.find('.date').html(date).parent().show();
        modal.find('.username').html(username);
        modal.find('[name=user]').val(order.user);
        modal.find('[name=shipping_phone]').val(order.shipping_phone);
        modal.find('[name=shipping_address]').val(order.shipping_address);
        modal.find('[name=shipping_type]').val(order.shipping_type).change();
        modal.find('[name=shipping_cost]').val(order.shipping_cost).change();
        modal.find('[name=shipping_username]').val(order.shipping_username).change();
        modal.find('[name=status]').val(order.status);
        modal.find('[name=comment]').val(order.comment);
        items = items[0];
        return items.forEach(function(item) {
          return modal.add_item(item);
        });
      });
    }).on('mousedown', '.cs-shop-order-delete', function() {
      var id;
      id = $(this).data('id');
      if (confirm(L.shop_sure_want_to_delete)) {
        return $.ajax({
          url: "api/Shop/admin/orders/" + id,
          type: 'delete',
          success: function() {
            alert(L.shop_deleted_successfully);
            return location.reload();
          }
        });
      }
    });
  });

}).call(this);
