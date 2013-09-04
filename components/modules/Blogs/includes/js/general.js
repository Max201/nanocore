/**
 * @package		Blogs
 * @category	modules
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2013, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
$(function () {
	$('.cs-blogs-post-preview').mousedown(function () {
		blogs_post_preview($(this).data('id'))
	});
	function blogs_post_preview (id) {
		var data	= {
			'title'		: $('.cs-blogs-new-post-title').val(),
			'sections'	: $('.cs-blogs-new-post-sections').val(),
			'content'	: $('.cs-blogs-new-post-content').val(),
			'tags'		: $('.cs-blogs-new-post-tags').val()
		};
		if (id) {
			data.id	= id;
		}
		$.ajax(
			cs.base_url + '/api/Blogs/posts/preview',
			{
				cache	: false,
				data	: data,
				type	: 'post',
				success	: function (result) {
					var	preview	= $('.cs-blogs-post-preview-content');
					preview.html(result);
					$('html, body').stop().animate(
						{
							scrollTop	: preview.offset().top
						},
						500
					);
				},
				error	: function (xhr) {
					if (xhr.responseText) {
						alert(cs.json_decode(xhr.responseText).error_description);
					} else {
						alert(cs.Language.post_preview_connection_error);
					}
				}
			}
		);
	}
});