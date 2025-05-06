### Scraper. Проект в разработке
#### Состоит из сервисов
- **parser-frontend** - интерфейс парсера сайтов. Vue, cборщик Vite. Работает на `localhost:777`
- **parser-backend** - backend парсера. Microframework Slim. За основу взята чистая архитектура. 
работает на `localhost:555`
- **shop** - Webasyst Framework + Shop-Script
- **mysql-shop, mysql-parser** - базы данных магазина и парсера

#### Как работает
В интерфейсе парсера создаются профили для каждого сайта, который необходимо парсить.
Под каждый профиль необходимо написать обрабтчик в бекенде, например для профиля с 
идентификатором gomobile -> `parser-backend/src/Infrastructure/Parser/GomobileParser.php`

Чтобы запустить парсинг, необходимо нажать кнопку Обновить (это обновит список товаров), потом
кнопку Парсить. Когда процесс будет завершен, то в папке parser-backend/storage будет создан 
файл `gomobile.product.jsonl`, в котором каждая строка будет json объект с данными об одном товаре

Парсинг также можно запускать через cron. Команды описаны в файле `parser-backend/cron/README.md`

#### Установка
После клонирования репозитория переименуйте файлы example.env в .env
- /.env
- docker/shop/.env
- docker/parser-frontend/.env
- docker/parser-backend/.env
Данные авторизаций к базам данных из файла /.env должны совпадать с `docker/parser-frontend/.env`,
`docker/parser-backend/.env`

### Scraper. Project in Development
#### Consists of the following services
- **parser-frontend** – website parser interface. Vue, built with Vite. Runs on `localhost:777`
- **parser-backend** – backend of the parser. Microframework Slim. Based on Clean Architecture.  
  Runs on `localhost:555`
- **shop** – Webasyst Framework + Shop-Script
- **mysql-shop**, **mysql-parser** – databases for the shop and the parser

#### How it works
Parser profiles are created in the interface for each website to be scraped.  
Each profile requires a corresponding handler in the backend. For example, for the profile with  
the identifier `gomobile`, the handler should be created at:  
`parser-backend/src/Infrastructure/Parser/GomobileParser.php`

To start scraping, click the **Update** button (this refreshes the product list), then  
the **Parse** button. When the process is finished, a file named  
`gomobile.product.jsonl` will be created in `parser-backend/storage`,  
where each line is a JSON object containing data about one product.

Scraping can also be triggered via cron. The commands are described in:  
`parser-backend/cron/README.md`

#### Installation
After cloning the repository, rename the `example.env` files to `.env`
- /.env
- docker/shop/.env
- docker/parser-frontend/.env
- docker/parser-backend/.env
