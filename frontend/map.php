<!DOCTYPE html>
<html lang="ru">

<head>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Социальное благополучие Москвы</title>
  <link rel="stylesheet" href="style.css">
  <link rel="icon" href="/data/logo.svg" type="image/svg">
  <meta charset="utf-8">
  <script src="https://maps.api.2gis.ru/2.0/loader.js?pkg=full"></script>
  <script type="text/javascript">
    let map;
    let clustersLayer;

    // Инициализация карты
    DG.then(function () {
      map = DG.map('map', {
        center: [55.755820, 37.617633],
        zoom: 12
      });
      clustersLayer = DG.layerGroup().addTo(map);
    });

    // Очистка старых кластеров
    function clearClusters() {
      if (clustersLayer) {
        clustersLayer.clearLayers();
      }
    }

    // Отрисовка кластеров на карте
    function drawClusters(clusters) {
      clearClusters();
      clusters.forEach(cluster => {
        const { cluster_id, size, hull, color } = cluster;
        const polygonCoordinates = hull.map(point => [point.lat, point.lon]);

        DG.polygon(polygonCoordinates, { color: color, fillOpacity: 0.3 })
          .addTo(clustersLayer)
          .bindPopup(`<strong>Кластер ${cluster_id}</strong><br>Размер: ${size}`);
      });
    }

    // Обработчик отправки формы
    function handleFormSubmit(event) {
      event.preventDefault();
      const sliderValue = document.getElementById('range-slider').value;
      const checkboxes = document.querySelectorAll('.dataset-checkbox:checked');
      const selectedDatasets = Array.from(checkboxes).map(cb => cb.value);

      if (selectedDatasets.length === 0) {
        alert('Выберите хотя бы один набор данных');
        return;
      }

      const url = `../backend/cluster.php?clusters=${sliderValue}&datasets=${selectedDatasets.join(',')}`;

      fetch(url)
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            drawClusters(data.clusters);
          } else {
            alert(data.error);
          }
        })
        .catch(error => {
          console.error('Ошибка:', error);
          alert('Ошибка при загрузке данных');
        });
    }

    // Обновление значения ползунка
    function updateSliderValue(value) {
      document.getElementById('slider-value').textContent = value;
    }

    // Включение/отключение кнопки отправки
    function toggleSubmitButton() {
      const checkboxes = document.querySelectorAll('.dataset-checkbox');
      const submitButton = document.querySelector('button[type="submit"]');
      const anyChecked = Array.from(checkboxes).some(cb => cb.checked);
      submitButton.disabled = !anyChecked;
    }

    document.addEventListener('DOMContentLoaded', () => {
      toggleSubmitButton();
      const checkboxes = document.querySelectorAll('.dataset-checkbox');
      checkboxes.forEach(cb => cb.addEventListener('change', toggleSubmitButton));
    });
  </script>
</head>

<body>
  <header>
    <div class="header-left">
      <img src="/data/logo.svg" alt="Логотип" class="logo">
      <span class="project-title">Анализ и визуализация статистической информации о социальном благополучии районов Москвы</span>
    </div>
    <div class="header-right">
      <span>Проект использует открытые данные:</span>
      <a href="https://data.mos.ru" target="_blank">Портал открытых данных Правительства Москвы</a>
    </div>
  </header>

  <div class="content-container">
    <div id="map"></div>
    <div class="control-panel">
      <h2>Панель управления</h2>
      <form onsubmit="handleFormSubmit(event)">
        <label for="range-slider">Количество областей (1-15):</label>
        <div class="slider-container">
          <input type="range" id="range-slider" name="scale" min="1" max="15" value="12" oninput="updateSliderValue(this.value)">
          <span id="slider-value">12</span>
        </div>

        <div class="checkbox-group">
          <label><input type="checkbox" class="dataset-checkbox" value="dataset1"> Тренажерные городки</label>
          <label><input type="checkbox" class="dataset-checkbox" value="dataset2"> Остановки наземного транспорта</label>
          <label><input type="checkbox" class="dataset-checkbox" value="dataset3"> Стационарные торговые объекты</label>
          <label><input type="checkbox" class="dataset-checkbox" value="dataset4"> Школы</label>
        </div>

        <button type="submit" disabled>Подтвердить</button>
        <div class="instructions">
          <p>
            Для использования приложения выполните следующие шаги:
          </p>
          <ul>
            <li>Выберите количество областей с помощью ползунка (от 1 до 15).</li>
            <li>Отметьте галочками один или несколько наборов данных, по результатам анализа которых хотите получить результат.</li>
            <li>Нажмите кнопку "Подтвердить" для выполнения расчётов и отображения результатов на карте.</li>
          </ul>
        </div>
      </form>
    </div>
  </div>

  <footer>
    <p>&copy; 2025 ХМК. Контактная информация: m.k.khamidullin@mospolytech.ru</p>
  </footer>
</body>

</html>