###*
 * @package    CleverStyle CMS
 * @category   modules
 * @author     Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright  Copyright (c) 2011-2015, Nazar Mokrynskyi
 * @license    MIT License, see license.txt
###
L							= cs.Language
###*
 * Get value by name
 *
 * @param {string}	name
 *
 * @return {string}
###
value_by_name				= (name) ->
	document.getElementsByName(name).item(0).value
###*
 * Cache cleaning
 *
 * @param 			element
 * @param {string}	action
###
cs.admin_cache				= (element, action, partial_path) ->
	$(element).html """
		<progress is="cs-progress" infinite></progress>
	"""
	$.ajax
		url		: action
		data	:
			partial_path	: partial_path
		type	: 'delete'
		success	: (result) ->
			$(element).html(
				if result
					"""<p class="cs-block-success cs-text-success">#{L.done}</p>"""
				else
					"""<p class="cs-block-errorcs-text-error">#{L.error}</p>"""
			)
	return
###*
 * Send request for db connection testing
 *
 * @param {int}	index
 * @param {int}	mirror_index
###
cs.db_test					= (index, mirror_index) ->
	modal	= $(cs.ui.simple_modal("""<div>
		<h3 class="cs-text-center">#{L.test_connection}</h3>
		<progress is="cs-progress" infinite></progress>
	</div>"""))
	$.ajax(
		url		: 'api/System/admin/databases_test'
		data	:
			if index != undefined
				index			: index
				mirror_index	: mirror_index
			else
				db	:
					type		: value_by_name('db[type]')
					name		: value_by_name('db[name]')
					user		: value_by_name('db[user]')
					password	: value_by_name('db[password]')
					host		: value_by_name('db[host]')
					charset		: value_by_name('db[charset]')
		type	: 'get'
		success	: (result) ->
			if result
				status = 'success'
			else
				status = 'error'
			result = if result then L.success else L.failed
			modal
				.find('progress')
				.replaceWith("""<p class="cs-text-center cs-block-#{status} cs-text-#{status}" style=text-transform:capitalize;">#{result}</p>""")
		error	: ->
			modal
				.find('progress')
				.replaceWith("""<p class="cs-text-center cs-block-error cs-text-error" style=text-transform:capitalize;">#{L.failed}</p>""")
	)
###*
 * Send request for storage connection testing
 *
 * @param {int}	index
###
cs.storage_test				= (index) ->
	modal	= $(cs.ui.simple_modal("""<div>
		<h3 class="cs-text-center">#{L.test_connection}</h3>
		<progress is="cs-progress" infinite></progress>
	</div>"""))
	$.ajax(
		url		: 'api/System/admin/storages_test'
		data	:
			if index != undefined
				index	: index
			else
				storage	:
					url			: value_by_name('storage[url]')
					host		: value_by_name('storage[host]')
					connection	: value_by_name('storage[connection]')
					user		: value_by_name('storage[user]')
					password	: value_by_name('storage[password]')
		type	: 'get'
		success	: (result) ->
			if result
				status = 'success'
			else
				status = 'error'
			result = if result then L.success else L.failed
			modal
				.find('progress')
				.replaceWith("""<p class="cs-text-center cs-block-#{status} cs-text-#{status}" style=text-transform:capitalize;">#{result}</p>""")
		error	: ->
			modal
				.find('progress')
				.replaceWith("""<p class="cs-text-center cs-block-error cs-text-error" style=text-transform:capitalize;">#{L.failed}</p>""")
	)
cs.test_email_sending		= () ->
	email = prompt(L.email)
	if email
		$.ajax(
			url		: 'api/System/admin/email_sending_test'
			data	:
				email	: email
			type	: 'get'
			success	: ->
				alert(L.done)
			error	: ->
				alert(L.test_email_sending_failed)
		)
