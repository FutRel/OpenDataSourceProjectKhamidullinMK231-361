<!DOCTYPE html>
<html lang="ru">

<head>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Социальное благополучие Москвы</title>
  <link rel="stylesheet" href="style.css">
  <link rel="icon" href="/data/logo.svg" type="image/svg+xml">
  <meta charset="utf-8">
  <script src="https://maps.api.2gis.ru/2.0/loader.js?pkg=full"></script>
  <script type="text/javascript">
    let map, clustersLayer;

    DG.then(() => {
      map = DG.map('map', { center: [55.755820, 37.617633], zoom: 11 });
      clustersLayer = DG.layerGroup().addTo(map);
    });

    const datasetIcons = {
      dataset1: '../data/icon1.png',
      dataset2: '../data/icon2.png',
      dataset3: '../data/icon3.png',
      dataset4: '../data/icon4.png',
    };

    async function fetchData(url) {
      try {
        const response = await fetch(url);

        if (!response.ok) {
          throw new Error(`Ошибка сети: ${response.statusText}`);
        }

        const data = await response.json();

        if (data.success) {
          processClusters(data.clusters);
        } else {
          alert(data.error || 'Ошибка обработки данных');
        }
      } catch (error) {
        console.error('Ошибка запроса:', error);
        alert('Ошибка при загрузке данных.');
      }
    }

    // Обработка и отрисовка кластеров и точек
    function processClusters(clusters) {
      clustersLayer.clearLayers();

      const batchSize = 100;
      let currentClusterIndex = 0;
      let currentPointIndex = 0;

      function drawBatch() {
        while (currentClusterIndex < clusters.length) {
          const cluster = clusters[currentClusterIndex];

          while (currentPointIndex < cluster.points.length) {
            const point = cluster.points[currentPointIndex];

            const iconUrl = datasetIcons[point.dataset] || 'default-icon.png';
            const markerIcon = DG.icon({
              iconUrl: iconUrl,
              iconSize: [10, 10],
            });

            DG.marker([point.lat_set, point.long_set], { icon: markerIcon })
              .addTo(clustersLayer)
              .bindPopup(`<strong>${point.name_set}</strong><br>Датасет: ${point.dataset}`);

            currentPointIndex++;

            if (currentPointIndex % batchSize === 0) {
              setTimeout(drawBatch, 50);
              return;
            }
          }

          if (cluster.hull.length > 2) {
            console.log(`Отрисовка полигона для кластера ${cluster.cluster_id}`);
            const polygonCoords = cluster.hull.map(({ lat_set, long_set }) => [lat_set, long_set]);
            DG.polygon(polygonCoords, { color: cluster.color, fillOpacity: 0.5 })
              .addTo(clustersLayer)
              .bindPopup(`<strong>Кластер ${cluster.cluster_id}</strong><br>Размер: ${cluster.size}`);
          }

          // Переход к следующему кластеру
          console.log(`Кластер ${cluster.cluster_id} обработан.`);
          currentClusterIndex++;
          currentPointIndex = 0;
        }

        console.log('Все кластеры успешно отрисованы.');
      }

      drawBatch();
    }

    // Обработка отправки формы
    function handleFormSubmit(event) {
      event.preventDefault();

      const sliderValue = document.getElementById('range-slider').value;
      const selectedDatasets = Array.from(document.querySelectorAll('.dataset-checkbox:checked'))
        .map(cb => cb.value);

      const url = `../backend/cluster.php?clusters=${sliderValue}&datasets=${selectedDatasets.join(',')}`;
      fetchData(url);
    }

    function updateSliderValue(value) {
      document.getElementById('slider-value').textContent = value;
    }

    function toggleSubmitButton() {
      const hasSelection = Array.from(document.querySelectorAll('.dataset-checkbox')).some(cb => cb.checked);
      document.querySelector('button[type="submit"]').disabled = !hasSelection;
    }

    document.addEventListener('DOMContentLoaded', () => {
      toggleSubmitButton();
      document.querySelectorAll('.dataset-checkbox').forEach(cb => cb.addEventListener('change', toggleSubmitButton));
    });
  </script>
</head>

<body>
  <header>
    <div class="header-left">
      <img src="/data/logo.svg" alt="Логотип" class="logo">
      <span class="project-title">Социальное благополучие Москвы</span>
      <nav class="navbar">
            <ul class="nav-links">
                <li><a href="index.php">Главная</a></li>
                <li><a href="map.php">Карта</a></li>
            </ul>
        </nav>
    </div>
  </header>

  <div class="content-container">
    <div id="map"></div>
    <div class="control-panel">
      <h2>Панель управления</h2>
      <form onsubmit="handleFormSubmit(event)">
        <label for="range-slider">Количество областей (2-20):</label>
        <div class="slider-container">
          <input type="range" id="range-slider" name="scale" min="2" max="20" value="12"
            oninput="updateSliderValue(this.value)">
          <span id="slider-value">12</span>
        </div>

        <div class="checkbox-group">
          <label><input type="checkbox" class="dataset-checkbox" value="dataset1"> Тренажерные городки</label>
          <label><input type="checkbox" class="dataset-checkbox" value="dataset2"> Остановки наземного
            транспорта</label>
          <label><input type="checkbox" class="dataset-checkbox" value="dataset3"> Стационарные торговые объекты</label>
          <label><input type="checkbox" class="dataset-checkbox" value="dataset4"> Школы</label>
        </div>

        <button type="submit" disabled>Подтвердить</button>
        <div class="instructions">
          <p>Для использования приложения выполните следующие шаги:</p>
          <ul>
            <li>Выберите количество областей с помощью ползунка (от 2 до 20).</li>
            <li>Отметьте галочками один или несколько наборов данных, по результатам анализа которых хотите получить
              результат.</li>
            <li>Нажмите кнопку "Подтвердить" для выполнения расчётов и отображения результатов на карте.</li>
          </ul>
        </div>
      </form>
    </div>
  </div>
  
  <footer>
    <span>Проект использует открытые данные:</span>
    <a href="https://data.mos.ru" target="_blank">Портал открытых данных Правительства Москвы</a>
  </footer>
</body>

</html>