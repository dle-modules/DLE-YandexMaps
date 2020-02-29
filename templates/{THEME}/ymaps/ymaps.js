/* eslint-disable no-undef */
/* eslint-env es5 */
/** !
 * DLE-YandexMaps — Бесплатный модуль Яндекс Карт для DLE
 *
 * @author     ПафНутиЙ <pafnuty10@gmail.com>
 * @link       https://git.io/v9irg
 */

/**
 * @typedef {object} mapConfig
 *
 * @property {string} mapSelector
 * @property {boolean} isInline
 * @property {string} mapUrl
 * @property {array} controls
 * @property {number} height
 * @property {object} defaultPos
 * @property {string} defaultPos.lat
 * @property {string} defaultPos.lon
 * @property {string} defaultPos.zoom
 * @property {object} arPlacemarkStyles
 * @property {string} inputSelector
 * @property {string} xfHolder
 */

/**
 * Функция, запускающая скрипт карты. вызывается из php файла
 * @param {mapConfig} mapConfig
 */
// eslint-disable-next-line no-unused-vars
var addNewsMapStart = function(mapConfig) {
  var script = document.createElement('script');
  script.src = mapConfig.mapUrl;
  script.onload = function() {
    ymapsStart(mapConfig);
  };
  document.getElementsByTagName('head')[0].appendChild(script);
};

/**
 *
 * @param {mapConfig} mapConfig
 */
var ymapsStart = function(mapConfig) {
  var addnewsMap, myPlacemark;
  var methods = {
    toggleXFieldAndBtn: function() {
      var $btn = $('.btn-addmap');
      if ($(mapConfig.xfHolder).css('display') !== 'none') {
        $(mapConfig.xfHolder).hide();
        $btn.show();
      } else {
        $btn.hide();
      }
    },

    /**
     *
     * @returns {{lat: string, lon: string, zoom: string}|any | jQuery | null | undefined}
     */
    getCoordsFromInput: function() {
      var pointInput = $(mapConfig.inputSelector);
      if (pointInput.val()) {
        return $.parseJSON(pointInput.val());
      } else {
        return mapConfig.defaultPos;
      }
    },

    /**
     *
     * @param coords
     * @param zoom
     */
    setCenter: function(coords, zoom) {
      if (coords && coords.length && zoom) {
        var inpText =
          '{"lat":"' +
          coords[0].toPrecision(6) +
          '", "lon" : "' +
          coords[1].toPrecision(6) +
          '", "zoom": "' +
          zoom +
          '"}';
        $(mapConfig.inputSelector).val(inpText);
      }
    },

    /**
     *
     * @returns {Array}
     */
    getDataFromInput: function() {
      var returnValue = [];
      var inputValue = $(mapConfig.inputSelector).val();
      if (inputValue) {
        var inputCoords = JSON.parse(inputValue);
        returnValue = [inputCoords.lat * 1, inputCoords.lon * 1];
      }
      return returnValue;
    },

    resetCoords: function() {
      $(mapConfig.inputSelector).val('');
    },

    /**
     *
     * @param coords
     * @returns {object} ymaps.Placemark
     */
    createPlacemark: function(coords) {
      var $catList = $('[name="catlist[]"]');
      var categoryId = 0;
      if ($catList.length) {
        categoryId = $catList.find('option:selected').val() || $catList.val();
      }
      var key = categoryId > 0 ? categoryId : 'default';
      var placemarkStyle = mapConfig.arPlacemarkStyles;

      return new ymaps.Placemark(coords, {}, placemarkStyle[key]);
    },

    /**
     *
     * @param res
     */
    addOrMovePlacemark: function(res) {
      // Пробуем получить метку из геометрии переданной точки (если точка передана из поиска)
      var coords = res.geometry && res.geometry.getCoordinates();

      // Или пробуем достать её из текущих координат (если это клик по карте)
      if (!coords) {
        coords = res.get('coords');
      }
      // Если метка уже создана – просто передвигаем ее
      if (myPlacemark) {
        myPlacemark.geometry.setCoordinates(coords);
      } else {
        // Если нет – создаем.
        myPlacemark = methods.createPlacemark(coords);
        addnewsMap.geoObjects.add(myPlacemark);

        // Слушаем событие окончания перетаскивания на метке.
        myPlacemark.events.add('dragend', function() {
          methods.setCenter(myPlacemark.geometry.getCoordinates(), addnewsMap.getZoom());
        });
      }
      methods.setCenter(myPlacemark.geometry.getCoordinates(), addnewsMap.getZoom());
    }
  };
  var doc = $(document);

  doc.on('change', '#category', methods.toggleXFieldAndBtn).on('click', '.add-map-clear', methods.resetCoords);

  // Инициализация карты.
  ymaps.ready(addMapInit);

  function addMapInit() {
    // Скрываем поле с координатами карты, оно не нужно пользователю.
    methods.toggleXFieldAndBtn();
    // Убираем блокировку с кнопки открытия модального окна
    $('.btn-addmap').prop('disabled', false);

    doc.on('mapInit', function() {
      var $map = $('#' + mapConfig.mapSelector);
      var coors = methods.getCoordsFromInput();
      var lat = coors.lat;
      var lon = coors.lon;
      var zoom = coors.zoom;

      // На всякий случай устанавливаем высоту карты
      $map.height(mapConfig.height);

      if (addnewsMap) {
        myPlacemark = false;
        addnewsMap.events.remove(['click', 'boundschange', 'dragend', 'resultselect', 'submit']);
        addnewsMap.destroy();
      }

      addnewsMap = new ymaps.Map(mapConfig.mapSelector, {
        center: [lat, lon], // Саратов
        zoom: zoom,
        controls: mapConfig.controls
      });

      var searchControl = addnewsMap.controls.get('searchControl');

      if (searchControl) {
        searchControl.options.set('noPlacemark', true);
        searchControl.events.add('resultselect', function(e) {
          var index = e.get('index');
          searchControl.getResult(index).then(methods.addOrMovePlacemark);
        });
      }

      var dataFromInput = methods.getDataFromInput();

      if (dataFromInput) {
        myPlacemark = methods.createPlacemark(dataFromInput);
        addnewsMap.geoObjects.add(myPlacemark);
      }

      // Слушаем клик на карте
      addnewsMap.events.add('click', methods.addOrMovePlacemark).add('boundschange', function() {
        var coords = [lat * 1, lon * 1];

        if (myPlacemark) {
          coords = myPlacemark.geometry.getCoordinates();
        }
        methods.setCenter(coords, addnewsMap.getZoom());
      });
    });

    if (mapConfig.isInline) {
      doc.trigger('mapInit');
    }

    if ($.magnificPopup) {
      // Дефолтные настройки magnificpopup
      $.extend(true, $.magnificPopup.defaults, {
        tClose: 'Закрыть (Esc)',
        tLoading: 'Загрузка...'
      });

      $('[data-mfp-src]').magnificPopup({
        type: 'inline',
        preloader: false,
        // focus: '#username',
        modal: true,
        callbacks: {
          open: function() {
            doc.trigger('mapInit');
          }
        }
      });
    }
  }
};
