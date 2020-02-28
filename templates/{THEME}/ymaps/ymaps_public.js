/**!
 * DLE-YandexMaps — Бесплатный модуль Яндекс Карт для DLE
 *
 * @author     ПафНутиЙ <pafnuty10@gmail.com>
 * @link       https://git.io/v9irg
 */

var doc = $(document);

doc
	.on('click', '.modal-close, .btn-close', function() {
		$.magnificPopup.close();
	});

jQuery(document).ready(function($) {

	// Дефолтные настройки magnificpopup
	$.extend(true, $.magnificPopup.defaults, {
		tClose: 'Закрыть (Esc)', // Alt text on close button
		tLoading: 'Загрузка...', // Text that is displayed during loading. Can contain %curr% and %total% keys
		ajax: {
			tError: '<a href="%url%">Контент</a> не загружен.' // Error message when ajax request failed
		}
	});

	$('[data-mfp-src]').magnificPopup({
		type: 'inline',
		preloader: true,
		modal: true,
		callbacks: {
			open : function () {
				doc.trigger('mapInit');
			}
		}
	});

}); //ready
