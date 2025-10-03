#WordPress #MUPlugin #CSV #Archive #InteractivityAPI #PHP #JavaScript

Плагин для массовой архивации постов через CSV

Архитектура решения

📁 Основные файлы:

🎯 archive-post-status.php

Главный файл плагина

Подключает все зависимости

⚙️ class-archive-plugin.php

Регистрирует статус "archive" для постов

Добавляет кнопки архивации в админке

Обрабатывает смену статусов по одному

📊 class-archive-plugin-page.php

Создает страницу Tools → Archive Page

REST API для массовой обработки

Интеграция с RankMath для редиректов

🎨 archive-page.php

Интерфейс загрузки CSV файла

Интеграция с Interactivity API

🚀 archive-page.js

Обработка CSV через PapaParse

Прогресс-бар архивации

Пакетные запросы по 10 постов

📈 archive-page.css

Стили таблицы и прогресс-бара

🛠️ papaparse.js

Парсинг CSV файлов

Процесс работы:

Загрузка CSV с колонками: Post ID, URL, Redirect URL

Парсинг данных и отображение в таблице

Массовая архивация через REST API

Создание редиректов в RankMath

Очистка кэша (SpinupWP, Cloudflare)

Результат:

✅ Массовая архивация тысяч постов
✅ Автоматические редиректы
✅ Прогресс-бар с реальным статусом
✅ Интеграция с системами кэширования
✅ Безопасность через nonce и права доступа

🛠️ Использованные технологии:

WordPress Interactivity API

REST API

PapaParse для CSV

RankMath API

SpinupWP/Cloudflare интеграция
