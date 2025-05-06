#!/bin/bash

# Проверка аргументов
if [ -z "$1" ]; then
    echo "Error: Profile ID is required"
    exit 1
fi

PROFILE_ID="$1"
LOG_FILE="/var/log/parser.log"  # Укажите путь к лог-файлу на хосте
PHP_SCRIPT="/var/www/html/cron/run_parser.php"        # Путь к PHP-скрипту относительно текущей директории

# Функция для записи в лог
log_message() {
    echo "$(date '+%Y-%m-%d %H:%M:%S') $1" >> "$LOG_FILE"
}

# Начало выполнения
log_message "Starting parser for profile $PROFILE_ID"

# Цикл выполнения парсера
while true; do
    # Выполняем PHP-скрипт и получаем значение complete
    COMPLETE=$(php "$PHP_SCRIPT" "$PROFILE_ID" 2>> "$LOG_FILE")
    EXIT_CODE=$?

    if [ $EXIT_CODE -ne 0 ]; then
        log_message "Error executing parser: check logs for details"
        exit 1
    fi

    # Проверяем, что complete получен
    if [ -z "$COMPLETE" ]; then
        log_message "Error: Unable to parse 'complete' from response"
        exit 1
    fi

    # Логируем значение complete
    log_message "Complete: $COMPLETE"

    # Проверяем завершение
    if [ "$COMPLETE" -eq 1 ]; then
        log_message "Parsing completed for profile $PROFILE_ID"
        break
    fi

    # Пауза между итерациями
    sleep 5
done

echo "Parsing completed for profile $PROFILE_ID"