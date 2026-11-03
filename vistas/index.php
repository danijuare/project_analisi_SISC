<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SISC - Sistema Interinstitucional de Servicios Ciudadanos</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 min-h-screen flex flex-col justify-between font-sans text-slate-800">

    <header class="bg-blue-900 text-white py-4 px-8 flex justify-between items-center shadow-md">
        <div class="flex items-center space-x-3">
            <span class="font-black text-2xl tracking-widest">SISC</span>
            <span class="text-xs text-blue-200 hidden sm:inline border-l border-blue-700 pl-3 uppercase tracking-wider">Gobierno de la República de Guatemala</span>
        </div>
        <a href="login.php" class="bg-white text-blue-950 font-bold px-5 py-2 rounded-lg text-sm hover:bg-blue-50 transition-all shadow">
            Iniciar Sesión
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
            Plataforma centralizada para gestionar trámites, pagos y expedientes ante RENAP, SAT, IGSS, IRTRA y Transmetro sin necesidad de presencialidad[cite: 1, 5].
        </p>

        <div class="grid grid-cols-2 md:grid-cols-5 gap-4 pt-6">
            <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm font-bold text-slate-700">RENAP</div>
            <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm font-bold text-slate-700">SAT</div>
            <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm font-bold text-slate-700">IGSS</div>
            <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm font-bold text-slate-700">IRTRA</div>
            <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm font-bold text-slate-700">Transmetro</div>
        </div>

        <div class="pt-6">
            <a href="login.php" class="inline-block bg-blue-900 hover:bg-blue-950 text-white font-bold text-base px-8 py-3.5 rounded-xl shadow-lg transition-all">
                Ingresar al Sistema
            </a>
        </div>
    </main>

    <footer class="bg-white border-t border-slate-200 text-center py-4 text-xs text-slate-500">
        República de Guatemala • Proyecto SISC © 2026[cite: 1, 5]
    </footer>

</body>
</html>