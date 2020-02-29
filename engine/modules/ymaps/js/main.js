/**!
 * DLE-YandexMaps — Бесплатный модуль Яндекс Карт для DLE
 *
 * @author     ПафНутиЙ <pafnuty10@gmail.com>
 * @link       https://git.io/v9irg
 */

var doc = $(document);
var laddaProcessObj;

doc
  .on('click', '.popup-modal-dismiss', function (e) {
    e.preventDefault();
    $.magnificPopup.close();
  })
  .on('click', '.modal-close', function () {
    $.magnificPopup.close();
  })
  .on('click', '.map-save', function (event) {
    $('#' + $(this).data('id')).trigger('submit');
  })
  .on('click', '.mfp-open-ajax-xf', function () {
    var $this    = $(this),
        itemData = $this.data(),
        $inp     = $('#' + itemData.id);
    if ($this.hasClass('disabled')) {
      return false;
    } else {
      $.magnificPopup.open({
        type: 'ajax',
        gallery: {
          enabled: false
        },
        items: {
          src: itemData.mfpSrc
        },
        ajax: {
          settings: {
            data: {
              name: $inp.val(),
              description: itemData.description,
              value: '',
              fieldType: itemData.fieldType
            }
          }
        },
        callbacks: {
          afterClose: function () {
            $('.map-save').trigger('click');
            $this.addClass('disabled');
          }
        }
      });
    }
  })
  .on('change input keyup', '.xf-input', function () {
    var $this  = $(this),
        valLen = $this.val().length;
    if (valLen > 2) {
      $this.next('span').removeClass('disabled');
    }
    if (valLen < 1) {
      $this.next('span').addClass('disabled');
    }
  })
  .on('click', '.code', function () {
    $(this).select();
  })
  // Аякс отправка формы с эффектами
  .on('submit', '[data-ajax-submit]', function () {
    var $this = $(this);

    var options = {
      dataType: 'json',
      beforeSubmit: processStartNew,
      success: processDoneNew
    };

    $this.ajaxSubmit(options);

    return false;
  })
  .on('submit', '[data-ladda-submit]', function (e) {
    e.preventDefault();

    var $this        = $(this);
    var progress     = 0;
    var laddaLoadNew = $this.find('.ladda-button').ladda();
    laddaLoadNew.ladda('start');

    var interval = setInterval(function () {
      progress = Math.min(progress + Math.random() * 0.2, 1);
      laddaLoadNew.ladda('setProgress', progress);

      if (progress === 1) {
        laddaLoadNew.ladda('stop');
        clearInterval(interval);
        $this.removeAttr('data-ladda-submit').submit();
      }
    }, 100);
  })
  .on('click', '[data-point-change]', function () {
    var itemData = $(this).data();

    $.magnificPopup.open({
      type: 'ajax',
      gallery: {
        enabled: false
      },
      items: {
        src: 'engine/ajax/controller.php'
      },
      ajax: {
        settings: {
          data: {
            id: itemData.pointChange,
            mod: 'ymaps_editmarker'
          }
        }
      },
      callbacks: {
        afterClose: function () {
          // $('.map-save').trigger('click');
          // $this.addClass('disabled');
        },
        ajaxContentAdded: function () {
          var $styler = $(this.content).find('.styler');
          if ($styler.length) {
            $markerSelect       = $('select.marker-select');
            $customMarkerSelect = $('.marker-custom-icon-select-wrapper');
            $styler.styler({
              onFormStyled: function () {
                showSelectedPoint($styler);
                if ($markerSelect.val() === 'custom') {
                  $customMarkerSelect.show();
                  showSelectedPoint($customMarkerSelect.find('select.styler'));
                }

              }
            });
          }

        }
      }
    });
  })
  .on('click', '[data-point-delete]', function () {
    var $this        = $(this);
    var itemData     = $this.data();
    var progress     = 0;
    var laddaLoadNew = $this.ladda();
    laddaLoadNew.ladda('start');

    $.ajax({
      url: 'engine/ajax/ymaps/saveconfig.php',
      type: 'POST',
      data: {
        pointID: itemData.pointDelete,
        deletePoint: 'y'
      }
    })
      .done(function () {
        console.log('done');
      })
      .fail(function () {
        console.log('error');
      }).always(function () {
      var interval = setInterval(function () {
        progress = Math.min(progress + Math.random() * 0.2, 1);
        laddaLoadNew.ladda('setProgress', progress);

        if (progress === 1) {
          laddaLoadNew.ladda('stop');
          clearInterval(interval);
          location.reload();
        }
      }, 100);
    });

    /* Act on the event */
  })
  .on('change', 'select.marker-select', function () {
    var $this               = $(this),
        $customMarkerSelect = $('.marker-custom-icon-select-wrapper');
    showSelectedPoint($this);
    if ($this.val() === 'custom') {
      $customMarkerSelect.show().find('.styler').trigger('refresh');
      showSelectedPoint($customMarkerSelect.find('select.styler'));
    } else {
      if (!$this.hasClass('marker-custom-icon-select')) {
        $customMarkerSelect.hide();
      }
    }
  })
  .on('change', 'select.marker-custom-icon-select', function () {
    // var $option = $(this).find('option:selected'),
    // 	$oData = $option.data();
    // 	console.log($oData);
  })
  .on('input keyup', '.set-icon-color-input', function () {
    var $this   = $(this),
        thisVal = $this.val();
    $('.selected-marker').css('background', thisVal);

  });

