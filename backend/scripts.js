$(document).ready(function () {
    // Словарь для русификации названий полей
    const fieldTranslations = {
        name_set: 'Название',
        id_set: 'Номер',
        adm_area_set: 'Административная область',
        web_site_set: 'Сайт',
        paid_set: 'Стоимость',
        lat_set: 'Широта',
        long_set: 'Долгота',
        service_set: 'Товары',
        object_set: 'Тип объекта',
        year_set: 'Год проишествия',
        road_set: 'Количество проишествий'
    };

    // При изменении датасета
    $('#dataset').change(function () {
        const datasetId = $(this).val();
        if (!datasetId) {
            $('#dynamic-filters').html('');
            $('#sort-by').html('<option value="">-- Выберите поле --</option>');
            return;
        }

        // Получаем фильтры и поля для сортировки
        $.get('../backend/get_filters.php', { dataset: datasetId }, function (response) {
            if (response.error) {
                alert('Ошибка: ' + response.error);
                return;
            }

            // Генерация HTML для фильтров
            let filtersHtml = '';
            response.filters.forEach(filter => {
                const label = fieldTranslations[filter];
                if (response.options[filter]) {
                    filtersHtml += `
                    <label for="${filter}">${label}:</label>
                    <select name="${filter}" id="${filter}">
                    <option value="">-- Выберите --</option>`;
                    response.options[filter].forEach(option => {
                        filtersHtml += `<option value="${option}">${option}</option>`;
                    });
                    filtersHtml += `</select><br>`;
                } else {
                    filtersHtml += `
                        <label for="${filter}">${label}:</label>
                        <input type="text" name="${filter}" id="${filter}"><br>`;
                }
            });

            $('#dynamic-filters').html(filtersHtml);

            // Обновляем поля для сортировки
            let sortOptionsHtml = '<option value="">-- Выберите поле --</option>';
            response.filters.forEach(filter => {
                const label = fieldTranslations[filter] || filter; // Используем перевод или оригинальное название
                sortOptionsHtml += `<option value="${filter}">${label}</option>`;
            });
            $('#sort-by').html(sortOptionsHtml);
        }, 'json').fail(function () {
            alert('Ошибка загрузки данных для фильтров');
        });
    });

    // Живой поиск
    $('#dynamic-filters').on('input', '#name_set', function () {
        const searchValue = $(this).val();
        const datasetId = $('#dataset').val();
        const $this = $(this);

        // Удаляем старые подсказки перед обновлением
        $('.suggestions').remove();

        if (datasetId && searchValue.length > 0) {
            $.get('../backend/search_name.php', { dataset: datasetId, query: searchValue }, function (response) {
                if (response.error) {
                    alert('Ошибка: ' + response.error);
                    return;
                }

                const sortedSuggestions = response.sort((a, b) => a.localeCompare(b));

                let suggestionsHtml = '<div class="suggestions">';
                sortedSuggestions.forEach(item => {
                    suggestionsHtml += `<div class="suggestion-item">${item}</div>`;
                });
                suggestionsHtml += '</div>';

                // Визуализация списка
                $this.after(suggestionsHtml);
            }, 'json').fail(function () {
                alert('Ошибка поиска по имени');
            });
        }
    });

    // Выбор варианта из предложенных
    $('#dynamic-filters').on('click', '.suggestion-item', function () {
        const selectedValue = $(this).text();
        $('#name_set').val(selectedValue); // Устанавливаем выбранное значение
        $('.suggestions').remove(); // Убираем список
    });

    // Отправка формы с фильтрами
    $('#filters-form').submit(function (e) {
        e.preventDefault();

        const filters = $(this).serialize();
        $.get('../backend/process_filters.php', filters, function (data) {
            if (data.error) {
                alert('Ошибка: ' + data.error);
                return;
            }

            // Отображение результатов в таблице
            let headerHtml = '', bodyHtml = '';
            if (data.length > 0) {
                Object.keys(data[0]).forEach(key => {
                    const label = fieldTranslations[key];
                    headerHtml += `<th>${label}</th>`;
                });
                data.forEach(row => {
                    bodyHtml += '<tr>';
                    Object.values(row).forEach(value => {
                        bodyHtml += `<td>${value}</td>`;
                    });
                    bodyHtml += '</tr>';
                });
            } else {
                bodyHtml = '<tr><td colspan="100%">Нет данных</td></tr>';
            }

            $('#table-header').html(headerHtml);
            $('#table-body').html(bodyHtml);
        }, 'json').fail(function () {
            alert('Ошибка обработки фильтров');
        });
    });
});
