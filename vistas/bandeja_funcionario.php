<?php
require_once "validar_sesion.php";
verificarAcceso(['FUNCIONARIO', 'ADMINISTRADOR']);

$u = usuarioActual();
$esAdmin = ($u["rol_nombre"] === 'ADMINISTRADOR');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal Institucional | SISC</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="bg-slate-100 min-h-screen flex flex-col font-sans text-slate-800">

    <!-- Navbar Superior -->
    <header class="bg-slate-900 text-white shadow-lg sticky top-0 z-40 border-b border-slate-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            
            <!-- Logo y Marca -->
            <div class="flex items-center space-x-3">
                <span class="text-2xl font-black tracking-widest text-blue-400">SISC</span>
                <span class="text-[10px] bg-slate-800 text-slate-300 border border-slate-700 px-2.5 py-0.5 rounded-full font-bold">
                    <i class="fa-solid fa-building-columns text-blue-400 me-1"></i> <?php echo htmlspecialchars($u["institucion_nombre"]); ?>
                </span>
            </div>

            <!-- Navegación según Rol -->
            <nav class="hidden md:flex items-center space-x-1 text-xs">
                <a href="bandeja_funcionario.php" class="px-3 py-2 rounded-lg bg-slate-800 text-white font-semibold flex items-center gap-1.5">
                    <i class="fa-solid fa-inbox text-blue-400"></i> Bandeja
                </a>

                <?php if ($esAdmin): ?>
                <a href="usuarios.php" class="px-3 py-2 rounded-lg text-slate-300 hover:text-white hover:bg-slate-800 transition-all font-semibold flex items-center gap-1.5">
                    <i class="fa-solid fa-users-gear text-emerald-400"></i> Usuarios y Roles
                </a>
                <a href="#" onclick="alert('Módulo de Instituciones del Estado'); return false;" class="px-3 py-2 rounded-lg text-slate-300 hover:text-white hover:bg-slate-800 transition-all font-semibold flex items-center gap-1.5">
                    <i class="fa-solid fa-landmark text-amber-400"></i> Instituciones
                </a>
                <a href="#" onclick="alert('Bitácora de Auditoría del Sistema'); return false;" class="px-3 py-2 rounded-lg text-slate-300 hover:text-white hover:bg-slate-800 transition-all font-semibold flex items-center gap-1.5">
                    <i class="fa-solid fa-shield-halved text-purple-400"></i> Auditoría
                </a>
                <?php endif; ?>
            </nav>

            <!-- Datos de Usuario y Salir -->
            <div class="flex items-center space-x-4">
                <div class="text-right hidden sm:block">
                    <p class="text-xs font-bold text-white"><?php echo htmlspecialchars($u["nombre_completo"]); ?></p>
                    <span class="text-[10px] uppercase font-mono font-bold px-2 py-0.5 rounded <?php echo $esAdmin ? 'bg-amber-500/20 text-amber-300 border border-amber-500/30' : 'bg-blue-500/20 text-blue-300 border border-blue-500/30'; ?>">
                        <?php echo htmlspecialchars($u["rol_nombre"]); ?>
                    </span>
                </div>
                <a href="../ajax/usuario.php?op=salir" class="text-xs bg-rose-600 hover:bg-rose-700 text-white font-semibold px-3 py-1.5 rounded-lg transition-all flex items-center gap-1.5 shadow-sm">
                    <i class="fa-solid fa-right-from-bracket"></i>
                    <span class="hidden sm:inline">Cerrar Sesión</span>
                </a>
            </div>

        </div>
    </header>

    <!-- Contenido Principal -->
    <main class="flex-grow max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 w-full space-y-6">

        <!-- Banner de Bienvenida -->
        <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="space-y-2">
                <div class="inline-flex items-center gap-2 bg-blue-50 text-blue-900 border border-blue-200 text-xs px-3 py-1 rounded-full font-bold">
                    <i class="fa-solid fa-shield-cat"></i> Sesión Institucional Autenticada
                </div>
                <h1 class="text-2xl sm:text-3xl font-black text-slate-900">
                    Bandeja Operativa: <?php echo htmlspecialchars($u["institucion_nombre"]); ?>
                </h1>
                <p class="text-xs text-slate-500 max-w-2xl">
                    Has ingresado como <strong><?php echo htmlspecialchars($u["rol_nombre"]); ?></strong>. Administra las solicitudes, valida expedientes y gestiona los permisos interinstitucionales del SISC.
                </p>
            </div>

            <!-- Accesos rápidos según rol -->
            <div class="flex flex-wrap gap-2">
                <?php if ($esAdmin): ?>
                <a href="usuarios.php" class="bg-[#1d4289] hover:bg-blue-900 text-white text-xs font-bold px-4 py-2.5 rounded-xl shadow transition-all flex items-center gap-2">
                    <i class="fa-solid fa-user-plus"></i> Gestionar Usuarios
                </a>
                <?php endif; ?>
                <button onclick="alert('Actualizando bandeja de solicitudes...');" class="bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold px-4 py-2.5 rounded-xl transition-all border border-slate-300 flex items-center gap-2">
                    <i class="fa-solid fa-rotate-right"></i> Refrescar
                </button>
            </div>
        </div>

        <!-- Métricas Rápidas -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            
            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
                <div>
                    <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Trámites Pendientes</span>
                    <h3 class="text-2xl font-black text-slate-900 mt-1">4</h3>
                    <span class="text-[11px] text-amber-600 font-medium">Requieren tu atención</span>
                </div>
                <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-xl">
                    <i class="fa-solid fa-clock"></i>
                </div>
            </div>

            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
                <div>
                    <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Aprobados Hoy</span>
                    <h3 class="text-2xl font-black text-slate-900 mt-1">12</h3>
                    <span class="text-[11px] text-emerald-600 font-medium">+15% vs ayer</span>
                </div>
                <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl">
                    <i class="fa-solid fa-circle-check"></i>
                </div>
            </div>

            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
                <div>
                    <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Documentos x Validar</span>
                    <h3 class="text-2xl font-black text-slate-900 mt-1">3</h3>
                    <span class="text-[11px] text-blue-600 font-medium">Con firma electrónica</span>
                </div>
                <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl">
                    <i class="fa-solid fa-signature"></i>
                </div>
            </div>

            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
                <div>
                    <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Integración ESB</span>
                    <h3 class="text-2xl font-black text-emerald-600 mt-1">ONLINE</h3>
                    <span class="text-[11px] text-slate-400 font-mono">Latencia: 42ms</span>
                </div>
                <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl">
                    <i class="fa-solid fa-network-wired"></i>
                </div>
            </div>

        </div>

        <!-- Módulos de Funcionario vs Administrador -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            
            <!-- Módulo Bandeja de Trabajo -->
            <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-sm space-y-4">
                <div class="w-10 h-10 rounded-xl bg-blue-100 text-blue-900 flex items-center justify-center text-lg">
                    <i class="fa-solid fa-list-check"></i>
                </div>
                <h3 class="font-bold text-slate-900 text-base">Revisión de Solicitudes</h3>
                <p class="text-xs text-slate-500 leading-relaxed">
                    Accede a los expedientes iniciados por ciudadanos que corresponden a la institución <strong><?php echo htmlspecialchars($u["institucion_nombre"]); ?></strong> para aprobar, rechazar o solicitar corrección.
                </p>
                <button onclick="alert('Cargando expedientes de ventanilla institucional...');" class="w-full py-2.5 bg-blue-900 hover:bg-blue-950 text-white font-bold rounded-xl text-xs transition-all">
                    Abrir Ventanilla Institucional
                </button>
            </div>

            <!-- Módulo Gestión de Usuarios y Roles (Especial para Administrador) -->
            <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-sm space-y-4 relative overflow-hidden">
                <?php if (!$esAdmin): ?>
                <div class="absolute inset-0 bg-slate-50/80 backdrop-blur-[2px] z-10 flex flex-col items-center justify-center p-6 text-center">
                    <i class="fa-solid fa-lock text-slate-400 text-2xl mb-2"></i>
                    <p class="text-xs font-bold text-slate-700">Módulo Restringido</p>
                    <p class="text-[11px] text-slate-500 mt-1">Exclusivo para rol ADMINISTRADOR</p>
                </div>
                <?php endif; ?>

                <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-800 flex items-center justify-center text-lg">
                    <i class="fa-solid fa-users"></i>
                </div>
                <h3 class="font-bold text-slate-900 text-base">Gestión de Usuarios y Roles</h3>
                <p class="text-xs text-slate-500 leading-relaxed">
                    Crear nuevos funcionarios o administradores, asignar roles institucionales, activar o suspender credenciales ciudadanas.
                </p>
                <a href="usuarios.php" class="block text-center w-full py-2.5 bg-emerald-700 hover:bg-emerald-800 text-white font-bold rounded-xl text-xs transition-all">
                    Ir a Gestión de Usuarios
                </a>
            </div>

            <!-- Módulo Firma Digital y ESB -->
            <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-sm space-y-4">
                <div class="w-10 h-10 rounded-xl bg-purple-100 text-purple-800 flex items-center justify-center text-lg">
                    <i class="fa-solid fa-file-shield"></i>
                </div>
                <h3 class="font-bold text-slate-900 text-base">Firma Electrónica y ESB</h3>
                <p class="text-xs text-slate-500 leading-relaxed">
                    Emisión de resoluciones con certificado digital PKI y comunicación automática mediante el Bus de Servicios Empresariales (ESB).
                </p>
                <button onclick="alert('Módulo de firmas electrónicas avanzadas PKI');" class="w-full py-2.5 bg-purple-700 hover:bg-purple-800 text-white font-bold rounded-xl text-xs transition-all">
                    Configurar Llave PKI
                </button>
            </div>

        </div>

        <!-- Tabla de Solicitudes en Bandeja -->
        <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-sm space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="font-bold text-slate-900 text-base flex items-center gap-2">
                        <i class="fa-solid fa-layer-group text-blue-900"></i> Solicitudes Recibidas en <?php echo htmlspecialchars($u["institucion_nombre"]); ?>
                    </h3>
                    <p class="text-xs text-slate-500">Expedientes entrantes pendientes de resolución</p>
                </div>
                <span class="text-xs font-semibold bg-slate-100 text-slate-600 px-3 py-1 rounded-full">
                    Cola de Atención
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-xs text-left">
                    <thead class="bg-slate-50 text-slate-500 font-semibold uppercase tracking-wider border-b border-slate-200">
                        <tr>
                            <th class="py-3 px-4">No. Seguimiento</th>
                            <th class="py-3 px-4">Ciudadano</th>
                            <th class="py-3 px-4">CUI</th>
                            <th class="py-3 px-4">Trámite</th>
                            <th class="py-3 px-4">Prioridad</th>
                            <th class="py-3 px-4">Estado</th>
                            <th class="py-3 px-4 text-right">Acción</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr class="hover:bg-slate-50 transition-all">
                            <td class="py-3.5 px-4 font-mono font-bold text-blue-900">SISC-TR-2026-089</td>
                            <td class="py-3.5 px-4 font-semibold">Carlos Roberto Morales Gómez</td>
                            <td class="py-3.5 px-4 font-mono">1234567890101</td>
                            <td class="py-3.5 px-4">Emisión de Certificado de Registro</td>
                            <td class="py-3.5 px-4">
                                <span class="bg-blue-100 text-blue-800 font-bold px-2 py-0.5 rounded text-[10px]">NORMAL</span>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="bg-amber-100 text-amber-800 font-bold px-2.5 py-1 rounded-full text-[10px]">
                                    EN REVISIÓN
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-right space-x-1">
                                <button onclick="alert('Aprobando solicitud SISC-TR-2026-089 con firma institucional...');" class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold px-2.5 py-1 rounded text-[10px]">Aprobar</button>
                                <button onclick="alert('Solicitud enviada a corrección');" class="bg-slate-200 hover:bg-slate-300 text-slate-700 font-bold px-2.5 py-1 rounded text-[10px]">Observar</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-slate-200 py-4 text-center text-xs text-slate-500 mt-auto">
        República de Guatemala • Sistema Interinstitucional de Servicios Ciudadanos (SISC) © 2026
    </footer>

</body>
</html>

