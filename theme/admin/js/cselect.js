$(function(){
    $.fn.cselect = function(options)
    {
        var $opt = $.extend({
            'item': function (value, label, selected) {
                selected = typeof selected === 'undefined' ? '' : selected;
                return '<li class="' + selected + '" data-value="' + value + '">' + label + '</li>';
            },
            'width': 0
        }, options);

        var $update = function(e) {
            var $li = $(e);
            var $ul = $li.parent();
            var new_val = $li.attr('data-value');
            var new_lablel = $li.text();

            // Updating select
            var $select = $('#' + $ul.attr('data-select'));
            $select.find('option[selected]').removeAttr('selected');
            $select.find('option[value="' + new_val + '"]').attr('selected', 'selected');

            // Updating custom select
            $ul.find('li.selected').removeClass('selected');
            $ul.find('li[data-value="' + new_val + '"]').addClass('selected');

            // Updating label
            $ul.parent().find('label').text($select.find('option[selected]').text());

            return e;
        };


        var $render = function(e) {
            // Get the select box
            var $select = $(e).find('select');

            // Generate select id
            var $sid = $select.attr('id');
            var $start_width = ($select.attr('data-width') ? $select.attr('data-width') : $select.width() + 56);

            // Hide select
            $select.css('display', 'none');

            // Override select box wrapper
            $(e).append('<div style="width: ' + $start_width + 'px;" data-input="' + $sid + '"></div>');
            var $overw = $(e).find('div[data-input="' + $sid + '"]'); // Wrapper

            // Override select box label
            $overw.append('<label for="' + $sid + '"></label>');
            var $overl = $overw.find('label');

            // Override select box options dropdown
            $overw.append('<ul style="width: ' + $start_width + 'px;" class="custom-select" data-select="' + $sid + '"></ul>');
            var $overd = $('[data-select="' + $sid + '"]'); // Select

            // Render options
            $.each($select.find('optgroup, option'), function(i, option){
                // Render group
                if ( $(option)[0].tagName.toLowerCase() == 'optgroup' ) {
                    $overd.append('<li class="group">' + $(option).attr('label') + '</li>');
                }

                // Render option
                if ( $(option)[0].tagName.toLowerCase() == 'option' ) {
                    var selected = '';
                    if ( $(option).is('[selected]') ) {
                        $overl.text($(option).text());
                        selected = 'selected'
                    }

                    $overd.append($opt.item($(option).attr('value'), $(option).text(), selected));
                    $('li[data-value="' + $(option).attr('value') + '"]').on('click', function(){
                        $update(this);
                    });
                }
            });

            // If no label
            if ( !$overl.text().length ) {
                $overl.text('Empty');
            }

            return e;
        };

        return $.each($(this), function(i, e){
            return $render(e);
        });
    };

    $('.cselect').cselect();
});