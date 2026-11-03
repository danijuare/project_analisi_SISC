<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (isset($_SESSION["usuario_id"])) {
    if (($_SESSION["tipo_usuario"] ?? '') === 'ciudadano') {
        header("Location: inicio_ciudadano.php");
        exit();
    } else {
        header("Location: bandeja_funcionario.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SISC - Ingresar | Sistema Interinstitucional de Servicios Ciudadanos</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="min-h-screen bg-slate-100 flex items-center justify-center p-3 md:p-6 font-sans">

    <div class="w-full max-w-5xl min-h-screen md:min-h-[620px] bg-white rounded-none md:rounded-3xl shadow-2xl overflow-hidden grid grid-cols-1 md:grid-cols-12">
        
        <!-- Bloque Izquierdo: Azul Profundo (Mockup SISC) -->
        <div class="md:col-span-5 bg-[#1d4289] text-white p-8 md:p-12 flex flex-col justify-between">
            <div>
                <div class="flex items-center space-x-2 mb-12">
                    <span class="text-3xl font-black tracking-widest">SISC</span>
                    <span class="text-[10px] bg-blue-800 text-blue-200 px-2 py-0.5 rounded font-mono font-bold">GT</span>
                </div>
                
                <h1 class="text-3xl font-extrabold leading-snug mb-6">
                    Tu identidad ciudadana en un solo lugar
                </h1>
                
                <p class="text-sm text-blue-100 mb-6">Sistema Interinstitucional de Servicios Ciudadanos.</p>
                
                <ul class="space-y-4 text-xs text-blue-100 font-normal">
                    <li class="flex items-start">
                        <i class="fa-solid fa-check-circle text-emerald-400 mt-0.5 mr-2"></i>
                        <span>Un solo ingreso para RENAP, SAT, IGSS, IRTRA y Transmetro</span>
                    </li>
                    <li class="flex items-start">
                        <i class="fa-solid fa-id-card text-emerald-400 mt-0.5 mr-2"></i>
                        <span>Credencial electrónica y validación oficial en tu teléfono</span>
                    </li>
                    <li class="flex items-start">
                        <i class="fa-solid fa-file-shield text-emerald-400 mt-0.5 mr-2"></i>
                        <span>Trámites, pagos y seguimiento sin filas ni copias físicas</span>
                    </li>
                </ul>
            </div>

            <!-- Cuentas Demo de Prueba Rápida -->
            <div class="mt-8 bg-blue-950/60 p-4 rounded-2xl border border-blue-400/20 text-xs">
                <span class="font-bold text-amber-300 block mb-1.5 flex items-center">
                    <i class="fa-solid fa-key me-1"></i> Credenciales de Demostración:
                </span>
                <div class="space-y-1 text-[11px] text-blue-200">
                    <div><strong>Ciudadano:</strong> <span class="cursor-pointer underline text-white" onclick="autocompletar('1234567890101', 'ciudadano123', 'ciudadano')">1234567890101</span> / <code>ciudadano123</code></div>
                    <div><strong>Funcionario RENAP:</strong> <span class="cursor-pointer underline text-white" onclick="autocompletar('funcionario_renap', 'renap123', 'funcionario')">funcionario_renap</span> / <code>renap123</code></div>
                    <div><strong>Admin General:</strong> <span class="cursor-pointer underline text-white" onclick="autocompletar('admin', 'admin123', 'funcionario')">admin</span> / <code>admin123</code></div>
                </div>
            </div>

            <div class="text-[11px] text-blue-200 mt-6">
                República de Guatemala · Proyecto SISC © 2026
            </div>
        </div>

        <!-- Bloque Derecho: Formulario Hueso/Gris (Mockup) -->
        <div class="md:col-span-7 bg-[#f6f5ee] p-6 md:p-12 flex flex-col justify-center">
            
            <div class="max-w-md mx-auto w-full space-y-5">
                
                <div>
                    <span class="text-[10px] font-bold tracking-wider text-blue-900 uppercase">SERVICIOS CIUDADANOS</span>
                    <h2 class="text-3xl font-bold text-slate-900 mt-1">Ingresar</h2>
                </div>

                <!-- Tabs Ciudadano / Funcionario -->
                <div class="bg-[#e7e5d8] p-1 rounded-full flex shadow-inner">
                    <button type="button" id="tabCiudadano" class="flex-1 py-2 text-xs font-bold rounded-full bg-white text-blue-950 shadow-sm transition-all flex items-center justify-center gap-1.5">
                        <i class="fa-solid fa-user"></i> Ciudadano
                    </button>
                    <button type="button" id="tabFuncionario" class="flex-1 py-2 text-xs font-semibold rounded-full text-slate-600 hover:text-slate-900 transition-all flex items-center justify-center gap-1.5">
                        <i class="fa-solid fa-briefcase"></i> Funcionario
                    </button>
                </div>

                <!-- Formulario procesado por AJAX -->
                <form id="formLogin" class="space-y-4">
                    <input type="hidden" name="tipo_usuario" id="tipo_usuario" value="ciudadano">

                    <!-- Tarjeta 1: Datos de Acceso -->
                    <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm space-y-3">
                        <div>
                            <label id="lblIdentificacion" class="block text-xs font-semibold text-slate-600 mb-1">CUI (número de DPI)</label>
                            <div class="relative">
                                <input type="text" name="identificacion" id="identificacion" placeholder="2456 78901 0123" required
                                    class="w-full pl-4 pr-10 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-900 focus:bg-white transition-all">
                                <span class="absolute right-3 top-2.5 text-slate-400">
                                    <i id="iconId" class="fa-solid fa-id-card"></i>
                                </span>
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Contraseña</label>
                            <div class="relative">
                                <input type="password" name="password" id="password" placeholder="••••••••" required
                                    class="w-full pl-4 pr-10 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-900 focus:bg-white transition-all">
                                <button type="button" onclick="togglePassVisibility()" class="absolute right-3 top-2.5 text-slate-400 hover:text-slate-700">
                                    <i id="eyeIcon" class="fa-solid fa-eye"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Tarjeta 2: Verificación 2FA -->
                    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm space-y-2">
                        <div class="flex items-center justify-between">
                            <label class="block text-xs font-bold text-slate-800 flex items-center gap-1.5">
                                <i class="fa-solid fa-shield-halved text-blue-900"></i> Verificación en dos pasos
                            </label>
                            <span class="text-[10px] text-slate-400 font-mono">2FA Opcional / Simulado</span>
                        </div>
                        <p class="text-[11px] text-slate-500">Ingresa el código de 6 dígitos de tu app de autenticación (o déjalo en blanco para demo)</p>
                        <div>
                            <input type="text" name="mfa_codigo" id="mfa_codigo" placeholder="482 912" maxlength="6"
                                class="w-full px-4 py-2 bg-slate-50 border border-slate-300 rounded-xl text-sm font-mono tracking-widest text-center focus:outline-none focus:ring-2 focus:ring-blue-900">
                        </div>
                    </div>

                    <!-- Mensaje de respuesta AJAX -->
                    <div id="msgResponse" class="hidden text-center text-xs font-semibold py-2.5 px-3 rounded-xl transition-all"></div>

                    <!-- Botón Ingresar -->
                    <button type="submit" id="btnSubmit" class="w-full py-3 bg-[#1d4289] hover:bg-blue-900 text-white font-bold rounded-2xl text-sm shadow-md transition-all flex items-center justify-center gap-2">
                        <span>Ingresar</span>
                        <i class="fa-solid fa-arrow-right text-xs"></i>
                    </button>
                </form>

                <!-- Registro Ciudadano -->
                <div id="bloqueRegistro" class="text-center space-y-2 pt-1">
                    <button type="button" id="btnAbrirModalRegistro" class="w-full py-2.5 border-2 border-blue-900 text-blue-900 font-bold rounded-2xl text-sm hover:bg-blue-900 hover:text-white transition-all flex items-center justify-center gap-2">
                        <i class="fa-solid fa-user-plus"></i> Registrarme con mi DPI
                    </button>
                    <p class="text-[11px] text-slate-500">Validamos tu identidad ciudadana de forma unificada.</p>
                </div>

                <!-- Nota Funcionario -->
                <div id="notaFuncionario" class="hidden bg-blue-100 text-blue-900 p-3.5 rounded-2xl text-xs border border-blue-200">
                    <div class="flex items-center gap-2 font-bold mb-1">
                        <i class="fa-solid fa-building-columns"></i> Acceso Funcionario / Institucional
                    </div>
                    Ingresa con tu usuario asignado por tu institución (RENAP, SAT, IGSS, IRTRA, Transmetro o SISC).
                </div>

            </div>
        </div>

    </div>

    <!-- ======================================================== -->
    <!-- MODAL DE REGISTRO CIUDADANO (CUI / DPI / RENAP) -->
    <!-- ======================================================== -->
    <div id="modalRegistro" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-3 hidden">
        <div class="bg-white w-full max-w-2xl rounded-3xl shadow-2xl overflow-hidden border border-slate-200 max-h-[90vh] flex flex-col animate-fade-in">
            
            <!-- Header Modal -->
            <div class="bg-[#1d4289] text-white p-5 px-6 flex justify-between items-center">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 rounded-xl bg-blue-800 flex items-center justify-center text-lg">
                        <i class="fa-solid fa-id-card"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-lg leading-tight">Registro de Ciudadano</h3>
                        <p class="text-xs text-blue-200">Sistema Interinstitucional de Servicios Ciudadanos (SISC)</p>
                    </div>
                </div>
                <button type="button" id="btnCerrarModal" class="text-blue-200 hover:text-white text-xl p-1">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <!-- Body Modal (Scrollable Form) -->
            <div class="p-6 overflow-y-auto space-y-4">
                
                <div class="bg-blue-50 border border-blue-200 rounded-xl p-3 text-xs text-blue-900 flex items-start gap-2">
                    <i class="fa-solid fa-circle-info text-blue-600 mt-0.5"></i>
                    <span>Ingresa tu número de <strong>CUI / DPI</strong> y datos oficiales para crear tu identidad ciudadana digital en SISC.</span>
                </div>

                <form id="formRegistroCiudadano" class="space-y-4">
                    
                    <!-- Fila 1: CUI y Fecha Nacimiento -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">CUI (Número de DPI) *</label>
                            <input type="text" name="cui" id="reg_cui" placeholder="13 dígitos (ej: 2456789010123)" required maxlength="20"
                                class="w-full px-3.5 py-2 bg-slate-50 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-blue-900 focus:bg-white outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Fecha de Nacimiento *</label>
                            <input type="date" name="fecha_nacimiento" id="reg_fecha" required
                                class="w-full px-3.5 py-2 bg-slate-50 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-blue-900 focus:bg-white outline-none">
                        </div>
                    </div>

                    <!-- Fila 2: Nombres y Apellidos -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Nombres *</label>
                            <input type="text" name="nombres" id="reg_nombres" placeholder="Nombres completos" required
                                class="w-full px-3.5 py-2 bg-slate-50 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-blue-900 focus:bg-white outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Apellidos *</label>
                            <input type="text" name="apellidos" id="reg_apellidos" placeholder="Apellidos completos" required
                                class="w-full px-3.5 py-2 bg-slate-50 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-blue-900 focus:bg-white outline-none">
                        </div>
                    </div>

                    <!-- Fila 3: Email y Teléfono -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Correo Electrónico *</label>
                            <input type="email" name="email" id="reg_email" placeholder="correo@ejemplo.com" required
                                class="w-full px-3.5 py-2 bg-slate-50 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-blue-900 focus:bg-white outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Teléfono Móvil *</label>
                            <input type="text" name="telefono" id="reg_telefono" placeholder="Ej: 55551234" required
                                class="w-full px-3.5 py-2 bg-slate-50 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-blue-900 focus:bg-white outline-none">
                        </div>
                    </div>

                    <!-- Fila 4: Dirección -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Dirección de Residencia *</label>
                        <input type="text" name="direccion" id="reg_direccion" placeholder="Zona, Municipio, Departamento" required
                            class="w-full px-3.5 py-2 bg-slate-50 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-blue-900 focus:bg-white outline-none">
                    </div>

                    <!-- Fila 5: NIT e IGSS (Opcionales) -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">NIT SAT (Opcional)</label>
                            <input type="text" name="nit" id="reg_nit" placeholder="Ej: 1234567-8"
                                class="w-full px-3.5 py-2 bg-slate-50 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-blue-900 focus:bg-white outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Afiliación IGSS (Opcional)</label>
                            <input type="text" name="numero_afiliacion_igss" id="reg_igss" placeholder="Número de carné IGSS"
                                class="w-full px-3.5 py-2 bg-slate-50 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-blue-900 focus:bg-white outline-none">
                        </div>
                    </div>

                    <!-- Fila 6: Nombre de Usuario y Contraseñas -->
                    <div class="border-t border-slate-200 pt-3">
                        <label class="block text-xs font-bold text-blue-950 uppercase mb-2">Credenciales de Acceso</label>
                        
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                            <div>
                                <label class="block text-xs font-semibold text-slate-600 mb-1">Usuario (Opcional)</label>
                                <input type="text" name="username" id="reg_username" placeholder="Por defecto será tu CUI"
                                    class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-blue-900 focus:bg-white outline-none">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-600 mb-1">Contraseña *</label>
                                <input type="password" name="password" id="reg_password" placeholder="Mínimo 6 caracteres" required
                                    class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-blue-900 focus:bg-white outline-none">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-600 mb-1">Confirmar *</label>
                                <input type="password" name="password_confirm" id="reg_password_confirm" placeholder="Repite contraseña" required
                                    class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-blue-900 focus:bg-white outline-none">
                            </div>
                        </div>
                    </div>

                    <!-- Mensaje en modal -->
                    <div id="modalMsg" class="hidden text-xs py-2 px-3 rounded-xl font-semibold"></div>

                    <!-- Botones footer del modal -->
                    <div class="flex justify-end space-x-3 pt-3 border-t border-slate-200">
                        <button type="button" id="btnCancelarRegistro" class="px-5 py-2.5 rounded-xl border border-slate-300 text-slate-600 text-xs font-bold hover:bg-slate-100 transition-all">
                            Cancelar
                        </button>
                        <button type="submit" id="btnGuardarCiudadano" class="px-6 py-2.5 rounded-xl bg-[#1d4289] hover:bg-blue-900 text-white text-xs font-bold transition-all shadow-md flex items-center gap-2">
                            <i class="fa-solid fa-check"></i> Completar Registro
                        </button>
                    </div>

                </form>

            </div>

        </div>
    </div>

    <script src="js/login.js"></script>
    <script>
        function autocompletar(id, pass, tab) {
            if (tab === 'ciudadano') {
                document.getElementById('tabCiudadano').click();
            } else {
                document.getElementById('tabFuncionario').click();
            }
            document.getElementById('identificacion').value = id;
            document.getElementById('password').value = pass;
        }

        function togglePassVisibility() {
            const pass = document.getElementById('password');
            const eye = document.getElementById('eyeIcon');
            if (pass.type === 'password') {
                pass.type = 'text';
                eye.classList.remove('fa-eye');
                eye.classList.add('fa-eye-slash');
            } else {
                pass.type = 'password';
                eye.classList.remove('fa-eye-slash');
                eye.classList.add('fa-eye');
            }
        }
    </script>
</body>
</html>
