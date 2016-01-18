/**
 * URLize string
 * @returns {string}
 */
String.prototype.urlize = function () {
    var out = '';
    var input = this.toLowerCase();
    var replacements = {
        ' ': '_',
        '=': '_',
        '&': 'and',
        '|': 'or',
        '@': 'at',
        '$': '',
        '^': '',
        '(': '',
        ')': '',
        '[': '',
        '%': '',
        '#': '',
        '!': '',
        '.': '',
        ':': '',
        '/': '',
        '\\': ''
    };

    for ( var i = 0; i < input.length; i++ ) {
        if ( typeof replacements[input[i]] != 'undefined' ) {
            out += replacements[input[i]];
            continue;
        }

        out += input[i];
    }

    return out.translit();
};

/**
 * Transliterate characters
 * @returns {string}
 */
String.prototype.translit = function () {
    var str = this;
    var func = function () {
        var typ = 1;
        var abs = Math.abs(typ);
        if (typ === abs) {
            str = str.replace(/(\u0456(?=.[^\u0430\u0435\u0438\u043E\u0443\u044A\s]+))/ig, "$1`");
            return [
                function (col, row) {
                    var chr;
                    if (chr = col[0] || col[abs]) {
                        trantab[row] = chr;
                        regarr.push(row);
                    }
                },
                function (str) {
                    return str.replace(/i``/ig, "i`").replace(/((c)z)(?=[ieyj])/ig, "$2");
                }
            ];
        } else {
            str = str.replace(/(c)(?=[ieyj])/ig, "$1z");
            return [
                function (col, row) {
                    var chr;
                    if (chr = col[0] || col[abs]) {
                        trantab[chr] = row;
                        regarr.push(chr);
                    }
                },
                function (str) {
                    return str;
                }
            ];
        }
    }();


    var iso9 = {
        "\u0449": ["", "\u015D", "", "sth", "", "shh", "shh"], // "щ"
        "\u044F": ["", "\u00E2", "ya", "ya", "", "ya", "ya"], // "я"
        "\u0454": ["", "\u00EA", "", "", "", "", "ye"], // "є"
        "\u0463": ["", "\u011B", "", "ye", "", "ye", ""], //  ять
        "\u0456": ["", "\u00EC", "i", "i`", "", "i`", "i"], // "і" йота
        "\u0457": ["", "\u00EF", "", "", "", "", "yi"], // "ї"
        "\u0451": ["", "\u00EB", "yo", "", "", "yo", ""], // "ё"
        "\u044E": ["", "\u00FB", "yu", "yu", "", "yu", "yu"], // "ю"
        "\u0436": ["zh", "\u017E"],                                // "ж"
        "\u0447": ["ch", "\u010D"],                                // "ч"
        "\u0448": ["sh", "\u0161"],                                // "ш"
        "\u0473": ["", "f\u0300", "", "fh", "", "fh", ""], //  фита
        "\u045F": ["", "d\u0302", "", "", "dh", "", ""], // "џ"
        "\u0491": ["", "g\u0300", "", "", "", "", "g`"], // "ґ"
        "\u0453": ["", "\u01F5", "", "", "g`", "", ""], // "ѓ"
        "\u0455": ["", "\u1E91", "", "", "z`", "", ""], // "ѕ"
        "\u045C": ["", "\u1E31", "", "", "k`", "", ""], // "ќ"
        "\u0459": ["", "l\u0302", "", "", "l`", "", ""], // "љ"
        "\u045A": ["", "n\u0302", "", "", "n`", "", ""], // "њ"
        "\u044D": ["", "\u00E8", "e`", "", "", "e`", ""], // "э"
        "\u044A": ["", "\u02BA", "", "a`", "", "``", ""], // "ъ"
        "\u044B": ["", "y", "y`", "", "", "y`", ""], // "ы"
        "\u045E": ["", "\u01D4", "u`", "", "", "", ""], // "ў"
        "\u046B": ["", "\u01CE", "", "o`", "", "", ""], //  юс
        "\u0475": ["", "\u1EF3", "", "yh", "", "yh", ""], //  ижица
        "\u0446": ["cz", "c"],                                // "ц"
        "\u0430": ["a"],                                          // "а"
        "\u0431": ["b"],                                          // "б"
        "\u0432": ["v"],                                          // "в"
        "\u0433": ["g"],                                          // "г"
        "\u0434": ["d"],                                          // "д"
        "\u0435": ["e"],                                          // "е"
        "\u0437": ["z"],                                          // "з"
        "\u0438": ["", "i", "", "i", "i", "i", "y`"], // "и"
        "\u0439": ["", "j", "j", "j", "", "j", "j"], // "й"
        "\u043A": ["k"],                                          // "к"
        "\u043B": ["l"],                                          // "л"
        "\u043C": ["m"],                                          // "м"
        "\u043D": ["n"],                                          // "н"
        "\u043E": ["o"],                                          // "о"
        "\u043F": ["p"],                                          // "п"
        "\u0440": ["r"],                                          // "р"
        "\u0441": ["s"],                                          // "с"
        "\u0442": ["t"],                                          // "т"
        "\u0443": ["u"],                                          // "у"
        "\u0444": ["f"],                                          // "ф"
        "\u0445": ["x", "h"],                                // "х"
        "\u044C": ["", "\u02B9", "`", "`", "", "`", "`"], // "ь"
        "\u0458": ["", "j\u030C", "", "", "j", "", ""], // "ј"
        "\u2019": ["'", "\u02BC"],                                // "’"
        "\u2116": ["#"]                                           // "№"
    }, regarr = [], trantab = {};
    for (var row in iso9) {
        func[0](iso9[row], row);
    }

    return func[1](
        str.replace(
            new RegExp(regarr.join("|"), "gi"),
            function (R) {
                if ( R.toLowerCase() === R) {
                    return trantab[R];
                } else {
                    return trantab[R.toLowerCase()].toUpperCase();
                }
            }
        )
    );
};


/**
 * Events
 */
$(function(){
    $('[data-url-for]').on('input', function(){$($(this).attr('data-url-for')).val($(this).val().urlize())});
    $('[data-url-for]').each(function(i, e){
        $($(e).attr('data-url-for')).val($(e).val().urlize());
    })
});