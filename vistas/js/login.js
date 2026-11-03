document.addEventListener("DOMContentLoaded", function () {
    // Referencias elementos Login
    const tabCiudadano = document.getElementById("tabCiudadano");
    const tabFuncionario = document.getElementById("tabFuncionario");
    const tipoUsuario = document.getElementById("tipo_usuario");
    const lblIdentificacion = document.getElementById("lblIdentificacion");
    const identificacion = document.getElementById("identificacion");
    const iconId = document.getElementById("iconId");
    const bloqueRegistro = document.getElementById("bloqueRegistro");
    const notaFuncionario = document.getElementById("notaFuncionario");
    const formLogin = document.getElementById("formLogin");
    const btnSubmit = document.getElementById("btnSubmit");
    const msgResponse = document.getElementById("msgResponse");

    // Referencias Modal Registro
    const modalRegistro = document.getElementById("modalRegistro");
    const btnAbrirModalRegistro = document.getElementById("btnAbrirModalRegistro");
    const btnCerrarModal = document.getElementById("btnCerrarModal");
    const btnCancelarRegistro = document.getElementById("btnCancelarRegistro");
    const formRegistroCiudadano = document.getElementById("formRegistroCiudadano");
    const btnGuardarCiudadano = document.getElementById("btnGuardarCiudadano");
    const modalMsg = document.getElementById("modalMsg");

    // ==========================================
    // 1. GESTIÓN DE TABS (CIUDADANO / FUNCIONARIO)
    // ==========================================
    tabCiudadano.addEventListener("click", function () {
        tipoUsuario.value = "ciudadano";
        tabCiudadano.className = "flex-1 py-2 text-xs font-bold rounded-full bg-white text-blue-950 shadow-sm transition-all flex items-center justify-center gap-1.5";
        tabFuncionario.className = "flex-1 py-2 text-xs font-semibold rounded-full text-slate-600 hover:text-slate-900 transition-all flex items-center justify-center gap-1.5";
        lblIdentificacion.innerText = "CUI (número de DPI)";
        identificacion.placeholder = "2456 78901 0123";
        if (iconId) iconId.className = "fa-solid fa-id-card";
        bloqueRegistro.classList.remove("hidden");
        notaFuncionario.classList.add("hidden");
    });

    tabFuncionario.addEventListener("click", function () {
        tipoUsuario.value = "funcionario";
        tabFuncionario.className = "flex-1 py-2 text-xs font-bold rounded-full bg-white text-blue-950 shadow-sm transition-all flex items-center justify-center gap-1.5";
        tabCiudadano.className = "flex-1 py-2 text-xs font-semibold rounded-full text-slate-600 hover:text-slate-900 transition-all flex items-center justify-center gap-1.5";
        lblIdentificacion.innerText = "Usuario Institucional / Correo";
        identificacion.placeholder = "admin / funcionario_renap";
        if (iconId) iconId.className = "fa-solid fa-user-shield";
        bloqueRegistro.classList.add("hidden");
        notaFuncionario.classList.remove("hidden");
    });

    // ==========================================
    // 2. ENVÍO DE LOGIN POR AJAX
    // ==========================================
    formLogin.addEventListener("submit", function (e) {
        e.preventDefault();

        const formData = new FormData(formLogin);
        btnSubmit.disabled = true;
        const originalBtnHtml = btnSubmit.innerHTML;
        btnSubmit.innerHTML = `<i class="fa-solid fa-spinner fa-spin"></i> <span>Verificando...</span>`;

        msgResponse.classList.add("hidden");
        msgResponse.className = "text-center text-xs font-semibold py-2.5 px-3 rounded-xl transition-all";

        fetch("../ajax/usuario.php?op=verificar", {
            method: "POST",
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            msgResponse.classList.remove("hidden");
            if (data.status === "success") {
                msgResponse.className = "text-center text-xs font-semibold py-2.5 px-3 rounded-xl bg-emerald-100 text-emerald-800 border border-emerald-300";
                msgResponse.innerHTML = `<i class="fa-solid fa-circle-check me-1"></i> ${data.mensaje} Redirigiendo a su portal...`;
                setTimeout(() => {
                    window.location.href = data.redirect;
                }, 800);
            } else {
                btnSubmit.disabled = false;
                btnSubmit.innerHTML = originalBtnHtml;
                msgResponse.className = "text-center text-xs font-semibold py-2.5 px-3 rounded-xl bg-rose-100 text-rose-800 border border-rose-300";
                msgResponse.innerHTML = `<i class="fa-solid fa-triangle-exclamation me-1"></i> ${data.mensaje}`;
            }
        })
        .catch(err => {
            console.error("Error AJAX:", err);
            btnSubmit.disabled = false;
            btnSubmit.innerHTML = originalBtnHtml;
            msgResponse.classList.remove("hidden");
            msgResponse.className = "text-center text-xs font-semibold py-2.5 px-3 rounded-xl bg-rose-100 text-rose-800 border border-rose-300";
            msgResponse.innerHTML = `<i class="fa-solid fa-triangle-exclamation me-1"></i> Error en la conexión con el servidor.`;
        });
    });

    // ==========================================
    // 3. APERTURA Y CIERRE DEL MODAL DE REGISTRO
    // ==========================================
    function abrirModal() {
        modalRegistro.classList.remove("hidden");
        document.body.style.overflow = "hidden";
    }

    function cerrarModal() {
        modalRegistro.classList.add("hidden");
        document.body.style.overflow = "auto";
        modalMsg.classList.add("hidden");
    }

    if (btnAbrirModalRegistro) {
        btnAbrirModalRegistro.addEventListener("click", abrirModal);
    }
    if (btnCerrarModal) {
        btnCerrarModal.addEventListener("click", cerrarModal);
    }
    if (btnCancelarRegistro) {
        btnCancelarRegistro.addEventListener("click", cerrarModal);
    }

    // Cerrar con Escape o click fuera
    window.addEventListener("keydown", function(e) {
        if (e.key === "Escape" && !modalRegistro.classList.contains("hidden")) {
            cerrarModal();
        }
    });

    modalRegistro.addEventListener("click", function(e) {
        if (e.target === modalRegistro) {
            cerrarModal();
        }
    });

    // ==========================================
    // 4. ENVÍO DE REGISTRO CIUDADANO (AJAX)
    // ==========================================
    if (formRegistroCiudadano) {
        formRegistroCiudadano.addEventListener("submit", function(e) {
            e.preventDefault();

            const p1 = document.getElementById("reg_password").value;
            const p2 = document.getElementById("reg_password_confirm").value;

            if (p1 !== p2) {
                mostrarMsgModal("error", "Las contraseñas no coinciden. Por favor verifícalas.");
                return;
            }

            const formData = new FormData(formRegistroCiudadano);
            btnGuardarCiudadano.disabled = true;
            const btnHtml = btnGuardarCiudadano.innerHTML;
            btnGuardarCiudadano.innerHTML = `<i class="fa-solid fa-spinner fa-spin"></i> Registrando...`;

            fetch("../ajax/usuario.php?op=registrar_ciudadano", {
                method: "POST",
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                btnGuardarCiudadano.disabled = false;
                btnGuardarCiudadano.innerHTML = btnHtml;

                if (data.status === "success") {
                    mostrarMsgModal("success", data.mensaje);
                    formRegistroCiudadano.reset();

                    // Pre-llenar en el formulario de login y cerrar modal tras 1.5s
                    setTimeout(() => {
                        cerrarModal();
                        tabCiudadano.click();
                        identificacion.value = data.username || '';
                        document.getElementById("password").focus();
                        msgResponse.classList.remove("hidden");
                        msgResponse.className = "text-center text-xs font-semibold py-2.5 px-3 rounded-xl bg-emerald-100 text-emerald-800 border border-emerald-300";
                        msgResponse.innerHTML = `<i class="fa-solid fa-circle-check me-1"></i> Registro completado con éxito. Ahora ingresa tu contraseña.`;
                    }, 1500);
                } else {
                    mostrarMsgModal("error", data.mensaje);
                }
            })
            .catch(err => {
                console.error("Error en registro:", err);
                btnGuardarCiudadano.disabled = false;
                btnGuardarCiudadano.innerHTML = btnHtml;
                mostrarMsgModal("error", "Ocurrió un error al procesar el registro. Inténtalo nuevamente.");
            });
        });
    }

    function mostrarMsgModal(tipo, texto) {
        modalMsg.classList.remove("hidden");
        if (tipo === "success") {
            modalMsg.className = "text-xs py-2 px-3 rounded-xl font-semibold bg-emerald-100 text-emerald-800 border border-emerald-300";
            modalMsg.innerHTML = `<i class="fa-solid fa-circle-check me-1"></i> ${texto}`;
        } else {
            modalMsg.className = "text-xs py-2 px-3 rounded-xl font-semibold bg-rose-100 text-rose-800 border border-rose-300";
            modalMsg.innerHTML = `<i class="fa-solid fa-circle-exclamation me-1"></i> ${texto}`;
        }
    }
});
