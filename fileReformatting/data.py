import os
import json

current_dir = os.path.dirname(os.path.abspath(__file__))

data_folder = os.path.join(current_dir, "../data")

json_files = ["dataset1.json", "dataset2.json", "dataset3.json", "dataset4.json"]

for json_file in json_files:
    input_path = os.path.join(data_folder, json_file)
    output_path = os.path.join(data_folder, f"f_{json_file}")

    try:
        data = None
        try:
            with open(input_path, "r", encoding='windows-1251') as f:
                data = json.load(f)
        except UnicodeDecodeError:
            print(f"Ошибка при чтении файла '{json_file}' с кодировкой 'windows-1251'. Пропуск файла.")
            continue

        with open(output_path, "w", encoding="utf-8") as f:
            json.dump(data, f, ensure_ascii=False, indent=4)

        print(f"Файл '{json_file}' успешно преобразован в '{output_path}'.")
    except Exception as e:
        print(f"Ошибка при обработке файла '{json_file}': {e}")

print("Обработка завершена.")
