/******/ (() => { // webpackBootstrap
/*!******************************************!*\
  !*** ./src/blocks/block-clinics/view.js ***!
  \******************************************/
initMap();
async function initMap() {
  // Промис `ymaps3.ready` будет зарезолвлен, когда загрузятся все компоненты основного модуля API
  await ymaps3.ready;
  const {
    YMap,
    YMapDefaultSchemeLayer,
    YMapDefaultFeaturesLayer,
    YMapMarker
  } = ymaps3;
  const maps = document.querySelectorAll('.clinics__item-map-container');
  if (maps) {
    maps.forEach(el => {
      const lat = parseFloat(el.dataset.lat) || 55.733842;
      const lng = parseFloat(el.dataset.lng) || 37.588144;
      const map = new YMap(el, {
        location: {
          center: [lng, lat],
          zoom: 15
        }
      });
      map.addChild(new YMapDefaultSchemeLayer());
      map.addChild(new YMapDefaultFeaturesLayer());

      // Пин на карте
      const pinEl = document.createElement('div');
      pinEl.innerHTML = '<svg width="32" height="42" viewBox="0 0 32 42" fill="none" xmlns="http://www.w3.org/2000/svg">' + '<path d="M16 0C7.163 0 0 7.163 0 16c0 12 16 26 16 26s16-14 16-26C32 7.163 24.837 0 16 0z" fill="#FF4D4D"/>' + '<circle cx="16" cy="16" r="6" fill="#fff"/></svg>';
      pinEl.style.cssText = 'position:absolute;transform:translate(-50%,-100%);cursor:pointer;';
      map.addChild(new YMapMarker({
        coordinates: [lng, lat]
      }, pinEl));
    });
  }
}
/******/ })()
;
//# sourceMappingURL=view.js.map