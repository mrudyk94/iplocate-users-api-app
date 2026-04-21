# 🚀 IP Locate API App

Backend API сервіс для роботи з користувачами.  
Проєкт запускається в Docker та використовує Symfony + MariaDB.

---

## 📦 Вимоги

- 🐳 Docker
- 🐳 Docker Compose
- 🐧 Linux / macOS (або WSL для Windows)

## Tech Stack

| Concern | Solution                               |
|---|----------------------------------------|
| Framework | Symfony 7                              |
| Database | Mariadb:10.11.6                        |
| Queue | RabbitMQ + Symfony Messenger            |
| IP Geolocation | iplocate.io REST API                   |
| API Docs | NelmioApiDocBundle + swagger-php       |
| Tests | PHPUnit 12 + Symfony KernelTestCase + Doctrine ORM |

## ⚙️ Швидкий старт

### 1️⃣ Клонування репозиторію
```bash
https://github.com/mrudyk94/iplocate-users-api-app.git
cd iplocate-users-api-app
```

### 2️⃣ Створення .env
```bash
cp env.dist .env
```

### 3️⃣ Збірка та запуск Docker
```text
# Збірка Docker контейнерів
docker compose build --no-cache

# Підняття контейнерів у фоновому режимі
docker compose up -d
```

В проєкті також є скрипт `run.sh` для зручної роботи з контейнерами.
```text
# Збірка Docker контейнерів
run build

# Підняття контейнерів у фоновому режимі
run up
```

### 4️⃣ Встановлення залежностей (Composer) без входу в контейнер
```bash
docker compose exec iplocate composer install
```

### 5️⃣ Міграції бази даних без входу в контейнер
> ⚠️ Перед виконанням міграцій **потрібно перезапустити контейнери**:
```bash
# Перезапуск контейнерів
docker compose down -v && docker compose up -d
```
```bash
docker compose exec iplocate php bin/console doctrine:migrations:migrate
```

### 🐰 RabbitMQ

Проєкт використовує RabbitMQ як транспорт для Symfony Messenger.

### 🔗 Management UI
http://localhost:15672

**Credentials:**
- login: `guest`
- password: `guest`

### 📌 Використання
- черга для обробки створення користувачів
- асинхронна обробка через worker-и
- можливість моніторингу повідомлень у реальному часі

### API Documentation

Visit **http://localhost:8045/api/doc** for the Swagger UI.

OpenAPI JSON spec: **http://localhost:8045/api/doc.json**

### 🧪 Приклади API-запитів (curl)

🔹 **Отримання списку користувачів**

Метод підтримує сортування результатів.

#### `sort` — поле для сортування
Обов’язковий параметр.

Доступні значення:
- `firstName` — ім’я
- `lastName` — прізвище
- `createdAt` — дата створення
- `updateAt` — дата оновлення
- `country` — країна

#### `order` — напрямок сортування
Необов’язковий параметр (за замовчуванням `DESC`).

Доступні значення:
- `ASC` — за зростанням
- `DESC` — за спаданням

```bash
curl --location 'http://localhost:8045/v1/api/users' \
--header 'Content-Type: application/json' \
--data '{
    "firstName": "Іван",
    "lastName": "Шевченко",
    "phoneNumbers": [
        "+380971234561",
        "+380631234561",
        "+380635492938"
    ]
}'
```
🔹 Отримати список всіх користувачів у сортованому вигляді
```bash
curl --location 'http://localhost:8045/v1/api/users/list?sort=createdAt&order=DESC'
```

🔹 Видалення користувача
```bash
curl --location --request DELETE 'http://localhost:8045/v1/api/users/1'
```

### 🧪 Postman Collection
```text
### 6️⃣ Postman Collection

Щоб швидко тестувати API:

1. Скопіюй JSON нижче у файл `Payment API.postman_collection.json`
2. Відкрий Postman → **Import** → **File** → вибери цей файл
3. Тепер готові запити до API

{
  "info": {
    "_postman_id": "683820be-2d35-4980-a568-908cf6419337",
    "name": "RESTful API users",
    "schema": "https://schema.getpostman.com/json/collection/v2.1.0/collection.json",
    "_exporter_id": "6390017"
  },
  "item": [
    {
      "name": "Add User",
      "request": {
        "method": "POST",
        "header": [],
        "body": {
          "mode": "raw",
          "raw": "{\r\n    \"firstName\": \"Іван\",\r\n    \"lastName\": \"Шевченко\",\r\n    \"phoneNumbers\": [\r\n        \"+380971234561\",\r\n        \"+380631234561\",\r\n        \"+380635492938\"\r\n    ]\r\n}",
          "options": {
            "raw": {
              "language": "json"
            }
          }
        },
        "url": {
          "raw": "http://localhost:8045/v1/api/users",
          "protocol": "http",
          "host": [
            "localhost"
          ],
          "port": "8045",
          "path": [
            "v1",
            "api",
            "users"
          ]
        }
      },
      "response": []
    },
    {
      "name": "Get List Users All",
      "request": {
        "method": "GET",
        "header": [],
        "url": {
          "raw": "http://localhost:8045/v1/api/users/list?sort=createdAt&order=DESC",
          "protocol": "http",
          "host": [
            "localhost"
          ],
          "port": "8045",
          "path": [
            "v1",
            "api",
            "users",
            "list"
          ],
          "query": [
            {
              "key": "sort",
              "value": "createdAt"
            },
            {
              "key": "order",
              "value": "DESC"
            }
          ]
        }
      },
      "response": []
    },
    {
      "name": "Delete User",
      "request": {
        "method": "DELETE",
        "header": [],
        "url": {
          "raw": "http://localhost:8045/v1/api/users/1",
          "protocol": "http",
          "host": [
            "localhost"
          ],
          "port": "8045",
          "path": [
            "v1",
            "api",
            "users",
            "1"
          ]
        }
      },
      "response": []
    }
  ]
}
```
