<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SISC - Ingresar</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-slate-100 flex items-center justify-center p-0 md:p-6 font-sans">

    <div class="w-full max-w-5xl min-h-screen md:min-h-[580px] bg-white rounded-none md:rounded-3xl shadow-2xl overflow-hidden grid grid-cols-1 md:grid-cols-12">
        
        <!-- Bloque Izquierdo: Azul Profundo (Mockup) -->
        <div class="md:col-span-5 bg-[#1d4289] text-white p-8 md:p-12 flex flex-col justify-between">
            <div>
                <h2 class="text-2xl font-bold tracking-widest mb-16">SISC</h2>
                
                <h1 class="text-3xl font-extrabold leading-snug mb-6">
                    Tu identidad ciudadana en un solo lugar[cite: 5, 18]
                </h1>
                
                <p class="text-sm text-blue-100 mb-4">Sistema Interinstitucional de Servicios Ciudadanos[cite: 5, 18].</p>
                
                <ul class="space-y-3 text-xs text-blue-100 font-normal">
                    <li class="flex items-start">
                        <span class="mr-2">•</span>
                        <span>Un solo ingreso para RENAP, SAT, IGSS, IRTRA y Transmetro[cite: 5, 18]</span>
                    </li>
                    <li class="flex items-start">
                        <span class="mr-2">•</span>
                        <span>Credencial electrónica en tu teléfono[cite: 5, 18]</span>
                    </li>
                    <li class="flex items-start">
                        <span class="mr-2">•</span>
                        <span>Trámites, pagos y seguimiento sin filas ni copias[cite: 5, 18]</span>
                    </li>
                </ul>
            </div>

            <div class="text-[11px] text-blue-200 mt-12">
                República de Guatemala · Proyecto SISC[cite: 5, 18]
            </div>
        </div>

        <!-- Bloque Derecho: Formulario Hueso/Gris (Mockup) -->
        <div class="md:col-span-7 bg-[#f6f5ee] p-8 md:p-12 flex flex-col justify-center">
            
            <div class="max-w-md mx-auto w-full space-y-6">
                
                <div>
                    <span class="text-[10px] font-bold tracking-wider text-blue-900 uppercase">SERVICIOS CIUDADANOS</span>
                    <h2 class="text-3xl font-bold text-slate-900 mt-1">Ingresar[cite: 5, 18]</h2>
                </div>

                <!-- Tabs Ciudadano / Funcionario -->
                <div class="bg-[#e7e5d8] p-1 rounded-full flex">
                    <button type="button" id="tabCiudadano" class="flex-1 py-2 text-xs font-bold rounded-full bg-white text-blue-950 shadow-sm transition-all">
                        Ciudadano[cite: 5, 18]
                    </button>
                    <button type="button" id="tabFuncionario" class="flex-1 py-2 text-xs font-semibold rounded-full text-slate-600 hover:text-slate-900 transition-all">
                        Funcionario[cite: 5, 18]
                    </button>
                </div>

                <!-- Formulario procesado por AJAX -->
                <form id="formLogin" class="space-y-4">
                    <input type="hidden" name="tipo_usuario" id="tipo_usuario" value="ciudadano">

                    <!-- Targeta 1: Datos de Acceso -->
                    <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm space-y-3">
                        <div>
                            <label id="lblIdentificacion" class="block text-xs font-semibold text-slate-500 mb-1">CUI (número de DPI)[cite: 5, 18]</label>
                            <input type="text" name="identificacion" id="identificacion" placeholder="2456 78901 0123" required
                                class="w-full px-4 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-900">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 mb-1">Contraseña[cite: 5, 18]</label>
                            <input type="password" name="password" id="password" placeholder="••••••••" required
                                class="w-full px-4 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-900">
                        </div>
                    </div>

                    <!-- Tarjeta 2: Verificación 2FA -->
                    <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm space-y-2">
                        <label class="block text-xs font-bold text-slate-800">Verificación en dos pasos[cite: 5, 18]</label>
                        <p class="text-[11px] text-slate-500">Ingresa el código de 6 dígitos de tu app de autenticación[cite: 5, 18]</p>
                        <div>
                            <label class="block text-[10px] font-semibold text-slate-400 uppercase">Código[cite: 5, 18]</label>
                            <input type="text" name="mfa_codigo" id="mfa_codigo" placeholder="482 912" maxlength="6"
                                class="w-full px-4 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm font-mono tracking-widest focus:outline-none focus:ring-2 focus:ring-blue-900">
                        </div>
                    </div>

                    <!-- Mensaje de respuesta AJAX -->
                    <div id="msgResponse" class="hidden text-center text-xs font-semibold py-2 rounded-lg"></div>

                    <!-- Botón Ingresar -->
                    <button type="submit" class="w-full py-3 bg-[#1d4289] hover:bg-blue-900 text-white font-bold rounded-2xl text-sm shadow-md transition-all">
                        Ingresar[cite: 5, 18]
                    </button>
                </form>

                <!-- Registro Ciudadano -->
                <div id="bloqueRegistro" class="text-center space-y-2 pt-2">
                    <button type="button" onclick="alert('Registro disponible en siguiente paso');" class="w-full py-2.5 border border-blue-900 text-blue-900 font-bold rounded-2xl text-sm hover:bg-blue-50 transition-all">
                        Registrarme con mi DPI[cite: 5, 18]
                    </button>
                    <p class="text-[11px] text-slate-500">Validamos tu identidad con RENAP al registrarte.[cite: 5, 18]</p>
                </div>

                <!-- Nota Funcionario -->
                <div id="notaFuncionario" class="hidden bg-blue-100 text-blue-900 p-3 rounded-xl text-xs text-center border border-blue-200">
                    Pantalla funcionario: cuenta institucional + contraseña + MFA obligatorio.[cite: 5, 18]
                </div>

            </div>
        </div>

    </div>

    <script src="js/login.js"></script>
</body>
</html>