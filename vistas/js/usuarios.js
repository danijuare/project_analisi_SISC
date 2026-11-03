document.addEventListener("DOMContentLoaded", function () {
    const tbodyUsuarios = document.getElementById("tbodyUsuarios");
    const totalUsuarios = document.getElementById("totalUsuarios");
    const buscador = document.getElementById("buscador");
    const alertaGeneral = document.getElementById("alertaGeneral");

    const modalUsuario = document.getElementById("modalUsuario");
    const modalTitulo = document.getElementById("modalTitulo");
    const btnNuevoUsuario = document.getElementById("btnNuevoUsuario");
    const btnCerrarModal = document.getElementById("btnCerrarModal");
    const btnCancelar = document.getElementById("btnCancelar");
    const formUsuario = document.getElementById("formUsuario");
    const modalMsg = document.getElementById("modalMsg");
    const btnGuardar = document.getElementById("btnGuardar");
    const lblPassword = document.getElementById("lblPassword");
    const passwordInput = document.getElementById("password");

    const selectRol = document.getElementById("rol_id");
    const selectInst = document.getElementById("institucion_id");

    let listaUsuariosCache = [];

    // ==========================================
    // 1. CARGA INICIAL
    // ==========================================
    cargarRoles();
    cargarInstituciones();
    cargarUsuarios();

    // ==========================================
    // 2. OBTENER LISTAS DE SELECTS (ROLES E INST)
    // ==========================================
    function cargarRoles() {
        fetch("../ajax/usuario.php?op=listar_roles")
            .then(res => res.json())
            .then(data => {
                if (data.status === "success") {
                    selectRol.innerHTML = "";
                    data.data.forEach(r => {
                        const opt = document.createElement("option");
                        opt.value = r.rol_id;
                        opt.textContent = `${r.nombre} (${r.descripcion || ''})`;
                        selectRol.appendChild(opt);
                    });
                }
            })
            .catch(err => console.error("Error al cargar roles:", err));
    }

    function cargarInstituciones() {
        fetch("../ajax/usuario.php?op=listar_instituciones")
            .then(res => res.json())
            .then(data => {
                if (data.status === "success") {
                    selectInst.innerHTML = "";
                    data.data.forEach(i => {
                        const opt = document.createElement("option");
                        opt.value = i.institucion_id;
                        opt.textContent = `${i.nombre} [${i.codigo}]`;
                        selectInst.appendChild(opt);
                    });
                }
            })
            .catch(err => console.error("Error al cargar instituciones:", err));
    }

    // ==========================================
    // 3. CARGAR Y RENDERIZAR TABLA DE USUARIOS
    // ==========================================
    function cargarUsuarios() {
        fetch("../ajax/usuario.php?op=listar")
            .then(res => res.json())
            .then(data => {
                if (data.status === "success") {
                    listaUsuariosCache = data.data;
                    renderizarTabla(listaUsuariosCache);
                } else {
                    tbodyUsuarios.innerHTML = `<tr><td colspan="8" class="text-center py-6 text-rose-500 font-bold">${data.mensaje || 'Error al obtener usuarios'}</td></tr>`;
                }
            })
            .catch(err => {
                console.error("Error AJAX:", err);
                tbodyUsuarios.innerHTML = `<tr><td colspan="8" class="text-center py-6 text-rose-500 font-bold">Error de conexión al cargar usuarios.</td></tr>`;
            });
    }

    function renderizarTabla(lista) {
        totalUsuarios.innerText = lista.length;
        if (lista.length === 0) {
            tbodyUsuarios.innerHTML = `<tr><td colspan="8" class="text-center py-8 text-slate-400">No se encontraron usuarios registrados.</td></tr>`;
            return;
        }

        let html = "";
        lista.forEach(u => {
            const badgeTipo = u.tipo_usuario === 'ciudadano' 
                ? '<span class="bg-blue-100 text-blue-800 font-bold px-2 py-0.5 rounded text-[10px]"><i class="fa-solid fa-user me-1"></i>Ciudadano</span>'
                : '<span class="bg-purple-100 text-purple-800 font-bold px-2 py-0.5 rounded text-[10px]"><i class="fa-solid fa-briefcase me-1"></i>Funcionario</span>';

            const badgeEstado = parseInt(u.activo) === 1
                ? '<span class="bg-emerald-100 text-emerald-800 font-bold px-2 py-0.5 rounded-full text-[10px]"><i class="fa-solid fa-circle-check me-1"></i>Activo</span>'
                : '<span class="bg-rose-100 text-rose-800 font-bold px-2 py-0.5 rounded-full text-[10px]"><i class="fa-solid fa-circle-xmark me-1"></i>Inactivo</span>';

            const btnToggleEstado = parseInt(u.activo) === 1
                ? `<button onclick="cambiarEstado(${u.usuario_id}, 0)" title="Desactivar usuario" class="p-1.5 rounded-lg bg-rose-50 text-rose-600 hover:bg-rose-100 transition-all"><i class="fa-solid fa-ban"></i></button>`
                : `<button onclick="cambiarEstado(${u.usuario_id}, 1)" title="Activar usuario" class="p-1.5 rounded-lg bg-emerald-50 text-emerald-600 hover:bg-emerald-100 transition-all"><i class="fa-solid fa-check"></i></button>`;

            html += `
                <tr class="hover:bg-slate-50 transition-all">
                    <td class="py-3 px-4 font-mono font-bold text-slate-500">#${u.usuario_id}</td>
                    <td class="py-3 px-4 font-bold text-slate-900">${u.username}</td>
                    <td class="py-3 px-4">
                        <div class="font-semibold text-slate-800">${u.nombre_completo}</div>
                        ${u.cui !== 'N/A' ? `<div class="text-[10px] text-slate-500 font-mono">CUI: ${u.cui}</div>` : ''}
                    </td>
                    <td class="py-3 px-4">${badgeTipo}</td>
                    <td class="py-3 px-4 text-slate-700 font-medium">${u.institucion_nombre}</td>
                    <td class="py-3 px-4">
                        <span class="bg-slate-100 text-slate-800 font-mono font-bold px-2 py-0.5 rounded text-[10px] border border-slate-200">
                            ${u.rol_nombre}
                        </span>
                    </td>
                    <td class="py-3 px-4">${badgeEstado}</td>
                    <td class="py-3 px-4 text-center space-x-1">
                        <button onclick="editarUsuario(${u.usuario_id})" title="Editar usuario" class="p-1.5 rounded-lg bg-blue-50 text-blue-900 hover:bg-blue-100 transition-all">
                            <i class="fa-solid fa-pen-to-square"></i>
                        </button>
                        ${btnToggleEstado}
                    </td>
                </tr>
            `;
        });
        tbodyUsuarios.innerHTML = html;
    }

    // ==========================================
    // 4. BUSCADOR EN VIVO
    // ==========================================
    buscador.addEventListener("input", function () {
        const term = this.value.toLowerCase().trim();
        if (!term) {
            renderizarTabla(listaUsuariosCache);
            return;
        }

        const filtrados = listaUsuariosCache.filter(u => {
            return u.username.toLowerCase().includes(term) ||
                   u.nombre_completo.toLowerCase().includes(term) ||
                   u.rol_nombre.toLowerCase().includes(term) ||
                   u.institucion_nombre.toLowerCase().includes(term) ||
                   (u.cui && u.cui.includes(term));
        });
        renderizarTabla(filtrados);
    });

    // ==========================================
    // 5. GESTIÓN DEL MODAL (CREAR / EDITAR)
    // ==========================================
    function abrirModal() {
        modalUsuario.classList.remove("hidden");
        document.body.style.overflow = "hidden";
    }

    function cerrarModal() {
        modalUsuario.classList.add("hidden");
        document.body.style.overflow = "auto";
        modalMsg.classList.add("hidden");
        formUsuario.reset();
        document.getElementById("usuario_id").value = "";
    }

    btnNuevoUsuario.addEventListener("click", function () {
        modalTitulo.innerText = "Crear Nuevo Usuario";
        lblPassword.innerText = "Contraseña *";
        passwordInput.required = true;
        passwordInput.placeholder = "••••••••";
        formUsuario.reset();
        document.getElementById("usuario_id").value = "";
        abrirModal();
    });

    btnCerrarModal.addEventListener("click", cerrarModal);
    btnCancelar.addEventListener("click", cerrarModal);

    window.editarUsuario = function (usuario_id) {
        modalTitulo.innerText = "Editar Usuario #" + usuario_id;
        lblPassword.innerText = "Nueva Contraseña (dejar en blanco para no cambiar)";
        passwordInput.required = false;
        passwordInput.placeholder = "•••••••• (opcional)";
        modalMsg.classList.add("hidden");

        const formData = new FormData();
        formData.append("usuario_id", usuario_id);

        fetch("../ajax/usuario.php?op=mostrar", {
            method: "POST",
            body: formData
        })
        .then(res => res.json())
        .then(res => {
            if (res.status === "success") {
                const d = res.data;
                document.getElementById("usuario_id").value = d.usuario_id;
                document.getElementById("username").value = d.username;
                document.getElementById("tipo_usuario").value = d.tipo_usuario;
                if (d.institucion_id) document.getElementById("institucion_id").value = d.institucion_id;
                if (d.rol_id) document.getElementById("rol_id").value = d.rol_id;
                document.getElementById("nombres").value = d.nombres || "";
                document.getElementById("apellidos").value = d.apellidos || "";
                document.getElementById("email").value = d.email || "";
                document.getElementById("telefono").value = d.telefono || "";
                document.getElementById("activo").checked = (parseInt(d.activo) === 1);
                abrirModal();
            } else {
                mostrarAlertaGeneral("error", res.mensaje || "Error al obtener usuario");
            }
        })
        .catch(err => {
            console.error(err);
            mostrarAlertaGeneral("error", "Error de conexión");
        });
    };

    window.cambiarEstado = function (usuario_id, nuevoEstado) {
        const accion = nuevoEstado === 1 ? 'activar' : 'desactivar';
        if (!confirm(`¿Estás seguro de que deseas ${accion} este usuario?`)) {
            return;
        }

        const formData = new FormData();
        formData.append("usuario_id", usuario_id);
        formData.append("activo", nuevoEstado);

        fetch("../ajax/usuario.php?op=cambiar_estado", {
            method: "POST",
            body: formData
        })
        .then(res => res.json())
        .then(res => {
            if (res.status === "success") {
                mostrarAlertaGeneral("success", res.mensaje);
                cargarUsuarios();
            } else {
                mostrarAlertaGeneral("error", res.mensaje);
            }
        })
        .catch(err => {
            console.error(err);
            mostrarAlertaGeneral("error", "Error al cambiar estado");
        });
    };

    // ==========================================
    // 6. GUARDAR FORMULARIO DE USUARIO (AJAX)
    // ==========================================
    formUsuario.addEventListener("submit", function (e) {
        e.preventDefault();

        const formData = new FormData(formUsuario);
        formData.set("activo", document.getElementById("activo").checked ? "1" : "0");

        btnGuardar.disabled = true;
        const btnHtml = btnGuardar.innerHTML;
        btnGuardar.innerHTML = `<i class="fa-solid fa-spinner fa-spin"></i> Guardando...`;

        fetch("../ajax/usuario.php?op=guardaryeditar_funcionario", {
            method: "POST",
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            btnGuardar.disabled = false;
            btnGuardar.innerHTML = btnHtml;

            if (data.status === "success") {
                mostrarAlertaGeneral("success", data.mensaje);
                cerrarModal();
                cargarUsuarios();
            } else {
                modalMsg.classList.remove("hidden");
                modalMsg.className = "text-xs py-2 px-3 rounded-xl font-semibold bg-rose-100 text-rose-800 border border-rose-300";
                modalMsg.innerHTML = `<i class="fa-solid fa-circle-exclamation me-1"></i> ${data.mensaje}`;
            }
        })
        .catch(err => {
            console.error(err);
            btnGuardar.disabled = false;
            btnGuardar.innerHTML = btnHtml;
            modalMsg.classList.remove("hidden");
            modalMsg.className = "text-xs py-2 px-3 rounded-xl font-semibold bg-rose-100 text-rose-800 border border-rose-300";
            modalMsg.innerHTML = `<i class="fa-solid fa-circle-exclamation me-1"></i> Error en la petición AJAX.`;
        });
    });

    function mostrarAlertaGeneral(tipo, msg) {
        alertaGeneral.classList.remove("hidden");
        if (tipo === "success") {
            alertaGeneral.className = "text-xs py-3 px-4 rounded-2xl font-semibold bg-emerald-100 text-emerald-800 border border-emerald-300";
            alertaGeneral.innerHTML = `<i class="fa-solid fa-circle-check me-1"></i> ${msg}`;
        } else {
            alertaGeneral.className = "text-xs py-3 px-4 rounded-2xl font-semibold bg-rose-100 text-rose-800 border border-rose-300";
            alertaGeneral.innerHTML = `<i class="fa-solid fa-circle-exclamation me-1"></i> ${msg}`;
        }
        setTimeout(() => {
            alertaGeneral.classList.add("hidden");
        }, 4000);
    }
});

