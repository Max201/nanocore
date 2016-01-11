/**
 * Copyright by NanoLab
 * @website www.nanolab.pw
 */

var WyEdit = function ($field, use_fm) {
    if ( use_fm ) {
        _wyi_name = use_fm;
    }

    $field.attr('contenteditable', true);
    this.field = $field;
};

/**
 * Get editor content
 * @returns {*}
 */
WyEdit.prototype.content = function() {
    this.field.focus();
    return this.field.html();
};

/**
 * Copies the current selection to the clipboard
 * @returns {boolean}
 */
WyEdit.prototype.copy = function() {
    this.field.focus();
    return document.execCommand('copy');
};

/**
 * Makes text bold
 * @returns {boolean}
 */
WyEdit.prototype.bold = function() {
    this.field.focus();
    return document.execCommand('bold');
};

/**
 * Makes text italic
 * @returns {boolean}
 */
WyEdit.prototype.italic = function() {
    this.field.focus();
    return document.execCommand('italic');
};

/**
 * Makes text underline
 * @returns {boolean}
 */
WyEdit.prototype.undeline = function() {
    this.field.focus();
    return document.execCommand('underline');
};

/**
 * Sets text background color
 * @returns {boolean}
 */
WyEdit.prototype.bgcolor = function(color) {
    this.field.focus();
    return document.execCommand('backColor', false, color);
};

/**
 * Sets text background color
 * @returns {boolean}
 */
WyEdit.prototype.color = function(color) {
    this.field.focus();
    return document.execCommand('foreColor', false, color);
};

/**
 * Sets text color
 * @returns {boolean}
 */
WyEdit.prototype.link = function(url) {
    this.field.focus();
    return document.execCommand('createLink', false, url);
};

/**
 * Delete link
 * @returns {boolean}
 */
WyEdit.prototype.unlink = function() {
    this.field.focus();
    return document.execCommand('unlink');
};

/**
 * Insert image
 * @returns {boolean}
 */
WyEdit.prototype.image = function(src) {
    this.field.focus();
    return document.execCommand('insertImage', false, src);
};

/**
 * Justify content
 * @returns {boolean}
 */
WyEdit.prototype.justify = function(by) {
    this.field.focus();
    by = by[0].toUpperCase() + by.substr(1);
    return document.execCommand('justify' + by);
};

/**
 * Heading selected string
 * @returns {boolean}
 */
WyEdit.prototype.heading = function(hnum) {
    this.field.focus();
    return document.execCommand('formatBlock', false, '<H' + hnum + '>');
};

