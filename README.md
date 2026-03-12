# BeTalent Payment API

API RESTful para gerenciamento de pagamentos multi-gateway, desenvolvida em Laravel 12 com suporte a múltiplos gateways de pagamento e fallback automático.

---

## 📋 Índice

- [Requisitos](#requisitos)
- [Instalação e Execução](#instalação-e-execução)
- [Variáveis de Ambiente](#variáveis-de-ambiente)
- [Estrutura do Banco de Dados](#estrutura-do-banco-de-dados)
- [Rotas da API](#rotas-da-api)
- [Multi-Gateways](#multi-gateways)
- [Autenticação](#autenticação)
- [Collection Postman](#collection-postman)

---

## ✅ Requisitos

- [Docker](https://www.docker.com/) e Docker Compose
- Sem necessidade de PHP ou MySQL instalados localmente

---

## 🚀 Instalação e Execução

### 1. Clone o repositório

```bash
git clone https://github.com/seu-usuario/betalent-api.git
cd betalent-api
```

### 2. Configure as variáveis de ambiente

```bash
cp .env.example .env
```

### 3. Suba os containers

```bash
docker compose up --build
```

Isso irá automaticamente:
- Subir o MySQL
- Rodar as migrations
- Iniciar a API Laravel na porta `8000`
- Iniciar os mocks dos gateways nas portas `3001` e `3002`

### 4. Crie um usuário administrador

```bash
docker exec -it betalent-api-app-1 php artisan tinker
```

```php
\App\Models\User::create([
    'name'     => 'Admin',
    'email'    => 'admin@betalent.com',
    'password' => bcrypt('sua_senha'),
    'role'     => 'admin',
]);
```

A API estará disponível em: **http://localhost:8000/api**

---

## 🔧 Variáveis de Ambiente

| Variável | Descrição | Padrão |
|---|---|---|
| `APP_NAME` | Nome da aplicação | `BeTalent` |
| `APP_ENV` | Ambiente | `local` |
| `APP_KEY` | Chave da aplicação | Gerada automaticamente |
| `APP_PORT` | Porta da aplicação | `8000` |
| `DB_CONNECTION` | Driver do banco | `mysql` |
| `DB_HOST` | Host do banco | `db` |
| `DB_PORT` | Porta do banco | `3306` |
| `DB_DATABASE` | Nome do banco | `betalent` |
| `DB_USERNAME` | Usuário do banco | `root` |
| `DB_PASSWORD` | Senha do banco | `root` |
| `GATEWAY1_URL` | URL do Gateway 1 | `http://gateways:3001` |
| `GATEWAY2_URL` | URL do Gateway 2 | `http://gateways:3002` |

---

## 🗄 Estrutura do Banco de Dados

```
users
├── id
├── name
├── email
├── password
├── role (admin, manager, finance, user)
└── timestamps

clients
├── id
├── name
├── email (unique)
└── timestamps

gateways
├── id
├── name
├── is_active
├── priority
└── timestamps

products
├── id
├── name
├── amount
└── timestamps

transactions
├── id
├── client_id (FK → clients)
├── gateway_id (FK → gateways)
├── external_id
├── status (paid, voided)
├── amount
├── card_last_numbers
└── timestamps
```

---

## 🛣 Rotas da API

### Rotas Públicas

| Método | Rota | Descrição | Body |
|---|---|---|---|
| `POST` | `/api/login` | Autenticação do usuário | `email`, `password` |
| `POST` | `/api/purchase` | Realizar uma compra | `amount`, `name`, `email`, `card_number`, `cvv` |

#### Exemplo — Login

```json
POST /api/login
{
    "email": "admin@betalent.com",
    "password": "sua_senha"
}
```

#### Exemplo — Compra

```json
POST /api/purchase
{
    "amount": 1000,
    "name": "João Silva",
    "email": "joao@email.com",
    "card_number": "5569000000006063",
    "cvv": "010"
}
```

> `amount` é em centavos. Ex: `1000` = R$ 10,00

---

### Rotas Privadas

> Todas as rotas privadas requerem o header: `Authorization: Bearer {token}`

#### Transações

| Método | Rota | Descrição |
|---|---|---|
| `GET` | `/api/transactions` | Listar todas as transações |
| `GET` | `/api/transactions/{id}` | Detalhe de uma transação |
| `POST` | `/api/transactions/{id}/refund` | Realizar reembolso |

#### Clientes

| Método | Rota | Descrição |
|---|---|---|
| `GET` | `/api/clients` | Listar todos os clientes |
| `GET` | `/api/clients/{id}` | Detalhe do cliente com suas transações |

#### Gateways

| Método | Rota | Descrição | Body |
|---|---|---|---|
| `PATCH` | `/api/gateways/{id}/toggle` | Ativar ou desativar gateway | — |
| `PATCH` | `/api/gateways/{id}/priority` | Alterar prioridade do gateway | `priority` |

#### Produtos

| Método | Rota | Descrição |
|---|---|---|
| `GET` | `/api/products` | Listar produtos |
| `POST` | `/api/products` | Criar produto |
| `GET` | `/api/products/{id}` | Detalhe do produto |
| `PUT` | `/api/products/{id}` | Atualizar produto |
| `DELETE` | `/api/products/{id}` | Deletar produto |

#### Usuários

| Método | Rota | Descrição |
|---|---|---|
| `GET` | `/api/users` | Listar usuários |
| `POST` | `/api/users` | Criar usuário |
| `GET` | `/api/users/{id}` | Detalhe do usuário |
| `PUT` | `/api/users/{id}` | Atualizar usuário |
| `DELETE` | `/api/users/{id}` | Deletar usuário |

---

## 💳 Multi-Gateways

A API suporta múltiplos gateways de pagamento com **fallback automático**. Ao realizar uma compra, o sistema tenta processar pelo gateway de maior prioridade. Em caso de falha, tenta o próximo gateway ativo automaticamente.

### Fluxo de pagamento

```
Requisição de compra
        ↓
Busca gateways ativos ordenados por prioridade
        ↓
Tenta Gateway 1 (prioridade 1)
   ├── Sucesso → Salva transação e retorna 201
   └── Falha → Tenta próximo gateway
        ↓
Tenta Gateway 2 (prioridade 2)
   ├── Sucesso → Salva transação e retorna 201
   └── Falha → Retorna erro 422
```

### Adicionando novos gateways

A arquitetura foi pensada para facilitar a adição de novos gateways:

1. Crie uma nova classe em `app/Services/Gateways/` implementando `GatewayInterface`
2. Implemente os métodos `charge()` e `refund()`
3. Registre o gateway no `GatewayManager`
4. Insira o novo gateway na tabela `gateways` com a prioridade desejada

---

## 🔐 Autenticação

A API utiliza **Laravel Sanctum** para autenticação via Bearer Token.

### Como usar

1. Faça login em `POST /api/login`
2. Copie o `token` retornado
3. Adicione no header de todas as requisições privadas:

```
Authorization: Bearer {token}
```

### Roles de usuário

| Role | Descrição |
|---|---|
| `admin` | Acesso total |
| `manager` | Gerencia produtos e usuários |
| `finance` | Gerencia produtos e realiza reembolsos |
| `user` | Acesso básico |

---

## 📮 Collection Postman

Importe o arquivo `betalent-api.postman_collection.json` no Postman para ter todos os endpoints pré-configurados.

> 💡 O endpoint de Login salva o token automaticamente em uma variável da collection, dispensando configuração manual nos demais endpoints.

---

## 🐳 Containers Docker

| Container | Imagem | Porta |
|---|---|---|
| `betalent-api-app-1` | PHP 8.2 + Laravel | `8000` |
| `betalent-api-db-1` | MySQL 8.0 | `3306` |
| `betalent-api-gateways-1` | gateways-mock | `3001`, `3002` |

---

## 🏗 Estrutura do Projeto

```
/
├── app/
│   ├── Http/Controllers/
│   │   ├── AuthController.php
│   │   ├── PurchaseController.php
│   │   ├── TransactionController.php
│   │   ├── ClientController.php
│   │   ├── GatewayController.php
│   │   ├── ProductController.php
│   │   └── UserController.php
│   ├── Models/
│   │   ├── User.php
│   │   ├── Client.php
│   │   ├── Gateway.php
│   │   ├── Product.php
│   │   └── Transaction.php
│   └── Services/
│       ├── PurchaseService.php
│       └── Gateways/
│           ├── GatewayInterface.php
│           ├── GatewayManager.php
│           ├── Gateway1Service.php
│           └── Gateway2Service.php
├── database/
│   ├── migrations/
│   └── seeders/
├── routes/
│   └── api.php
├── docker/
│   └── entrypoint.sh
├── Dockerfile
├── docker-compose.yml
├── .env.example
└── README.md
```

---

Desenvolvido para o **Teste Prático Back-end BeTalent** — Nível 1.
