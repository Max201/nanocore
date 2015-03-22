// Generated by CoffeeScript 1.4.0

/**
 * @package       Shop
 * @order_status  modules
 * @author        Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright     Copyright (c) 2014-2015, Nazar Mokrynskyi
 * @license       MIT License, see license.txt
*/


(function() {

  (function(L) {
    return Polymer({
      publish: {
        description: '',
        address: '',
        amount: 0,
        label: ''
      },
      progress_text: L.blockchain_payment_waiting_for_payment,
      ready: function() {
        var _this = this;
        return $(function() {
          _this.description = JSON.parse(_this.description);
          _this.text = L.blockchain_payment_scan_or_transfer(_this.amount, _this.address);
          $(_this.$.qr).qrcode({
            height: 512,
            text: 'bitcoin:' + _this.address + '?amount=' + _this.amount + '&label=' + _this.label,
            width: 512
          });
          return _this.update_status();
        });
      },
      update_status: function() {
        var _this = this;
        return $.ajax({
          url: 'api/Blockchain_payment/' + $(this).data('id'),
          type: 'get',
          success: function(data) {
            if (parseInt(data.confirmed)) {
              location.reload();
              return;
            }
            if (parseInt(data.paid)) {
              _this.progress_text = L.blockchain_payment_waiting_for_confirmations;
            }
            return setTimeout((function() {
              return _this.update_status();
            }), 5000);
          }
        });
      }
    });
  })(cs.Language);

}).call(this);
