# MediAgenda

Sistema web para gerenciamento de consultas medicas, desenvolvido em PHP com MySQL/MariaDB, HTML5, CSS3, Bootstrap e JavaScript.

## Descricao

O MediAgenda permite acompanhar a agenda de consultas, cadastrar agendamentos, gerenciar medicos e manter o cadastro de especialidades medicas usadas no sistema.

## Funcionalidades Implementadas

- Login de usuario.
- Visualizacao da agenda mensal.
- Cadastro, listagem, edicao e cancelamento de agendamentos.
- CRUD completo de medicos:
  - cadastro;
  - listagem com filtros;
  - edicao;
  - inativacao;
  - exclusao definitiva somente quando nao houver agendamentos vinculados;
  - relacionamento com especialidades.
- CRUD completo de especialidades:
  - cadastro;
  - listagem com filtros;
  - edicao;
  - inativacao;
  - exclusao definitiva somente quando nao houver vinculos;
  - integracao com medicos e agendamentos.
- Navegacao lateral corrigida para agenda, medicos e especialidades.
- Banco de dados com tabelas, relacionamentos e views atualizadas.

## Tecnologias Utilizadas

- PHP
- MySQL / MariaDB
- HTML5
- CSS3
- Bootstrap 5
- JavaScript
- SweetAlert2
- Docker e Docker Compose

## Como Executar

1. Suba os containers:

```bash
docker compose up -d
```

2. Acesse o phpMyAdmin:

```text
http://localhost:8081
```

3. Importe o arquivo `script.sql` no banco `mediagenda`.

4. Acesse o sistema:

```text
http://localhost:8080/login.php
```

5. Use um dos logins iniciais:

```text
Usuario: aluno
Senha: 123456
```

```text
Usuario: professor
Senha: professor123
```

## Configuracao do Banco

A conexao fica em:

```text
www/conexao.php
```

Configuracao padrao para Docker:

```text
Host: mysql
Usuario: root
Senha: root123
Banco: mediagenda
Porta: 3306
```

## Integrantes do Grupo

- João Víctor Souza Resende
- Leticia Gomes
- Mel Borges
- Arthur Miguel
- Arthur Garlati
- Luciana Carolline