// pre-submit callback
function processStartNew(formData, jqForm) {
  laddaProcessObj = jqForm.find('.ladda-button').ladda();
  laddaProcessObj.ladda('start');

  return true;
}

// post-submit callback
function processDoneNew(responseText, statusText, xhr, $form) {

  var formContent    = $form.html();
  var responseResult = (formContent) ? formContent : responseText;

  var progress = 0;
  var interval = setInterval(function () {
    progress = Math.min(progress + Math.random() * 0.2, 1);
    laddaProcessObj.ladda('setProgress', progress);

    if (progress === 1) {
      laddaProcessObj.ladda('stop');
      laddaProcessObj = null;
      clearInterval(interval);
      // Тут что-то делаем с пришедшими данными
      if (statusText === 'success') {
        if ($form.data('ajaxSubmit') === 'reload') {
          location.reload();
        }

        if ($form.data('ajaxSubmit') !== 'noreload' && $form.data('ajaxSubmit') !== 'reload') {
          $form.html(responseResult);
        }

      }

    }

  }, 100);
}

// Как только будет загружен API и готов DOM, выполняем инициализацию
ymaps.ready(init);

// Инициализация карты.
function init() {
  var demoMap,
      myPlacemark,
      $lat  = $('#mapCenterLat'),
      $lon  = $('#mapCenterLon'),
      $zoom = $('#mapCenterZoom'),
      lat   = ($lat.val()) ? $lat.val() : '51.5350',
      lon   = ($lon.val()) ? $lon.val() : '46.0259',
      zoom  = ($zoom.val()) ? $zoom.val() : '9';
  doc
    .on('mapInit', function () {
      var mapHeightVal = $('#mapHeight').val();
      var controls     = [];
      var height       = (mapHeightVal) ? mapHeightVal : 400;
      var $map         = $('#map');

      $map.height(height);

      $.each($('input[name*="controls"]'), function (index, val) {
        var $this = $(this);
        var id    = $this.prop('id');
        if ($this.prop('checked')) {
          controls.push(id);
        }

      });
      if (!demoMap) {

        demoMap = new ymaps.Map('map', {
          center: [lat, lon], // Саратов
          zoom: zoom,
          controls: controls
        });
      }

      // Слушаем клик на карте
      demoMap.events.add('click', function (e) {
        var coords = e.get('coords');

        // Если метка уже создана – просто передвигаем ее
        if (myPlacemark) {
          myPlacemark.geometry.setCoordinates(coords);
        }
        // Если нет – создаем.
        else {
          myPlacemark = createPlacemark(coords);
          demoMap.geoObjects.add(myPlacemark);
          // Слушаем событие окончания перетаскивания на метке.
          myPlacemark.events.add('dragend', function () {
            setCenter(myPlacemark.geometry.getCoordinates(), demoMap.getZoom());
          });
        }
        setCenter(coords, demoMap.getZoom());
      });

      // Создание метки
      function createPlacemark(coords) {
        return new ymaps.Placemark(coords,
          {
            preset: 'islands#icon',
            draggable: true,
            iconColor: '#0095b6'
          });
      }

      function setCenter(coords, zoom) {
        $lat.val(coords[0].toPrecision(6));
        $lon.val(coords[1].toPrecision(6));
        $zoom.val(zoom);
      }
    })
    .on('mapDestroy', function () {
      /**/
    });
}

