<!DOCTYPE html>
<html lang="en">

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Социальное благополучие Москвы</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="search-style.css">
    <link rel="stylesheet" href="localized-style.css">
    <link rel="icon" href="/data/logo.svg" type="image/svg+xml">
    <meta charset="utf-8">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="../backend/scripts.js"></script>
</head>

<body>
    <header>
        <div class="header-left">
            <img src="/data/logo.svg" alt="Логотип" class="logo">
            <span class="project-title">Социальное благополучие Москвы</span>
        </div>
        <nav class="navbar">
            <ul class="nav-links">
                <li><a href="index.php">Главная</a></li>
                <li><a href="map.php">Карта</a></li>
                <li><a href="data.php">Данные</a></li>
            </ul>
        </nav>
    </header>
    <main>
        <h1>Фильтры данных</h1>
        <form id="filters-form">
            <label for="dataset">Выберите датасет:</label>
            <select name="dataset" id="dataset">
                <option value="">-- Выберите датасет --</option>
                <option value="1">Тренажерные городки</option>
                <option value="2">Остановки наземного транспорта</option>
                <option value="3">Стационарные торговые объекты</option>
                <option value="4">Школы</option>
            </select>
            <div id="dynamic-filters"></div>

            <div id="sort-section">
                <label for="sort-by">Сортировать по:</label>
                <select name="sort_by" id="sort-by">
                    <option value="">-- Выберите поле --</option>
                </select>

                <div>
                    <label><input type="radio" name="sort_order" value="ASC" checked> Возрастание</label>
                    <label><input type="radio" name="sort_order" value="DESC"> Убывание</label>
                </div>
            </div>

            <button type="submit">Применить</button>
        </form>
        <div id="results">
            <table id="results-table">
                <thead>
                    <tr id="table-header"></tr>
                </thead>
                <tbody id="table-body"></tbody>
            </table>
        </div>
    </main>
    <footer>
        <span>Проект использует открытые данные:</span>
        <a href="https://data.mos.ru" target="_blank">Портал открытых данных Правительства Москвы</a>
    </footer>
</body>

</html>