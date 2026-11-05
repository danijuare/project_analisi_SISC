<?php
if (!function_exists('usuarioActual')) {
    require_once "validar_sesion.php";
}
$user = usuarioActual();
$esAdmin = ($user["rol_nombre"] === 'ADMINISTRADOR');
$paginaActual = basename($_SERVER['PHP_SELF']);
?>
<!-- Navbar Superior Funcionario / Administrador (Diseño Fase 3) -->
<header class="bg-[#1d4289] text-white shadow-md sticky top-0 z-40">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
        
        <!-- Marca y Navegación Institucional -->
        <div class="flex items-center space-x-6">
            <a href="bandeja_funcionario.php" class="flex items-center space-x-2 text-white no-underline">
                <span class="text-2xl font-black tracking-widest">SISC</span>
                <span class="text-[10px] bg-blue-800 text-blue-200 px-2 py-0.5 rounded font-mono font-bold hidden sm:inline">INSTITUCIONAL</span>
            </a>

            <nav class="hidden md:flex items-center space-x-2 text-xs font-semibold">
                <a href="bandeja_funcionario.php" class="px-3 py-1.5 rounded-full transition-all <?php echo in_array($paginaActual, ['bandeja_funcionario.php', 'revisar_solicitud.php']) ? 'bg-blue-900 text-white shadow-inner font-bold' : 'text-blue-100 hover:bg-blue-800 hover:text-white'; ?>">
                    Bandeja
                </a>

                <a href="pagos_recibidos.php" class="px-3 py-1.5 rounded-full transition-all <?php echo ($paginaActual == 'pagos_recibidos.php') ? 'bg-blue-900 text-white shadow-inner font-bold' : 'text-blue-100 hover:bg-blue-800 hover:text-white'; ?>">
                    Pagos
                </a>

                <a href="control_cruzado.php" class="px-3 py-1.5 rounded-full transition-all <?php echo ($paginaActual == 'control_cruzado.php') ? 'bg-blue-900 text-white shadow-inner font-bold' : 'text-blue-100 hover:bg-blue-800 hover:text-white'; ?>">
                    Reportes
                </a>

                <a href="integraciones_esb.php" class="px-3 py-1.5 rounded-full transition-all <?php echo ($paginaActual == 'integraciones_esb.php') ? 'bg-blue-900 text-white shadow-inner font-bold' : 'text-blue-100 hover:bg-blue-800 hover:text-white'; ?>">
                    Integraciones
                </a>

                <?php if ($esAdmin): ?>
                <a href="usuarios.php" class="px-3 py-1.5 rounded-full transition-all <?php echo ($paginaActual == 'usuarios.php') ? 'bg-blue-900 text-white shadow-inner font-bold' : 'text-amber-200 hover:bg-blue-800 hover:text-white'; ?>">
                    <i class="fa-solid fa-users-gear me-1"></i> Usuarios
                </a>
                <?php endif; ?>
            </nav>
        </div>

        <!-- Usuario Institucional, Institución, MFA y Salir -->
        <div class="flex items-center space-x-3 text-xs">
            <div class="hidden sm:flex items-center gap-2 text-blue-100 font-semibold">
                <span><?php echo htmlspecialchars($user["nombre_completo"]); ?></span>
                <span class="text-blue-300">·</span>
                <span class="bg-blue-950/60 text-blue-200 px-2 py-0.5 rounded text-[11px] font-mono border border-blue-400/30">
                    <?php echo htmlspecialchars($user["institucion_codigo"] ?: 'SISC'); ?>
                </span>
                <span class="text-blue-300">·</span>
                <span class="text-emerald-300 flex items-center gap-1 text-[11px]">
                    <i class="fa-solid fa-shield-halved"></i> MFA activo
                </span>
            </div>

            <span class="text-blue-300 hidden sm:inline">·</span>
            <a href="../ajax/usuario.php?op=salir" class="text-blue-200 hover:text-white font-semibold transition-colors flex items-center gap-1">
                <span>Salir</span>
                <i class="fa-solid fa-right-from-bracket text-xs"></i>
            </a>
        </div>
    </div>
</header>

