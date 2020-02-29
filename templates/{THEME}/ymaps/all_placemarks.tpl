{*
  Этот файл следует подключать в другие шаблоны вот так:

  include file="ymaps/all_placemarks.tpl"

*}


{* При необходимости переместить стили в CSS файл *}
<style>
  #allPlacemarks.loading {
    position: relative;
  }
  #allPlacemarks.loading:after {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    z-index: 150;
    background: rgba(255,255,255,.8) url('data:image/GIF;base64,R0lGODlhFAAUAPQSAOzu7Pz6/PT29Nza3PTy9OTm5Pz+/Ozq7MTGxKyqrMTCxOTi5Nze3MzKzLy+vLSytNTS1NTW1Ly6vMzOzLS2tKyurJSSlJyenJSWlJyanKSipKSmpP///wAAAAAAAAAAACH/C05FVFNDQVBFMi4wAwEAAAAh+QQJBQASACwAAAAAFAAUAAAFZaAkisL4KGOqSssTiI+zzsEDwfK8MkkZ6yLAS2JwICQnoAGiWBgkh8ThpywgGgdJBHAAAEeBgSLy/K4EBbPoUGCzh+a2/ABXg+tqg4AgKJsNAXwBfl96fIRqgyKIdiOMjZCRkikhACH5BAkFAAkALAAAAAAQABAAAAVcYCKKwahMYyoWTZmc5HOkBsKIcDJhgnooPZgAAxEJXIZJ5IVybFwLBsCQIDgIp4NlMTIABgtCYgEAECYUVSJQGBSoXTUJIDcGjkeXHI+H1/9/fn5qg4OBgHGICSEAIfkECQUADgAsAQAAABIACwAABVigIzrBCA1jmhKMIZ6ioQCqYzDHizpRVdaEQQkmqOxEAZdtUXDAGhQloSAgCFyCgeAESDQDCIvEELCWDoL0AGGIZC4DpaF6TQUSmMbPvjcRaiNKgIOCgw4hACH5BAkFABEALAEAAAATAAsAAAVTYCRGxrgcY6oGRBkVKAkRqmgQgnjqSlCLrNJOgFjUXAZBbjdouAiAVIkVgAEU0QAkgSCpBAFwAWJgPB4Ml9cmlSTIvzgjF48fJHi8oz4q5CUPDiEAIfkECQUADgAsBAAAABAAEAAABVigYzikIJBoSo4OcYrLu6YmCTCBmrpOMBA6lqN2YLAEQJXLBwwwFJFgALAwFBqIgzA4URh1ukIOrANgzw2yw4xAqyFJ9SphQYzlhsjlMtiSAwgWEnI7BQ4hACH5BAkFAAgALAsAAQAJABIAAAVTIIIYQiCeSEAY6EgILSIIbAqrpnEMhW0QDMZKZFgMDrUTABYjRAbPp8gJfQ5EwljAkYCYRJBH8fFgsA4WhgySaIgcmprgIBJgIDHEBPNFGR50JyEAIfkECQUACwAsCQABAAsAEwAABVTgIi7GaJ7lqa6kaATrSwhoQMypewu5GfQnwaEwHJqExKSpQFsFJgoGTHQADBCGAwJxKD0UgERhEWAoIovvYkJJBQBpxUJQGZzUi0hlKsIbwCZ4KyEAIfkECQUADgAsBAAFABAADwAABVigI46kY5SoeI5r2rZpLDtBINi2jN+CIAOBGMywGBxgpgQDQFhEDAQGg9CKYAiKCRZAOwwKogCm4cg6Bo1VrnwJmgOKRQlgGYjMjgUiOFpQVngGEAQyeCkhACH5BAkFAAcALAAABgATAA4AAAVd4CGO5GiQZ6mubOu+MAwMqpCWgmNR92EQBJsJgtEwAo5FELAwBIABgyGBmQQOjIQAMggMCD5B8FAQiAIVGpfcFDlLk8d1bRgAWITEQhShHQBHKwUIKWs+C2YvhiohACH5BAkFAAwALAAACQATAAsAAAVUoCRRIkUwaKqi5Ciea7wCi2wHU4IYKo/6DMOgQingDgFBgAAMGhyVQYBRQAQWSMK0eRCgAojaosAQCJqrQWNaOAS1NoGCTHWXmTJAxNf+KW0qWDYhACH5BAkFAAkALAAABAATABAAAAVhYLIISWmeaGJU2BSkcGlA2cXEsaBYlIHDgMGv1EAUi6ShcdlIDk+Ew9MUGCgiPlMWZVggEIAuwfBSnQwzRcEXDAicKUA5wAAkCM5t6sDw4c04AgMEJW9nMAJrJX+AT4YwIQAh+QQFBQAKACwAAAAADgATAAAFX6AiisJontEVnGyQISw7WER8lIqRUKzhVBGD4mBZ9AYVirFxiCkCkwRC6BQRjKzIQBuJrLLbMK76BJADhUGBejIAGAuCQfAVGQwLxkEYkLPodgRjTgJ+VQYEdU4BgyYhADs=') no-repeat 50% 50%;
  }
