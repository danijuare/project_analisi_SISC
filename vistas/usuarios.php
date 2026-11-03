<?php
require_once "validar_sesion.php";
verificarAcceso(['ADMINISTRADOR']);

$u = usuarioActual();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Usuarios y Roles | SISC</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="bg-slate-100 min-h-screen flex flex-col font-sans text-slate-800">

    <!-- Header / Navbar Superior -->
    <header class="bg-slate-900 text-white shadow-lg sticky top-0 z-40 border-b border-slate-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <a href="bandeja_funcionario.php" class="text-2xl font-black tracking-widest text-blue-400 hover:text-blue-300">SISC</a>
                <span class="text-[10px] bg-slate-800 text-slate-300 border border-slate-700 px-2.5 py-0.5 rounded-full font-bold">
                    Panel de Administración
                </span>
            </div>

            <nav class="flex items-center space-x-2 text-xs">
                <a href="bandeja_funcionario.php" class="px-3 py-2 rounded-lg text-slate-300 hover:text-white hover:bg-slate-800 transition-all font-semibold flex items-center gap-1.5">
                    <i class="fa-solid fa-arrow-left"></i> Volver a Bandeja
                </a>
                <a href="../ajax/usuario.php?op=salir" class="bg-rose-600 hover:bg-rose-700 text-white font-semibold px-3 py-1.5 rounded-lg transition-all flex items-center gap-1.5">
                    <i class="fa-solid fa-right-from-bracket"></i> Salir
                </a>
            </nav>
        </div>
    </header>

    <!-- Contenido Principal -->
    <main class="flex-grow max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 w-full space-y-6">

        <!-- Encabezado con Botón Crear -->
        <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <div class="inline-flex items-center gap-2 bg-emerald-50 text-emerald-800 border border-emerald-200 text-xs px-3 py-1 rounded-full font-bold mb-2">
                    <i class="fa-solid fa-users-gear"></i> Control de Acceso Basado en Roles (RBAC)
                </div>
                <h1 class="text-2xl sm:text-3xl font-black text-slate-900">
                    Administración de Usuarios y Roles
                </h1>
                <p class="text-xs text-slate-500 mt-1">
                    Crea funcionarios, asigna roles institucionales (RENAP, SAT, IGSS, etc.) o gestiona las cuentas de ciudadanos.
                </p>
            </div>

            <button type="button" id="btnNuevoUsuario" class="bg-[#1d4289] hover:bg-blue-900 text-white text-xs font-bold px-5 py-3 rounded-2xl shadow-md transition-all flex items-center justify-center gap-2">
                <i class="fa-solid fa-user-plus text-sm"></i>
                <span>Crear Nuevo Usuario</span>
            </button>
        </div>

        <!-- Alerta de Respuesta General -->
        <div id="alertaGeneral" class="hidden text-xs py-3 px-4 rounded-2xl font-semibold transition-all"></div>

        <!-- Tabla de Usuarios -->
        <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-sm space-y-4">
            
            <!-- Barra de Búsqueda y Filtros -->
            <div class="flex flex-col sm:flex-row items-center justify-between gap-3">
                <div class="relative w-full sm:w-80">
                    <input type="text" id="buscador" placeholder="Buscar por usuario, nombre, rol..."
                        class="w-full pl-9 pr-4 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-blue-900 outline-none">
                    <span class="absolute left-3 top-2.5 text-slate-400 text-xs">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </span>
                </div>

                <div class="text-xs text-slate-500">
                    Total registrados: <strong id="totalUsuarios" class="text-slate-800">0</strong>
                </div>
            </div>

            <!-- Tabla -->
            <div class="overflow-x-auto">
                <table class="w-full text-xs text-left">
                    <thead class="bg-slate-50 text-slate-500 font-semibold uppercase tracking-wider border-b border-slate-200">
                        <tr>
                            <th class="py-3 px-4">ID</th>
                            <th class="py-3 px-4">Usuario</th>
                            <th class="py-3 px-4">Nombre / CUI</th>
                            <th class="py-3 px-4">Tipo</th>
                            <th class="py-3 px-4">Institución</th>
                            <th class="py-3 px-4">Rol Asignado</th>
                            <th class="py-3 px-4">Estado</th>
                            <th class="py-3 px-4 text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tbodyUsuarios" class="divide-y divide-slate-100">
                        <tr>
                            <td colspan="8" class="text-center py-8 text-slate-400">
                                <i class="fa-solid fa-spinner fa-spin text-lg mb-2"></i>
                                <p>Cargando usuarios del sistema...</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

        </div>

    </main>

    <!-- ======================================================== -->
    <!-- MODAL CREAR / EDITAR USUARIO -->
    <!-- ======================================================== -->
    <div id="modalUsuario" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-3 hidden">
        <div class="bg-white w-full max-w-xl rounded-3xl shadow-2xl overflow-hidden border border-slate-200 max-h-[90vh] flex flex-col">
            
            <div class="bg-slate-900 text-white p-5 px-6 flex justify-between items-center">
                <div class="flex items-center space-x-3">
                    <div class="w-9 h-9 rounded-xl bg-blue-600 flex items-center justify-center text-white">
                        <i class="fa-solid fa-user-gear"></i>
                    </div>
                    <div>
                        <h3 id="modalTitulo" class="font-bold text-base">Crear Nuevo Usuario</h3>
                        <p class="text-[11px] text-slate-400">Asigna credenciales, institución y rol de acceso</p>
                    </div>
                </div>
                <button type="button" id="btnCerrarModal" class="text-slate-400 hover:text-white text-xl">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <div class="p-6 overflow-y-auto space-y-4">
                
                <form id="formUsuario" class="space-y-4">
                    <input type="hidden" name="usuario_id" id="usuario_id" value="">

                    <!-- Tipo de Usuario y Rol -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Tipo de Usuario *</label>
                            <select name="tipo_usuario" id="tipo_usuario" required
                                class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-blue-900 outline-none">
                                <option value="funcionario">Funcionario Público</option>
                                <option value="administrador">Administrador del Sistema</option>
                                <option value="ciudadano">Ciudadano</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Rol Asignado *</label>
                            <select name="rol_id" id="rol_id" required
                                class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-blue-900 outline-none">
                                <!-- Cargado dinámicamente -->
                            </select>
                        </div>
                    </div>

                    <!-- Institución -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Institución a la que pertenece *</label>
                        <select name="institucion_id" id="institucion_id" required
                            class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-blue-900 outline-none">
                            <!-- Cargado dinámicamente -->
                        </select>
                    </div>

                    <!-- Username y Contraseña -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Nombre de Usuario (Username) *</label>
                            <input type="text" name="username" id="username" placeholder="ej: jlopez_sat" required
                                class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-blue-900 outline-none">
                        </div>
                        <div>
                            <label id="lblPassword" class="block text-xs font-bold text-slate-700 mb-1">Contraseña *</label>
                            <input type="password" name="password" id="password" placeholder="••••••••" required
                                class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-blue-900 outline-none">
                        </div>
                    </div>

                    <!-- Nombres y Apellidos -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Nombres</label>
                            <input type="text" name="nombres" id="nombres" placeholder="Nombres del usuario"
                                class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-blue-900 outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Apellidos</label>
                            <input type="text" name="apellidos" id="apellidos" placeholder="Apellidos del usuario"
                                class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-blue-900 outline-none">
                        </div>
                    </div>

                    <!-- Email y Teléfono -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Correo Institucional</label>
                            <input type="email" name="email" id="email" placeholder="usuario@institucion.gob.gt"
                                class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-blue-900 outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Teléfono</label>
                            <input type="text" name="telefono" id="telefono" placeholder="Ej: 2200-0000"
                                class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-blue-900 outline-none">
                        </div>
                    </div>

                    <!-- Estado -->
                    <div class="flex items-center gap-2 pt-2">
                        <input type="checkbox" name="activo" id="activo" value="1" checked class="rounded text-blue-900">
                        <label for="activo" class="text-xs font-semibold text-slate-700">Usuario Activo</label>
                    </div>

                    <!-- Alerta de Modal -->
                    <div id="modalMsg" class="hidden text-xs py-2 px-3 rounded-xl font-semibold"></div>

                    <!-- Footer Botones -->
                    <div class="flex justify-end space-x-3 pt-3 border-t border-slate-200">
                        <button type="button" id="btnCancelar" class="px-5 py-2.5 rounded-xl border border-slate-300 text-slate-600 text-xs font-bold hover:bg-slate-100 transition-all">
                            Cancelar
                        </button>
                        <button type="submit" id="btnGuardar" class="px-6 py-2.5 rounded-xl bg-blue-900 hover:bg-blue-950 text-white text-xs font-bold transition-all shadow-md flex items-center gap-2">
                            <i class="fa-solid fa-save"></i> Guardar Usuario
                        </button>
                    </div>
                </form>

            </div>

        </div>
    </div>

    <script src="js/usuarios.js"></script>
</body>
</html>

