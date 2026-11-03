document.addEventListener("DOMContentLoaded", function () {
    const tabCiudadano = document.getElementById("tabCiudadano");
    const tabFuncionario = document.getElementById("tabFuncionario");
    const tipoUsuario = document.getElementById("tipo_usuario");
    const lblIdentificacion = document.getElementById("lblIdentificacion");
    const bloqueRegistro = document.getElementById("bloqueRegistro");
    const notaFuncionario = document.getElementById("notaFuncionario");
    const formLogin = document.getElementById("formLogin");
    const msgResponse = document.getElementById("msgResponse");

    // Evento Tab Ciudadano
    tabCiudadano.addEventListener("click", function () {
        tipoUsuario.value = "ciudadano";
        tabCiudadano.className = "flex-1 py-2 text-xs font-bold rounded-full bg-white text-blue-950 shadow-sm transition-all";
        tabFuncionario.className = "flex-1 py-2 text-xs font-semibold rounded-full text-slate-600 hover:text-slate-900 transition-all";
        lblIdentificacion.innerText = "CUI (número de DPI)";
        bloqueRegistro.classList.remove("hidden");
        notaFuncionario.classList.add("hidden");
    });

    // Evento Tab Funcionario
    tabFuncionario.addEventListener("click", function () {
        tipoUsuario.value = "funcionario";
        tabFuncionario.className = "flex-1 py-2 text-xs font-bold rounded-full bg-white text-blue-950 shadow-sm transition-all";
        tabCiudadano.className = "flex-1 py-2 text-xs font-semibold rounded-full text-slate-600 hover:text-slate-900 transition-all";
        lblIdentificacion.innerText = "Usuario Institucional / Correo";
        bloqueRegistro.classList.add("hidden");
        notaFuncionario.classList.remove("hidden");
    });

    // Envío por AJAX
    formLogin.addEventListener("submit", function (e) {
        e.preventDefault();

        const formData = new FormData(formLogin);

        fetch("../ajax/usuario.php?op=verificar", {
            method: "POST",
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            msgResponse.classList.remove("hidden");
            if (data.status === "success") {
                msgResponse.className = "text-center text-xs font-semibold py-2 rounded-lg bg-emerald-100 text-emerald-800";
                msgResponse.innerText = data.mensaje;
                setTimeout(() => {
                    window.location.href = data.redirect;
                }, 1000);
            } else {
                msgResponse.className = "text-center text-xs font-semibold py-2 rounded-lg bg-rose-100 text-rose-800";
                msgResponse.innerText = data.mensaje;
            }
        })
        .catch(err => {
            console.error("Error AJAX:", err);
            msgResponse.classList.remove("hidden");
            msgResponse.className = "text-center text-xs font-semibold py-2 rounded-lg bg-rose-100 text-rose-800";
            msgResponse.innerText = "Error en la solicitud de conexión.";
        });
    });
});