// Generated by CoffeeScript 1.9.3

/**
 * @package   Shop
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2014-2015, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */

(function() {
  (function(cart, L) {
    return Polymer({
      'is': 'cs-shop-add-to-cart',
      behaviors: [cs.Polymer.behaviors.Language],
      properties: {
        item_id: Number,
        in_cart: 0
      },
      ready: function() {
        this.set('in_cart', cart.get(this.item_id));
        return UIkit.tooltip(this.$.in_cart, {
          animation: true,
          delay: 200
        });
      },
      add: function() {
        return this.set('in_cart', cart.add(this.item_id));
      }
    });
  })(cs.shop.cart, cs.Language);

}).call(this);
