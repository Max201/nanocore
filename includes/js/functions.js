// Generated by CoffeeScript 1.9.3

/**
 * @package		CleverStyle CMS
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2015, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */

(function() {
  var L,
    hasProp = {}.hasOwnProperty,
    slice = [].slice;

  L = cs.Language;


  /**
   * Adds method for symbol replacing at specified position
   *
   * @param {int}		index
   * @param {string}	symbol
   *
   * @return {string}
   */

  String.prototype.replaceAt = function(index, symbol) {
    return this.substr(0, index) + symbol + this.substr(index + symbol.length);
  };


  /**
   * Supports algorithms sha1, sha224, sha256, sha384, sha512
   *
   * @param {string} algo Chosen algorithm
   * @param {string} data String to be hashed
   * @return {string}
   */

  cs.hash = function(algo, data) {
    algo = (function() {
      switch (algo) {
        case 'sha1':
          return 'SHA-1';
        case 'sha224':
          return 'SHA-224';
        case 'sha256':
          return 'SHA-256';
        case 'sha384':
          return 'SHA-384';
        case 'sha512':
          return 'SHA-512';
        default:
          return algo;
      }
    })();
    return (new jsSHA(data, 'ASCII')).getHash(algo, 'HEX');
  };


  /**
   * Function for setting cookies taking into account cookies prefix
   *
   * @param {string}	name
   * @param {string}	value
   * @param {int}		expires
   *
   * @return {bool}
   */

  cs.setcookie = function(name, value, expires) {
    var date, options;
    name = cs.cookie_prefix + name;
    options = {
      path: '/',
      domain: cs.cookie_domain,
      secure: cs.protocol === 'https'
    };
    if (!value) {
      return $.removeCookie(name);
    }
    if (expires) {
      date = new Date();
      date.setTime(expires * 1000);
      options.expires = date;
    }
    return !!$.cookie(name, value, options);
  };


  /**
   * Function for getting of cookies, taking into account cookies prefix
   *
   * @param {string}			name
   *
   * @return {bool|string}
   */

  cs.getcookie = function(name) {
    name = cs.cookie_prefix + name;
    return $.cookie(name);
  };


  /**
   * Sign in into system
   *
   * @param {string} login
   * @param {string} password
   */

  cs.sign_in = function(login, password) {
    login = String(login).toLowerCase();
    password = String(password);
    return $.ajax({
      url: 'api/System/user/sign_in',
      cache: false,
      data: {
        login: cs.hash('sha224', login),
        password: cs.hash('sha512', cs.hash('sha512', password) + cs.public_key)
      },
      type: 'post',
      success: function() {
        return location.reload();
      }
    });
  };


  /**
   * Sign out
   */

  cs.sign_out = function() {
    return $.ajax({
      url: 'api/System/user/sign_out',
      cache: false,
      data: {
        sign_out: true
      },
      type: 'post',
      success: function() {
        return location.reload();
      }
    });
  };


  /**
   * Registration in the system
   *
   * @param {string} email
   */

  cs.registration = function(email) {
    if (!email) {
      cs.ui.confirm(L.please_type_your_email);
      return;
    }
    email = String(email).toLowerCase();
    return $.ajax({
      url: 'api/System/user/registration',
      cache: false,
      data: {
        email: email
      },
      type: 'post',
      success: function(result) {
        if (result === 'reg_confirmation') {
          return cs.ui.simple_modal('<div>' + L.reg_confirmation + '</div>');
        } else if (result === 'reg_success') {
          return cs.ui.simple_modal('<div>' + L.reg_success + '</div>');
        }
      }
    });
  };


  /**
   * Password restoring
   *
   * @param {string} email
   */

  cs.restore_password = function(email) {
    if (!email) {
      cs.ui.alert(L.please_type_your_email);
      return;
    }
    email = String(email).toLowerCase();
    return $.ajax({
      url: 'api/System/user/restore_password',
      cache: false,
      data: {
        email: cs.hash('sha224', email)
      },
      type: 'post',
      success: function(result) {
        if (result === 'OK') {
          return cs.ui.simple_modal('<div>' + L.restore_password_confirmation + '</div>');
        }
      }
    });
  };


  /**
   * Password changing
   *
   * @param {string} current_password
   * @param {string} new_password
   * @param {Function} success
   * @param {Function} error
   */

  cs.change_password = function(current_password, new_password, success, error) {
    if (!current_password) {
      cs.ui.alert(L.please_type_current_password);
      return;
    } else if (!new_password) {
      cs.ui.alert(L.please_type_new_password);
      return;
    } else if (current_password === new_password) {
      cs.ui.alert(L.current_new_password_equal);
      return;
    } else if (String(new_password).length < cs.password_min_length) {
      cs.ui.alert(L.password_too_short);
      return;
    } else if (cs.password_check(new_password) < cs.password_min_strength) {
      cs.ui.alert(L.password_too_easy);
      return;
    }
    current_password = cs.hash('sha512', cs.hash('sha512', String(current_password)) + cs.public_key);
    new_password = cs.hash('sha512', cs.hash('sha512', String(new_password)) + cs.public_key);
    return $.ajax({
      url: 'api/System/user/change_password',
      cache: false,
      data: {
        current_password: current_password,
        new_password: new_password
      },
      type: 'post',
      success: function(result) {
        if (result === 'OK') {
          if (success) {
            return success();
          } else {
            return cs.ui.alert(L.password_changed_successfully);
          }
        } else {
          if (error) {
            return error();
          } else {
            return cs.ui.alert(result);
          }
        }
      },
      error: function() {
        return error();
      }
    });
  };


  /**
   * Check password strength
   *
   * @param	string	password
   * @param	int		min_length
   *
   * @return	int		In range [0..7]<br><br>
   * 					<b>0</b> - short password<br>
   * 					<b>1</b> - numbers<br>
   *  				<b>2</b> - numbers + letters<br>
   * 					<b>3</b> - numbers + letters in different registers<br>
   * 		 			<b>4</b> - numbers + letters in different registers + special symbol on usual keyboard +=/^ and others<br>
   * 					<b>5</b> - numbers + letters in different registers + special symbols (more than one)<br>
   * 					<b>6</b> - as 5, but + special symbol, which can't be found on usual keyboard or non-latin letter<br>
   * 					<b>7</b> - as 5, but + special symbols, which can't be found on usual keyboard or non-latin letter (more than one symbol)<br>
   */

  cs.password_check = function(password, min_length) {
    var $strength, matches;
    password = new String(password);
    min_length = min_length || 4;
    password = password.replace(/\s+/g, ' ');
    $strength = 0;
    if (password.length >= min_length) {
      matches = password.match(/[~!@#\$%\^&\*\(\)\-_=+\|\\\/;:,\.\?\[\]\{\}]/g);
      if (matches) {
        $strength = 4;
        if (matches.length > 1) {
          ++$strength;
        }
      } else {
        if (/[A-Z]+/.test(password)) {
          ++$strength;
        }
        if (/[a-z]+/.test(password)) {
          ++$strength;
        }
        if (/[0-9]+/.test(password)) {
          ++$strength;
        }
      }
      matches = password.match(/[^0-9a-z~!@#\$%\^&\*\(\)\-_=+\|\\\/;:,\.\?\[\]\{\}]/ig);
      if (matches) {
        ++$strength;
        if (matches.length > 1) {
          ++$strength;
        }
      }
    }
    return $strength;
  };


  /**
   * Bitwise XOR operation for 2 strings
   *
   * @param {string} string1
   * @param {string} string2
   *
   * @return {string}
   */

  cs.xor_string = function(string1, string2) {
    var j, k, len1, len2, pos, ref, ref1;
    len1 = string1.length;
    len2 = string2.length;
    if (len2 > len1) {
      ref = [string2, string1, len2, len1], string1 = ref[0], string2 = ref[1], len1 = ref[2], len2 = ref[3];
    }
    for (j = k = 0, ref1 = len1; 0 <= ref1 ? k < ref1 : k > ref1; j = 0 <= ref1 ? ++k : --k) {
      pos = j % len2;
      string1 = string1.replaceAt(j, String.fromCharCode(string1.charCodeAt(j) ^ string2.charCodeAt(pos)));
    }
    return string1;
  };


  /**
   * Prepare text to be used as value for html attribute value
   *
   * @param {string}|{string}[] string
   *
   * @return {string}|{string}[]
   */

  cs.prepare_attr_value = function(string) {
    var k, len, ref, results, s;
    if (string instanceof Array) {
      ref = string.slice(0);
      results = [];
      for (k = 0, len = ref.length; k < len; k++) {
        s = ref[k];
        results.push(cs.prepare_attr_value(s));
      }
      return results;
    } else {
      return String(string).replace(/&/g, '&amp;').replace(/'/g, '&apos;').replace(/"/g, '&quot;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
    }
  };


  /**
   * Asynchronous execution of array of the functions
   *
   * @param {function[]}	functions
   * @param {int}			timeout
   */

  cs.async_call = function(functions, timeout) {
    var fn, i;
    timeout = timeout || 0;
    fn = function(func) {
      return setTimeout((function() {
        return requestAnimationFrame(func);
      }), timeout);
    };
    for (i in functions) {
      if (!hasProp.call(functions, i)) continue;
      fn(functions[i]);
    }
  };


  /**
   * Observe for inserted nodes using `MutationObserver` if available and `addEventListener('DOMNodeInserted', ...)` otherwise
   *
   * @param {Number}		timeout
   * @param {function[]}	callback	Will be called with either directly on inserted node(s) or on some of its parent(s) as argument
   */

  cs.observe_inserts_on = function(target, callback) {
    if (MutationObserver) {
      return (new MutationObserver(function(mutations) {
        return mutations.forEach(function(mutation) {
          if (mutation.addedNodes.length) {
            return callback(mutation.addedNodes);
          }
        });
      })).observe(target, {
        childList: true,
        subtree: true
      });
    } else {
      return target.addEventListener('DOMNodeInserted', function() {
        return callback(target);
      }, false);
    }
  };

  (function() {
    var ui;
    cs.ui = cs.ui || {};
    ui = cs.ui;

    /**
    	 * Modal dialog
    	 *
    	 * @param {HTMLElement}|{jQuery}|{String} content
        *
    	 * @return {HTMLElement}
     */
    ui.modal = function(content) {
      var modal;
      modal = document.createElement('section', 'cs-section-modal');
      if (typeof content === 'string' || content instanceof Function) {
        modal.innerHTML = content;
      } else {
        if (content instanceof jQuery) {
          content.appendTo(modal);
        } else {
          modal.appendChild(content);
        }
      }
      document.documentElement.appendChild(modal);
      return modal;
    };

    /**
    	 * Simple modal dialog that will be opened automatically and destroyed after closing
    	 *
    	 * @param {HTMLElement}|{jQuery}|{String} content
        *
    	 * @return {HTMLElement}
     */
    ui.simple_modal = function(content) {
      var modal;
      modal = ui.modal(content);
      modal.autoDestroy = true;
      modal.open();
      return modal;
    };

    /**
    	 * Alert modal
    	 *
    	 * @param {HTMLElement}|{jQuery}|{String} content
        *
    	 * @return {HTMLElement}
     */
    ui.alert = function(content) {
      var modal, ok;
      if (content instanceof Function) {
        content = content.toString();
      }
      if (typeof content === 'string' && content.indexOf('<') === -1) {
        content = "<h3>" + content + "</h3>";
      }
      modal = ui.modal(content);
      modal.autoDestroy = true;
      modal.manualClose = true;
      ok = document.createElement('button', 'cs-button');
      ok.innerHTML = 'OK';
      ok.primary = true;
      ok.action = 'close';
      ok.bind = modal;
      modal.ok = ok;
      modal.appendChild(ok);
      modal.open();
      return modal;
    };

    /**
    	 * Confirm modal
    	 *
    	 * @param {HTMLElement}|{jQuery}|{String} content
    	 * @param {Function}                      ok_callback
    	 * @param {Function}                      cancel_callback
        *
    	 * @return {HTMLElement}
     */
    ui.confirm = function(content, ok_callback, cancel_callback) {
      var cancel, modal, ok;
      if (content instanceof Function) {
        content = content.toString();
      }
      if (typeof content === 'string' && content.indexOf('<') === -1) {
        content = "<h3>" + content + "</h3>";
      }
      modal = ui.modal(content);
      modal.autoDestroy = true;
      modal.manualClose = true;
      ok = document.createElement('button', 'cs-button');
      ok.innerHTML = 'OK';
      ok.primary = true;
      ok.action = 'close';
      ok.bind = modal;
      ok.addEventListener('click', function() {
        ok_callback();
      });
      modal.ok = ok;
      modal.appendChild(ok);
      cancel = document.createElement('button', 'cs-button');
      cancel.innerHTML = L.cancel;
      cancel.action = 'close';
      cancel.bind = modal;
      if (cancel_callback) {
        cancel.addEventListener('click', function() {
          cancel_callback();
        });
      }
      modal.cancel = cancel;
      modal.appendChild(cancel);
      modal.open();
      return modal;
    };

    /**
    	 * Notify
    	 *
    	 * @param {HTMLElement}|{jQuery}|{String} content
        *
    	 * @return {HTMLElement}
     */
    return ui.notify = function() {
      var content, k, len, notify, option, options;
      content = arguments[0], options = 2 <= arguments.length ? slice.call(arguments, 1) : [];
      notify = document.createElement('cs-notify');
      if (typeof content === 'string' || content instanceof Function) {
        notify.innerHTML = content;
      } else {
        if (content instanceof jQuery) {
          content.appendTo(notify);
        } else {
          notify.appendChild(content);
        }
      }
      for (k = 0, len = options.length; k < len; k++) {
        option = options[k];
        switch (typeof option) {
          case 'string':
            notify[option] = true;
            break;
          case 'number':
            notify.timeout = option;
        }
      }
      document.documentElement.appendChild(notify);
      return notify;
    };
  })();

}).call(this);