</style>

{* Если скрипт я.карт уже есть на странице, это подключение не нужно *}
<script src="//api-maps.yandex.ru/2.1/?lang=ru_RU"></script>
<script>
  ymaps.ready(allPlacemarks);
  function allPlacemarks() {
    var myMap = new ymaps.Map('allPlacemarks', { // ID блока с картой
          center: [25.0088, 54.9854], // координаты центра карты
          zoom: 12 // зум карты
        }),
        objectManager = new ymaps.ObjectManager({
          // Чтобы метки начали кластеризоваться, выставляем опцию.
          clusterize: true,
          // ObjectManager принимает те же опции, что и кластеризатор.
          gridSize: 32
        });

    // Чтобы задать опции одиночным кластерам,
    // обратимся к дочерним коллекциям ObjectManager.
    // Установим дизайн метки по умолчанию:
    objectManager.clusters.options.set('preset', 'islands#blueClusterIcons');

    myMap.geoObjects.add(objectManager);

    // Выполняем ajax-запрос к мини-модулю для вывода всех меток
    // Модуль кеширует данные, так что тормоза будут только при первом вызове,

    $.ajax({
      url: dle_root + 'engine/ajax/controller.php',
      dataType: 'json',
      data: {
        mod: 'ymaps_all',
        // вместо allplacemarks можно прописать имя другого шаблона, содержащего в себе конфигурацию для вывода меток
        // сам шаблон с конфигом находится в папке ymaps/all
        preset: 'allplacemarks'
      }
    })
      .done(function (data) {
        // Если всё ок - добавим метки на карту
        objectManager.add(data);
        // Теперь надо бы найти максимальные и минимальные координаты точек
        // Для этого определим изначальные "координаты"
        var maxLat = 0,
            maxLon = 0,
            minLat = 100,
            minLon = 100;
        // пройдёмся в цикле по всем координатам
        objectManager.objects.each(function (object) {
          var lat = object.geometry.coordinates[0],
              lon = object.geometry.coordinates[1];
          // Определим максимальные и минимальные результаты
          maxLat = (lat <= maxLat) ? maxLat : lat;
          maxLon = (lon <= maxLon) ? maxLon : lon;
          minLat = (lat >= minLat) ? minLat : lat;
          minLon = (lon >= minLon) ? minLon : lon;
        }, myMap);
        // Установим границы карты в соответсвии с полученными данными
        myMap.setBounds([[minLat, minLon], [maxLat, maxLon]]);

        // "Отключим" прелодер.
        $('#allPlacemarks').removeClass('loading');

        // Выплюнем в консольку данные, пришедшие от модуля:
        // console.log(data);

        // Эти переменные нужны для дальнейшего показа количества видимых на карте меток.
        var singleCounter = 0,
            singleCounterHidden = 0,
            clusterCounter = 0;

        // Пример организации обхода массива видимых меток
        objectManager.objects.each(function (object) {
          // В данный момент нас интересует конкретный геообъект
          var objectState = objectManager.getObjectState(object.id);
          if (objectState.isClustered) {
            // Если метка геообъекта находится в составе кластера - прибавим счётчик.
            clusterCounter++;
          }
          else {
            // Если метка не в кластере прибавим другой счётчик.
            singleCounterHidden++;
            if (objectState.isShown) {
              // Если метка не в кластере и показана в области
              // видимости карты - прибавим другой счётчик и
              // убавим счётчик всех объектов. Таким образом счётчик будет
              // показывать только скрытые объекты
              singleCounter++;
              singleCounterHidden--;
            }
          }
        }, myMap);

        // Ну и теперь можно выплюнуть собранные счётчики в консоль:
        console.log('Количество показаных единичных меток: ' + singleCounter);
        console.log('Количество невидимых единичных меток: ' + singleCounterHidden);
        console.log('Количество показаных меток, составе кластера: ' + clusterCounter);
      })
      .fail(function () {
        console.log("error");
      });
  }
</script>

{* В этом блоке будет отображаться карта *}
<div id="allPlacemarks" class="loading" style="height: 350px;"></div>