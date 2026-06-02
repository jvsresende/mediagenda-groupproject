# MediAgenda

Sistema web para gerenciamento e agendamento de consultas medicas desenvolvido em PHP durante as aulas de Programacao II e melhorado em grupo.

---

# Sobre o Projeto

O MediAgenda e uma aplicacao desenvolvida para auxiliar no gerenciamento de consultas medicas, permitindo:

- Login de usuarios.
- Visualizacao de agenda mensal.
- Cadastro e gerenciamento de agendamentos.
- Cadastro, listagem, edicao, inativacao e exclusao de medicos.
- Cadastro, listagem, edicao, inativacao e exclusao de especialidades.
- Cancelamento de consultas.
- Dashboard com calendario.

O projeto foi desenvolvido utilizando conceitos de:

- PHP
- MySQL / MariaDB
- HTML5
- CSS3
- Bootstrap 5
- JavaScript
- SweetAlert2
- Docker e Docker Compose
- Git e GitHub

---

# Estrutura do Projeto

```text
mediagenda-groupproject/
|
|-- docker-compose.yml
|-- dockerfile
|-- Dockerfile.node
|-- script.sql
|-- README.md
|
|-- www/
|   |-- login.php
|   |-- cadastrobanco.php
|   |-- principal.php
|   |-- logout.php
|   |-- conexao.php
|   |-- cadastro_agendas.php
|   |-- cadastro_medicos.php
|   |-- cadastro_especialidades.php
|   |-- cancelar_agendamento.php
|   |-- img/
|
|-- nodejs/
|   |-- server.js
|   |-- conexao.js
|   |-- package.json
|   |-- public/
```

---

# Banco de Dados

O sistema utiliza MySQL.

O arquivo:

```text
script.sql
```

contem:

- criacao do banco;
- tabelas;
- relacionamentos;
- views utilizadas pelo sistema;
- usuarios iniciais para acesso.

Configuracao padrao do banco no Docker:

```text
Host interno: mysql
Host pelo Workbench: localhost
Porta: 3306
Usuario: root
Senha: root123
Banco: mediagenda
```

---

# Como Executar

## 1. Iniciar o Docker

Abra o Docker Desktop e aguarde o engine ficar ativo.

Se ja existir outro MySQL usando a porta 3306, pare esse servico antes de iniciar o projeto.

---

## 2. Subir os containers

Na pasta do projeto, execute:

```bash
docker compose up -d
```

---

## 3. Importar o banco

Importe o arquivo:

```text
script.sql
```

no banco:

```text
mediagenda
```

Tambem e possivel importar pelo phpMyAdmin.

---

## 4. Acessar o sistema

Sistema:

```text
http://localhost:8080/login.php
```

phpMyAdmin:

```text
http://localhost:8081
```

Node.js:

```text
http://localhost:3000
```

---

# Logins Iniciais

```text
Usuario: aluno
Senha: 123456
```

```text
Usuario: professor
Senha: professor123
```

---

# Integrantes do Grupo

- Joao Victor Souza Resende
- Leticia Gomes
- Mel Borges
- Arthur Miguel
- Arthur Garlati
- Luciana Carolline

---

# Objetivo Academico

Este projeto possui finalidade educacional e foi desenvolvido como atividade pratica da disciplina de Programacao II.

---

# Funcionalidades Futuras

- Responsividade mobile.
- Notificacoes de consultas.
- Relatorios.
- Controle de perfis de acesso.
- Publicacao em nuvem.

---

# Tecnologias Utilizadas

| Tecnologia | Finalidade |
|---|---|
| PHP | Back-end |
| MySQL | Banco de dados |
| Bootstrap 5 | Interface |
| JavaScript | Interatividade |
| SweetAlert2 | Alertas |
| Docker | Ambiente de execucao |
| Git/GitHub | Versionamento |

---

# Observacao

Projeto desenvolvido para fins academicos e aprendizado de desenvolvimento web com PHP e banco de dados relacional.
