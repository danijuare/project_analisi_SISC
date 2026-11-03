<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$sesionActiva = isset($_SESSION["usuario_id"]);
$urlPanel = $sesionActiva ? (($_SESSION["tipo_usuario"] ?? '') === 'ciudadano' ? 'inicio_ciudadano.php' : 'bandeja_funcionario.php') : 'login.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SISC - Sistema Interinstitucional de Servicios Ciudadanos</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="bg-slate-50 min-h-screen flex flex-col justify-between font-sans text-slate-800">

    <header class="bg-[#1d4289] text-white py-4 px-6 md:px-8 flex justify-between items-center shadow-md">
        <div class="flex items-center space-x-3">
            <span class="font-black text-2xl tracking-widest">SISC</span>
            <span class="text-xs text-blue-200 hidden sm:inline border-l border-blue-700 pl-3 uppercase tracking-wider">Gobierno de la República de Guatemala</span>
        </div>
        <a href="<?php echo $urlPanel; ?>" class="bg-white text-blue-950 font-bold px-5 py-2 rounded-xl text-xs hover:bg-blue-50 transition-all shadow">
            <?php echo $sesionActiva ? 'Ir a mi Panel (' . htmlspecialchars($_SESSION["username"]) . ')' : 'Iniciar Sesión'; ?>
        </a>
    </header>

    <main class="max-w-5xl mx-auto px-6 py-16 text-center space-y-8 flex-grow flex flex-col justify-center">
        <div class="inline-block bg-blue-100 text-blue-900 px-4 py-1.5 rounded-full text-xs font-semibold uppercase tracking-wider">
            Portal Unificado v1.0
        </div>
        <h1 class="text-4xl sm:text-5xl font-black text-slate-900 leading-tight">
            Sistema Interinstitucional de Servicios Ciudadanos
        </h1>
        <p class="text-lg text-slate-600 max-w-2xl mx-auto">
            Plataforma centralizada para gestionar trámites, pagos y expedientes ante RENAP, SAT, IGSS, IRTRA y Transmetro sin necesidad de presencialidad.
        </p>

        <div class="grid grid-cols-2 md:grid-cols-5 gap-4 pt-6">
            <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm font-bold text-slate-700 flex flex-col items-center gap-1">
                <i class="fa-solid fa-id-card text-blue-900 text-xl"></i>
                <span>RENAP</span>
            </div>
            <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm font-bold text-slate-700 flex flex-col items-center gap-1">
                <i class="fa-solid fa-receipt text-blue-900 text-xl"></i>
                <span>SAT</span>
            </div>
            <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm font-bold text-slate-700 flex flex-col items-center gap-1">
                <i class="fa-solid fa-hospital-user text-blue-900 text-xl"></i>
                <span>IGSS</span>
            </div>
            <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm font-bold text-slate-700 flex flex-col items-center gap-1">
                <i class="fa-solid fa-tree text-blue-900 text-xl"></i>
                <span>IRTRA</span>
            </div>
            <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm font-bold text-slate-700 flex flex-col items-center gap-1">
                <i class="fa-solid fa-bus text-blue-900 text-xl"></i>
                <span>Transmetro</span>
            </div>
        </div>

        <div class="pt-6">
            <a href="<?php echo $urlPanel; ?>" class="inline-block bg-[#1d4289] hover:bg-blue-900 text-white font-bold text-base px-8 py-3.5 rounded-2xl shadow-lg transition-all">
                <?php echo $sesionActiva ? 'Ingresar a mi Panel' : 'Ingresar al Sistema'; ?>
            </a>
        </div>
    </main>

    <footer class="bg-white border-t border-slate-200 text-center py-4 text-xs text-slate-500">
        República de Guatemala • Proyecto SISC © 2026
    </footer>

</body>
</html>
