###*
 * @package   CleverStyle Widgets
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2014-2015, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
###
Polymer(
	'is'		: 'cs-button'
	'extends'	: 'button'
	properties	:
		active	:
			reflectToAttribute	: true
			type				: Boolean
		empty	:
			reflectToAttribute	: true
			type				: Boolean
		icon	: String
	ready : ->
		if !@childNodes.length
			@empty = true
)