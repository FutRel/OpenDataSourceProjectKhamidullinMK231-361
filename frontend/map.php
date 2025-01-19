<!DOCTYPE html>
<html lang='ru'>

<head>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Социальное благополучие Москвы</title>
  <link rel="stylesheet" href="style.css">
  <link rel="icon" href="\data\logo.svg" type="image/svg">
  <meta charset="utf-8">
  <script src="https://maps.api.2gis.ru/2.0/loader.js?pkg=full"></script>
  <script type="text/javascript">
    var map;
    DG.then(function () {
      map = DG.map('map', {
        center: [55.755820, 37.617633],
        zoom: 12
      });
    });
  </script>
</head>

<body>
  <header>
    <div class="header-left">
      <img src="\data\logo.svg" alt="Логотип" class="logo">
      <span class="project-title">OPEN DATA SOURCE PROJECT</span>
    </div>
    <div class="header-right">
      <span>Проект основан на открытых данных:</span>
      <a href="https://data.mos.ru" target="_blank">Портал открытых данных Правительства Москвы</a>
    </div>
  </header>

  <div id="map" style="width:80vw; height:90vh;"></div>

  <footer>
    <p>&copy; 2025 Веб-приложение. Все права защищены.</p>
  </footer>
</body>

</html>