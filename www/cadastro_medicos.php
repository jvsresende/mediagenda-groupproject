<?php
session_start();
require_once("conexao.php");

if (!isset($_SESSION['cod_usuario'])) {
    header("Location: login.php");
    exit;
}

function h($valor) {
    return htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8');
}

function voltarMedicos($tipo, $msg) {
    header("Location: cadastro_medicos.php?tipo=" . urlencode($tipo) . "&msg=" . urlencode($msg));
    exit;
}

$cod_usuario = (int)$_SESSION['cod_usuario'];
$operadorNome = "";
$operadorEmail = "";
$usuarioSql = "SELECT nome, email FROM usuario WHERE cod_usuario = $cod_usuario";
$usuarioResult = mysqli_query($conexao_bd, $usuarioSql);
if ($usuario = mysqli_fetch_assoc($usuarioResult)) {
    $operadorNome = $usuario['nome'];
    $operadorEmail = $usuario['email'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = isset($_POST['acao']) ? $_POST['acao'] : '';
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

    if ($acao === 'novo' || $acao === 'editar') {
        $nome = mysqli_real_escape_string($conexao_bd, trim($_POST['nome'] ?? ''));
        $crm = mysqli_real_escape_string($conexao_bd, trim($_POST['crm'] ?? ''));
        $especialidadeId = (int)($_POST['especialidade_id'] ?? 0);
        $telefone = mysqli_real_escape_string($conexao_bd, trim($_POST['telefone'] ?? ''));
        $email = mysqli_real_escape_string($conexao_bd, trim($_POST['email'] ?? ''));
        $status = ($_POST['status'] ?? 'Ativo') === 'Inativo' ? 'Inativo' : 'Ativo';

        if ($nome === '' || $crm === '' || $especialidadeId <= 0) {
            voltarMedicos('erro', 'Preencha nome, CRM e especialidade.');
        }

        if ($acao === 'novo') {
            $sql = "INSERT INTO medicos (nome, crm, especialidade_id, telefone, email, status)
                    VALUES ('$nome', '$crm', $especialidadeId, '$telefone', '$email', '$status')";
            $ok = mysqli_query($conexao_bd, $sql);
            voltarMedicos($ok ? 'sucesso' : 'erro', $ok ? 'Medico cadastrado com sucesso.' : 'Nao foi possivel cadastrar o medico. Verifique se o CRM ja existe.');
        }

        if ($id <= 0) {
            voltarMedicos('erro', 'Medico invalido.');
        }

        $sql = "UPDATE medicos
                   SET nome = '$nome',
                       crm = '$crm',
                       especialidade_id = $especialidadeId,
                       telefone = '$telefone',
                       email = '$email',
                       status = '$status'
                 WHERE id = $id";
        $ok = mysqli_query($conexao_bd, $sql);
        voltarMedicos($ok ? 'sucesso' : 'erro', $ok ? 'Medico atualizado com sucesso.' : 'Nao foi possivel atualizar o medico.');
    }

    if ($acao === 'inativar') {
        if ($id <= 0) {
            voltarMedicos('erro', 'Medico invalido.');
        }
        $ok = mysqli_query($conexao_bd, "UPDATE medicos SET status = 'Inativo' WHERE id = $id");
        voltarMedicos($ok ? 'sucesso' : 'erro', $ok ? 'Medico inativado com sucesso.' : 'Nao foi possivel inativar o medico.');
    }

    if ($acao === 'excluir') {
        if ($id <= 0) {
            voltarMedicos('erro', 'Medico invalido.');
        }
        $vinculos = mysqli_fetch_assoc(mysqli_query($conexao_bd, "SELECT COUNT(*) AS total FROM agendamentos WHERE medico_id = $id"));
        if ((int)$vinculos['total'] > 0) {
            voltarMedicos('erro', 'Este medico possui agendamentos vinculados. Use inativar para manter o historico.');
        }
        $ok = mysqli_query($conexao_bd, "DELETE FROM medicos WHERE id = $id");
        voltarMedicos($ok ? 'sucesso' : 'erro', $ok ? 'Medico excluido com sucesso.' : 'Nao foi possivel excluir o medico.');
    }
}

$filtroNome = trim(isset($_GET['nome']) ? $_GET['nome'] : '');
$filtroEspecialidade = trim(isset($_GET['especialidade']) ? $_GET['especialidade'] : '');
$filtroStatus = trim(isset($_GET['status']) ? $_GET['status'] : '');
$mensagem = isset($_GET['msg']) ? $_GET['msg'] : '';
$tipoMensagem = isset($_GET['tipo']) && $_GET['tipo'] === 'erro' ? 'erro' : 'sucesso';

$especialidades = array();
$espResult = mysqli_query($conexao_bd, "SELECT id, nome, status FROM especialidades ORDER BY nome");
while ($esp = mysqli_fetch_assoc($espResult)) {
    $especialidades[] = $esp;
}

$where = array();
if ($filtroNome !== '') {
    $nomeBusca = mysqli_real_escape_string($conexao_bd, $filtroNome);
    $where[] = "m.nome LIKE '%$nomeBusca%'";
}
if ($filtroEspecialidade !== '') {
    $where[] = "m.especialidade_id = " . (int)$filtroEspecialidade;
}
if ($filtroStatus !== '') {
    $statusBusca = mysqli_real_escape_string($conexao_bd, $filtroStatus);
    $where[] = "m.status = '$statusBusca'";
}
$whereSql = count($where) ? "WHERE " . implode(" AND ", $where) : "";

$medicos = array();
$sqlMedicos = "SELECT m.id, m.nome, m.crm, m.especialidade_id, e.nome AS especialidade,
                      m.telefone, m.email, m.status
                 FROM medicos m
                 JOIN especialidades e ON e.id = m.especialidade_id
                 $whereSql
             ORDER BY m.nome";
$medicosResult = mysqli_query($conexao_bd, $sqlMedicos);
while ($med = mysqli_fetch_assoc($medicosResult)) {
    $medicos[] = $med;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MediAgenda - Cadastro de Medicos</title>
    <link rel="icon" type="image/x-icon" href="img/favicon.ico">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        :root { --azul-primario:#0d6efd; --azul-escuro:#084298; --azul-claro:#e7f1ff; --cinza-fundo:#f5f7fa; --cinza-borda:#e3e6ea; --texto-escuro:#1f2d3d; --sidebar-larg:250px; }
        body { background-color:var(--cinza-fundo); font-family:'Segoe UI', Tahoma, sans-serif; color:var(--texto-escuro); overflow-x:hidden; }
        .navbar-topo { background:linear-gradient(90deg,var(--azul-primario) 0%,var(--azul-escuro) 100%); height:60px; box-shadow:0 2px 8px rgba(0,0,0,0.08); position:fixed; top:0; left:0; right:0; z-index:1030; }
        .navbar-topo .navbar-brand { color:#fff; font-weight:600; font-size:1.25rem; }
        .navbar-topo .navbar-brand i { margin-right:8px; }
        .btn-sanduiche { background:transparent; border:none; color:#fff; font-size:1.3rem; padding:6px 12px; border-radius:6px; transition:background 0.2s; }
        .btn-sanduiche:hover { background:rgba(255,255,255,0.15); }
        .operador-toggle { background:transparent; border:none; color:#fff; display:flex; align-items:center; gap:8px; padding:6px 12px; border-radius:30px; transition:background 0.2s; }
        .operador-toggle:hover, .operador-toggle:focus { background:rgba(255,255,255,0.15); color:#fff; }
        .operador-toggle i.fa-circle-user { font-size:1.6rem; }
        .dropdown-menu-operador { min-width:220px; border-radius:10px; box-shadow:0 4px 16px rgba(0,0,0,0.12); border:none; }
        .dropdown-menu-operador .dropdown-item i { width:22px; color:var(--azul-primario); }
        .sidebar { position:fixed; top:60px; left:0; width:var(--sidebar-larg); height:calc(100vh - 60px); background:#fff; border-right:1px solid var(--cinza-borda); padding:20px 0; transition:transform 0.3s ease; z-index:1020; overflow-y:auto; }
        .sidebar.oculta { transform:translateX(calc(var(--sidebar-larg) * -1)); }
        .sidebar .nav-link { color:var(--texto-escuro); padding:12px 20px; border-left:3px solid transparent; transition:all 0.2s; display:flex; align-items:center; gap:12px; }
        .sidebar .nav-link i { width:22px; color:var(--azul-primario); font-size:1.05rem; }
        .sidebar .nav-link:hover, .sidebar .nav-link.ativo { background:var(--azul-claro); border-left-color:var(--azul-primario); color:var(--azul-escuro); }
        .sidebar .nav-link.ativo { font-weight:600; }
        .sidebar-overlay { display:none; position:fixed; top:60px; left:0; right:0; bottom:0; background:rgba(0,0,0,0.4); z-index:1010; }
        .sidebar-overlay.ativo { display:block; }
        .conteudo-principal { margin-top:60px; margin-left:var(--sidebar-larg); padding:25px; transition:margin-left 0.3s ease; min-height:calc(100vh - 60px); }
        .conteudo-principal.expandido { margin-left:0; }
        @media (max-width:991.98px) { .sidebar { transform:translateX(calc(var(--sidebar-larg) * -1)); } .sidebar.aberta { transform:translateX(0); box-shadow:2px 0 12px rgba(0,0,0,0.15); } .conteudo-principal { margin-left:0; } }
        .page-header { display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px; margin-bottom:22px; }
        .page-header h2 { font-size:1.4rem; font-weight:700; color:var(--azul-escuro); margin:0; display:flex; align-items:center; gap:10px; }
        .page-header h2 i, .card-pagina .card-titulo i { color:var(--azul-primario); }
        .card-pagina { background:#fff; border-radius:12px; box-shadow:0 2px 10px rgba(0,0,0,0.05); border:1px solid var(--cinza-borda); padding:20px 24px; margin-bottom:20px; }
        .card-pagina .card-titulo { font-weight:600; font-size:0.95rem; color:var(--azul-escuro); margin-bottom:16px; display:flex; align-items:center; gap:8px; }
        .tabela-medicos { width:100%; border-collapse:separate; border-spacing:0; font-size:0.88rem; }
        .tabela-medicos thead th { background:var(--azul-claro); color:var(--azul-escuro); font-weight:600; padding:10px 14px; border-bottom:2px solid var(--cinza-borda); white-space:nowrap; }
        .tabela-medicos tbody tr:hover { background:#f8fbff; }
        .tabela-medicos tbody td { padding:10px 14px; border-bottom:1px solid var(--cinza-borda); vertical-align:middle; }
        .badge-status { display:inline-block; padding:4px 10px; border-radius:20px; font-size:0.78rem; font-weight:600; }
        .badge-ativo { background:#d1e7dd; color:#0a3622; }
        .badge-inativo { background:#f8d7da; color:#58151c; }
        .avatar-medico { width:34px; height:34px; border-radius:50%; background:var(--azul-claro); color:var(--azul-primario); display:inline-flex; align-items:center; justify-content:center; font-weight:700; font-size:0.82rem; margin-right:8px; flex-shrink:0; }
        .modal-form .modal-header { background:var(--azul-primario); color:#fff; }
        .modal-form .modal-header .btn-close { filter:invert(1); }
        .modal-form label, .card-pagina label { font-weight:500; font-size:0.88rem; margin-bottom:4px; }
    </style>
</head>
<body>
    <nav class="navbar-topo d-flex align-items-center justify-content-between px-3">
        <div class="d-flex align-items-center gap-2">
            <button class="btn-sanduiche" id="btnSanduiche" title="Menu"><i class="fa-solid fa-bars"></i></button>
            <a class="navbar-brand mb-0 d-flex align-items-center" href="principal.php"><i class="fa-solid fa-stethoscope"></i><span>MediAgenda</span></a>
        </div>
        <div class="dropdown">
            <button class="operador-toggle" type="button" id="dropdownOperador" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fa-solid fa-circle-user"></i><span class="d-none d-md-inline"><?php echo h($operadorNome) ?></span><i class="fa-solid fa-chevron-down" style="font-size:0.75rem;"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end dropdown-menu-operador" aria-labelledby="dropdownOperador">
                <li><a class="dropdown-item" href="#"><i class="fa-solid fa-user"></i><?php echo h($operadorNome) ?></a></li>
                <li><a class="dropdown-item" href="#"><i class="fa-solid fa-envelope"></i><?php echo h($operadorEmail) ?></a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="logout.php"><i class="fa-solid fa-right-from-bracket"></i>Sair</a></li>
            </ul>
        </div>
    </nav>

    <aside class="sidebar" id="sidebar">
        <ul class="nav flex-column">
            <li class="nav-item"><a class="nav-link" href="principal.php"><i class="fa-solid fa-calendar-days"></i> Calendario</a></li>
            <li class="nav-item"><a class="nav-link" href="cadastro_agendas.php"><i class="fa-solid fa-calendar-plus"></i> Agendamentos</a></li>
            <li class="nav-item"><a class="nav-link ativo" href="cadastro_medicos.php"><i class="fa-solid fa-user-doctor"></i> Cadastro de Medicos</a></li>
            <li class="nav-item"><a class="nav-link" href="cadastro_especialidades.php"><i class="fa-solid fa-list-check"></i> Cadastro de Especialidades</a></li>
        </ul>
    </aside>
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <main class="conteudo-principal" id="conteudoPrincipal">
        <div class="page-header">
            <h2><i class="fa-solid fa-user-doctor"></i> Cadastro de Medicos</h2>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalFormMedico"><i class="fa-solid fa-plus me-1"></i> Novo Medico</button>
        </div>

        <?php if ($mensagem !== ''): ?>
            <div class="alert alert-<?php echo $tipoMensagem === 'erro' ? 'danger' : 'success' ?> alert-dismissible fade show" role="alert">
                <?php echo h($mensagem) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
            </div>
        <?php endif; ?>

        <div class="card-pagina">
            <div class="card-titulo"><i class="fa-solid fa-magnifying-glass"></i> Filtros</div>
            <form method="GET" action="cadastro_medicos.php">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label for="filtroNome">Nome</label>
                        <input type="text" class="form-control form-control-sm" id="filtroNome" name="nome" placeholder="Nome do medico" value="<?php echo h($filtroNome) ?>">
                    </div>
                    <div class="col-md-4">
                        <label for="filtroEspecialidade">Especialidade</label>
                        <select class="form-select form-select-sm" id="filtroEspecialidade" name="especialidade">
                            <option value="">Todas</option>
                            <?php foreach ($especialidades as $esp): ?>
                                <option value="<?php echo (int)$esp['id'] ?>" <?php echo ($filtroEspecialidade !== '' && (int)$filtroEspecialidade === (int)$esp['id']) ? 'selected' : '' ?>>
                                    <?php echo h($esp['nome']) ?><?php echo $esp['status'] === 'Inativo' ? ' (Inativa)' : '' ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="filtroStatus">Status</label>
                        <select class="form-select form-select-sm" id="filtroStatus" name="status">
                            <option value="">Todos</option>
                            <option value="Ativo" <?php echo $filtroStatus === 'Ativo' ? 'selected' : '' ?>>Ativo</option>
                            <option value="Inativo" <?php echo $filtroStatus === 'Inativo' ? 'selected' : '' ?>>Inativo</option>
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end gap-2">
                        <button type="submit" class="btn btn-primary btn-sm"><i class="fa-solid fa-magnifying-glass me-1"></i> Filtrar</button>
                        <a href="cadastro_medicos.php" class="btn btn-outline-secondary btn-sm"><i class="fa-solid fa-xmark me-1"></i> Limpar</a>
                    </div>
                </div>
            </form>
        </div>

        <div class="card-pagina">
            <div class="card-titulo d-flex justify-content-between align-items-center">
                <span><i class="fa-solid fa-table-list"></i> Medicos</span>
                <span class="text-muted" style="font-size:0.82rem; font-weight:400;"><?php echo count($medicos) ?> registro(s) encontrado(s)</span>
            </div>
            <div class="table-responsive">
                <table class="tabela-medicos">
                    <thead>
                        <tr>
                            <th>#</th><th>Nome</th><th>CRM</th><th>Especialidade</th><th>Telefone</th><th>E-mail</th><th>Status</th><th class="text-center">Acoes</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($medicos)): ?>
                            <tr><td colspan="8" class="text-center text-muted py-4"><i class="fa-solid fa-user-xmark me-2"></i>Nenhum medico encontrado.</td></tr>
                        <?php else: ?>
                            <?php foreach ($medicos as $med):
                                $partesNome = preg_split('/\s+/', str_replace(array('Dr.', 'Dra.'), '', $med['nome']));
                                $iniciais = '';
                                foreach ($partesNome as $parte) {
                                    if ($parte !== '') {
                                        $iniciais .= strtoupper(substr($parte, 0, 1));
                                    }
                                    if (strlen($iniciais) >= 2) break;
                                }
                                $iniciais = $iniciais ?: strtoupper(substr($med['nome'], 0, 1));
                                $classeBadge = $med['status'] === 'Ativo' ? 'badge-ativo' : 'badge-inativo';
                            ?>
                                <tr>
                                    <td class="text-muted"><?php echo (int)$med['id'] ?></td>
                                    <td><div class="d-flex align-items-center"><span class="avatar-medico"><?php echo h($iniciais) ?></span><?php echo h($med['nome']) ?></div></td>
                                    <td><?php echo h($med['crm']) ?></td>
                                    <td><?php echo h($med['especialidade']) ?></td>
                                    <td><?php echo h($med['telefone']) ?></td>
                                    <td><?php echo h($med['email']) ?></td>
                                    <td><span class="badge-status <?php echo $classeBadge ?>"><?php echo h($med['status']) ?></span></td>
                                    <td class="text-center" style="white-space:nowrap;">
                                        <button class="btn btn-sm btn-outline-primary py-0 px-2 btn-editar" title="Editar"
                                                data-id="<?php echo (int)$med['id'] ?>"
                                                data-nome="<?php echo h($med['nome']) ?>"
                                                data-crm="<?php echo h($med['crm']) ?>"
                                                data-especialidade-id="<?php echo (int)$med['especialidade_id'] ?>"
                                                data-telefone="<?php echo h($med['telefone']) ?>"
                                                data-email="<?php echo h($med['email']) ?>"
                                                data-status="<?php echo h($med['status']) ?>">
                                            <i class="fa-solid fa-pen"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-warning py-0 px-2 btn-inativar" title="Inativar" data-id="<?php echo (int)$med['id'] ?>" data-nome="<?php echo h($med['nome']) ?>">
                                            <i class="fa-solid fa-user-slash"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger py-0 px-2 btn-excluir" title="Excluir definitivamente" data-id="<?php echo (int)$med['id'] ?>" data-nome="<?php echo h($med['nome']) ?>">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <div class="modal fade modal-form" id="modalFormMedico" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalFormTitulo"><i class="fa-solid fa-user-plus me-2"></i>Novo Medico</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <form id="formMedico" action="cadastro_medicos.php" method="POST">
                    <input type="hidden" name="acao" id="formAcao" value="novo">
                    <input type="hidden" name="id" id="formId" value="">
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-8">
                                <label for="formNome">Nome completo <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="formNome" name="nome" placeholder="Ex: Dr. Carlos Lima" required>
                            </div>
                            <div class="col-md-4">
                                <label for="formCrm">CRM <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="formCrm" name="crm" placeholder="Ex: CRM/SP 12345" required>
                            </div>
                            <div class="col-md-6">
                                <label for="formEspecialidade">Especialidade <span class="text-danger">*</span></label>
                                <select class="form-select" id="formEspecialidade" name="especialidade_id" required>
                                    <option value="">Selecione...</option>
                                    <?php foreach ($especialidades as $esp): ?>
                                        <option value="<?php echo (int)$esp['id'] ?>"><?php echo h($esp['nome']) ?><?php echo $esp['status'] === 'Inativo' ? ' (Inativa)' : '' ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="formTelefone">Telefone</label>
                                <input type="text" class="form-control" id="formTelefone" name="telefone" placeholder="(00) 00000-0000">
                            </div>
                            <div class="col-md-8">
                                <label for="formEmail">E-mail</label>
                                <input type="email" class="form-control" id="formEmail" name="email" placeholder="medico@clinica.com">
                            </div>
                            <div class="col-md-4">
                                <label for="formStatus">Status</label>
                                <select class="form-select" id="formStatus" name="status">
                                    <option value="Ativo">Ativo</option>
                                    <option value="Inativo">Inativo</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk me-1"></i> Salvar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <form id="formAcaoLinha" action="cadastro_medicos.php" method="POST" class="d-none">
        <input type="hidden" name="acao" id="acaoLinha">
        <input type="hidden" name="id" id="idLinha">
    </form>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        var btnSanduiche = document.getElementById('btnSanduiche');
        var sidebar = document.getElementById('sidebar');
        var conteudoPrincipal = document.getElementById('conteudoPrincipal');
        var sidebarOverlay = document.getElementById('sidebarOverlay');
        btnSanduiche.addEventListener('click', function() {
            if (window.innerWidth <= 991.98) {
                sidebar.classList.toggle('aberta');
                sidebarOverlay.classList.toggle('ativo');
            } else {
                sidebar.classList.toggle('oculta');
                conteudoPrincipal.classList.toggle('expandido');
            }
        });
        sidebarOverlay.addEventListener('click', function() {
            sidebar.classList.remove('aberta');
            sidebarOverlay.classList.remove('ativo');
        });
        window.addEventListener('resize', function() {
            if (window.innerWidth > 991.98) {
                sidebar.classList.remove('aberta');
                sidebarOverlay.classList.remove('ativo');
            }
        });

        var modalFormMedicoEl = document.getElementById('modalFormMedico');
        var modalFormMedico = new bootstrap.Modal(modalFormMedicoEl);
        var modoEdicao = false;
        modalFormMedicoEl.addEventListener('show.bs.modal', function() {
            if (!modoEdicao) {
                document.getElementById('modalFormTitulo').innerHTML = '<i class="fa-solid fa-user-plus me-2"></i>Novo Medico';
                document.getElementById('formAcao').value = 'novo';
                document.getElementById('formId').value = '';
                document.getElementById('formMedico').reset();
            }
            modoEdicao = false;
        });

        document.querySelector('.tabela-medicos').addEventListener('click', function(e) {
            var btnEditar = e.target.closest('.btn-editar');
            var btnInativar = e.target.closest('.btn-inativar');
            var btnExcluir = e.target.closest('.btn-excluir');

            if (btnEditar) {
                modoEdicao = true;
                document.getElementById('modalFormTitulo').innerHTML = '<i class="fa-solid fa-pen me-2"></i>Editar Medico';
                document.getElementById('formAcao').value = 'editar';
                document.getElementById('formId').value = btnEditar.dataset.id;
                document.getElementById('formNome').value = btnEditar.dataset.nome;
                document.getElementById('formCrm').value = btnEditar.dataset.crm;
                document.getElementById('formEspecialidade').value = btnEditar.dataset.especialidadeId;
                document.getElementById('formTelefone').value = btnEditar.dataset.telefone;
                document.getElementById('formEmail').value = btnEditar.dataset.email;
                document.getElementById('formStatus').value = btnEditar.dataset.status;
                modalFormMedico.show();
            }

            if (btnInativar || btnExcluir) {
                var acao = btnInativar ? 'inativar' : 'excluir';
                var botao = btnInativar || btnExcluir;
                Swal.fire({
                    title: acao === 'inativar' ? 'Inativar medico?' : 'Excluir medico?',
                    html: 'Confirma a acao para <strong>' + botao.dataset.nome + '</strong>?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: acao === 'inativar' ? '#ffc107' : '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: acao === 'inativar' ? 'Sim, inativar' : 'Sim, excluir',
                    cancelButtonText: 'Voltar'
                }).then(function(result) {
                    if (result.isConfirmed) {
                        document.getElementById('acaoLinha').value = acao;
                        document.getElementById('idLinha').value = botao.dataset.id;
                        document.getElementById('formAcaoLinha').submit();
                    }
                });
            }
        });
    </script>
</body>
</html>