jQuery(document).ready(function ($) {

  // Авторазмер для блоков с кодом
  $('.code').autosize();

  // Селекты
  $('.styler').styler();

  // Прячем кнопки создания допполей
  $.each($('.xf-input'), function () {
    if ($(this).val().length) {
      $(this).next('span').addClass('disabled');
    }

  });

  // Инициализация Ladda
  var laddaLoad = $('.ladda-button-old').ladda();

  // Дефолтные настройки аякс формы
  var formOptions = {
    dataType: 'json',
    beforeSubmit: processStart,
    success: processDone
  };

  // Табы настроек
  $('#settings').easyResponsiveTabs();

  // Дефолтные настройки magnificpopup
  $.extend(true, $.magnificPopup.defaults, {
    tClose: 'Закрыть (Esc)', // Alt text on close button
    tLoading: 'Загрузка...', // Text that is displayed during loading. Can contain %curr% and %total% keys
    ajax: {
      tError: '<a href="%url%">Контент</a> не загружен.' // Error message when ajax request failed
    }
  });

  //
  $('#settingsForm').ajaxForm(formOptions);

  $('.mfp-open').magnificPopup();

  $('.mfp-open-ajax').magnificPopup({
    type: 'ajax'
  });

  $('.mfp-open-modal-map').magnificPopup({
    type: 'inline',
    preloader: false,
    // focus: '#username',
    modal: true,
    callbacks: {
      open: function () {
        doc.trigger('mapInit');
      },
      afterClose: function () {
        doc.trigger('mapDestroy');
      }
    }
  });

  /**
   * [processStart description]
   * @return {[type]} [description]
   */
  function processStart() {
    laddaLoad.ladda('start')
  }

  /**
   * [processDone description]
   * @param  {[type]} data [description]
   * @return {[type]}      [description]
   */
  function processDone(data) {
    var progress = 0;
    var interval = setInterval(function () {
      progress = Math.min(progress + Math.random() * 0.2, 1);
      laddaLoad.ladda('setProgress', progress);

      if (progress === 1) {
        laddaLoad.ladda('stop');
        clearInterval(interval);
      }
    }, 100);
  }
});

function showSelectedPoint(obj) {
  var selectedData      = obj.find('option:selected').data();
  var $marker           = $('.selected-marker');
  var $iconLayout       = $('.iconLayout');
  var $iconImageSize0   = $('.iconImageSize0');
  var $iconImageSize1   = $('.iconImageSize1');
  var $iconImageOffset0 = $('.iconImageOffset0');
  var $iconImageOffset1 = $('.iconImageOffset1');

  $marker.prop('src', selectedData.selectImg);
  $('.set-icon-color-input').remove();
  $marker.css('background', 'none');

  if (selectedData.iconColor) {
    $marker.css('background', selectedData.iconColor);
    $('.marker-select-wrapper').append('<input class="set-icon-color-input input input-block" type="text" name="iconColor" value="' + selectedData.iconColor + '">');
  }

  if (selectedData.sizeWidth > 0) {
    $iconLayout.val('default#image');
    $iconImageSize0.val(selectedData.sizeWidth);
    $iconImageSize1.val(selectedData.sizeHeight);
    $iconImageOffset0.val((selectedData.offsetWidth !== '') ? selectedData.offsetWidth : '-' + Math.round(selectedData.sizeWidth / 2));
    $iconImageOffset1.val((selectedData.offsetHeight !== '') ? selectedData.offsetHeight : '-' + Math.round(selectedData.sizeHeight / 2));
  } else {
    $iconLayout.val('');
    $iconImageSize0.val('');
    $iconImageSize1.val('');
    $iconImageOffset0.val('');
    $iconImageOffset1.val('');
  }
}
