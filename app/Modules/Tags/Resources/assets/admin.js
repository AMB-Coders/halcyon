/* global $ */ // jquery.js
/* global jQuery */ // jquery.js
/* global Halcyon */ // core.js

document.addEventListener('DOMContentLoaded', function () {

	document.querySelectorAll('.sluggable').forEach(function(el){
		el.addEventListener('keyup', function () {
			if (this.getAttribute('data-rel')) {
				var alias = document.querySelector(this.getAttribute('data-rel'));

				var val = this.value;
				val = val.toLowerCase()
					.replace(/\s+/g, '_')
					.replace(/[^a-z0-9_]+/g, '');

				alias.value = val;
			}
		});
	});

	document.querySelectorAll('.alias-add').forEach(function (el) {
		el.addEventListener('click', function (e) {
			e.preventDefault();

			var name = document.querySelector(this.getAttribute('href'));
			var btn = this;

			// create new relationship
			$.ajax({
				url: btn.getAttribute('data-api'),
				type: 'post',
				data: {
					'parent_id': btn.getAttribute('data-id'),
					'name': name.value
				},
				dataType: 'json',
				async: false,
				success: function (response) {
					Halcyon.message('success', btn.getAttribute('data-success'));

					var c = $(name).closest('table');
					var li = c.find('tr.hidden');

					if (typeof (li) !== 'undefined') {
						var template = $(li)
							.clone()
							.removeClass('hidden');

						template
							.attr('id', template.attr('id').replace(/\{id\}/g, response.id))
							.data('id', response.id);

						template.find('a').each(function (i, el) {
							$(el).attr('data-api', $(el).attr('data-api').replace(/\{id\}/g, response.id));
						});

						var content = template
							.html()
							.replace(/\{id\}/g, response.id)
							.replace(/\{name\}/g, response.name)
							.replace(/\{slug\}/g, response.slug);

						template.html(content).insertBefore(li);
					}

					name.value = '';
				},
				error: function (xhr) { //xhr, ajaxOptions, thrownError
					Halcyon.message('danger', xhr.responseJSON.message);
				}
			});
		});
	});

	document.querySelector('#main').addEventListener('click', (e) => {
		if (!e.target.parentNode.matches('.remove-alias')) {
			return;
		}

		e.preventDefault();

		var btn = e.target.parentNode;
		var result = confirm(btn.getAttribute('data-confirm'));

		if (result) {
			var field = document.querySelector(btn.getAttribute('href'));

			// delete relationship
			$.ajax({
				url: btn.getAttribute('data-api'),
				type: 'delete',
				dataType: 'json',
				async: false,
				success: function () {
					Halcyon.message('success', btn.getAttribute('data-success'));
					field.remove();
				},
				error: function (xhr) {
					Halcyon.message('danger', xhr.responseJSON.message);
				}
			});
		}
	});
});
