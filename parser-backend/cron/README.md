### Команды запуска в контейнере php

#### Запуск парсера
```bash
sh /var/www/html/cron/go_parser.sh 9
```
9 - это id профиля

#### Запуск обновления парсера
```bash
php /var/www/html/cron/refresh_parser.php 9
```
9 - это id профиля