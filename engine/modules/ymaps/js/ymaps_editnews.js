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
 * @property {array} scripts
 * @property {array} controls
 * @property {number} height
 * @property {object} defaultPos
 * @property {string} defaultPos.lat
 * @property {string} defaultPos.lon
 * @property {string} defaultPos.zoom
 * @property {object} arPlacemarkStyles
 * @property {string} inputSelector
 * @property {string} xfHolder
 * @property {string} modalHtml
 * @property {string} btnHtml
 * @property {object} text
 * @property {string} text.edit
 * @property {string} text.add
 */
var doc = $(document);

doc
  .on('dialogopen', '#dlepopup-news-edit', function() {
    setTimeout(
      loadController
      , 500);
  })
  .on('click', '.popup-modal-dismiss', function(e) {
    e.preventDefault();
    $.magnificPopup.close();
  });

var loadController = function () {
  // eslint-disable-next-line camelcase
  var dleRoot = window.dle_root || '';
  $.ajax({
    url: dleRoot + 'engine/ajax/controller.php',
    data: {
      mod: 'ymaps',
      type: 'editnews'
    }
  })
    .done(function(data) {
      runQuickEdit(data);
    })
    .fail(function() {
      console.error('error');
    });
};

var loadScript = function(url, callback) {
  var script = document.createElement('script');
  script.src = url;
  if (callback) {
    script.onload = callback;
  }
  document.getElementsByTagName('head')[0].appendChild(script);
};

var runQuickEdit = function(mapConfig) {
  if ($(mapConfig.inputSelector).length) {
    mapConfig.scripts.forEach(function(scriptUrl) {
      loadScript(scriptUrl);
    });

    if (window.ymaps) {
      ymapsStart(mapConfig);
    } else {
      loadScript(mapConfig.mapUrl, function () {
        ymapsStart(mapConfig);
      });
    }
  }
};

/**
 *
 * @param {mapConfig} mapConfig
 */
var ymapsStart = function(mapConfig) {
  var $xfieldInput = $(mapConfig.inputSelector);

  var $btnHtml = $(mapConfig.btnHtml);

  $xfieldInput
    .parent()
    .append($btnHtml)
    .append($(mapConfig.modalHtml));

  // $btnHtml.insertAfter($xfieldInput);

  var $editMapBtn = $('.btn-editmap-modal');

  var editnewsMap = window.editnewsMap;
  var myPlacemark;

  var methods = {
    checkBtnText: function() {
      var $this = $(mapConfig.inputSelector);
      if ($this.val().length) {
        $editMapBtn.text(mapConfig.text.edit);
      } else {
        $editMapBtn.text(mapConfig.text.add);
      }
    },
    toggleXFieldAndBtn: function() {
      var $xfHolder = $(mapConfig.xfHolder);
      if ($xfHolder.length) {
        if ($xfHolder.css('display') !== 'none') {
          $xfieldInput.hide();
        }
      } else {
        if ($xfieldInput.css('display') !== 'none') {
          $xfieldInput.hide();
          $btnHtml.show();
        } else {
          $btnHtml.hide();
        }
      }
    },

    /**
     * @returns {{lat: string, lon: string, zoom: string}|any | jQuery | null | undefined}
     */
    getCoordsFromInput: function() {
      var pointInput = $xfieldInput;
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
        $xfieldInput.val(inpText);
      }
      methods.checkBtnText();
    },

    /**
     *
     * @returns {Array}
     */
    getDataFromInput: function() {
      var returnValue = [];
      var inputValue = $xfieldInput.val();
      if (inputValue) {
        var inputCoords = JSON.parse(inputValue);
        returnValue = [inputCoords.lat * 1, inputCoords.lon * 1];
      }
      return returnValue;
    },

    resetCoords: function() {
      $xfieldInput.val('');
      methods.checkBtnText();
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
        editnewsMap.geoObjects.add(myPlacemark);

        // Слушаем событие окончания перетаскивания на метке.
        myPlacemark.events.add('dragend', function() {
          methods.setCenter(myPlacemark.geometry.getCoordinates(), editnewsMap.getZoom());
        });
      }
      methods.setCenter(myPlacemark.geometry.getCoordinates(), editnewsMap.getZoom());
    }
  };

  doc
    .off('.ymapsEdit')
    .on('change.ymapsEdit', '#category, [name="category[]"]', methods.toggleXFieldAndBtn)
    .on('change.ymapsEdit', mapConfig.inputSelector, methods.checkBtnText)
    .on('click.ymapsEdit', '.btn-clear-placemark', methods.resetCoords);

  // Инициализация карты.
  ymaps.ready(addMapInit);

  function addMapInit() {
    // Скрываем поле с координатами карты, оно не нужно пользователю.
    methods.toggleXFieldAndBtn();
    methods.checkBtnText();

    // Убираем блокировку с кнопки открытия модального окна
    $editMapBtn.prop('disabled', false);

    doc
      .on('mapInit.ymapsEdit', function() {
        var $map = $('#' + mapConfig.mapSelector);
        var coors = methods.getCoordsFromInput();
        var lat = coors.lat;
        var lon = coors.lon;
        var zoom = coors.zoom;

        // На всякий случай устанавливаем высоту карты
        $map.height(mapConfig.height);

        if (editnewsMap) {
          myPlacemark = false;
          editnewsMap.events.remove(['click', 'boundschange', 'dragend', 'resultselect', 'submit']);
          editnewsMap.destroy();
        }

        editnewsMap = new ymaps.Map(mapConfig.mapSelector, {
          center: [lat, lon], // Саратов
          zoom: zoom,
          controls: mapConfig.controls
        });

        var searchControl = editnewsMap.controls.get('searchControl');

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
          editnewsMap.geoObjects.add(myPlacemark);
        }

        // Слушаем клик на карте
        editnewsMap.events.add('click', methods.addOrMovePlacemark).add('boundschange', function() {
          var coords = [lat * 1, lon * 1];

          if (myPlacemark) {
            coords = myPlacemark.geometry.getCoordinates();
          }
          methods.setCenter(coords, editnewsMap.getZoom());
        });
      })
      .on('click.ymapsEdit', '.btn-editmap-modal', function() {
        if ($.magnificPopup) {
          // Дефолтные настройки magnificpopup
          $.extend(true, $.magnificPopup.defaults, {
            tClose: 'Закрыть (Esc)',
            tLoading: 'Загрузка...'
          });

          $.magnificPopup.open({
            type: 'inline',
            items: {
              src: '#' + mapConfig.mapSelector + '-modal'
            },
            preloader: false,
            modal: true,
            callbacks: {
              open: function() {
                doc.trigger('mapInit');
              }
            }
          });
        }
      });
  }
};
