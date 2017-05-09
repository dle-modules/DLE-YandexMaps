/**!
 * DLE-YandexMaps — Бесплатный модуль Яндекс Карт для DLE
 *
 * @author     ПафНутиЙ <pafnuty10@gmail.com>
 * @link       https://git.io/v9irg
 */

var doc = $(document);

doc
	.on('click', '.popup-modal-dismiss', function (e) {
		e.preventDefault();
		$.magnificPopup.close();
	})
	.on('click', '.btn-editmap-save', function (e) {
		if (coordsFieldText) {
			pointInput.val(coordsFieldText);
			hideCoordsField();
		}

	});
jQuery(document).ready(function ($) {
	$.ajax({
		url: 'engine/ajax/ymaps/editnews.php'
	})
		.done(function (data) {
			$('#addnews').append(data);
			hideCoordsField();
			$.extend(true, $.magnificPopup.defaults, {
				tClose: 'Закрыть (Esc)',
				tLoading: 'Загрузка...'
			});

			$('.btn-editmap-modal').magnificPopup({
				type: 'inline',
				preloader: false,
				modal: true,
				callbacks: {
					open: function () {
						doc.trigger('editMapInit');
					}
				}
			});

		})
		.fail(function () {
			console.log("error");
		});
});
