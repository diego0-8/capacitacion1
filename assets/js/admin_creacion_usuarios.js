(function () {
  'use strict';

  var modal = document.getElementById('usuario-modal');
  if (!modal) return;

  var form = document.getElementById('usuario-form');
  var titulo = document.getElementById('usuario-modal-titulo');
  var btnSubmit = document.getElementById('usuario-form-submit');
  var wrapConfirmar = document.getElementById('wrap-clave-confirmar');
  var helpClave = document.getElementById('help-clave-editar');
  var inputCedula = document.getElementById('cedula');
  var inputClave = document.getElementById('clave');
  var inputClaveConfirmar = document.getElementById('clave_confirmar');

  function setModoNuevo() {
    if (titulo) titulo.textContent = 'Nuevo usuario';
    if (btnSubmit) btnSubmit.textContent = 'Crear usuario';
    if (form) form.reset();
    if (inputCedula) {
      inputCedula.readOnly = false;
      inputCedula.required = true;
    }
    if (inputClave) {
      inputClave.required = true;
      inputClave.placeholder = '';
    }
    if (inputClaveConfirmar) {
      inputClaveConfirmar.required = true;
    }
    if (wrapConfirmar) wrapConfirmar.hidden = false;
    if (helpClave) helpClave.hidden = true;
    var rol = document.getElementById('rol');
    var estado = document.getElementById('estado');
    if (rol) rol.value = 'asesor';
    if (estado) estado.value = 'activo';
  }

  function setModoEditar(data) {
    if (titulo) titulo.textContent = 'Editar usuario';
    if (btnSubmit) btnSubmit.textContent = 'Guardar cambios';
    if (inputCedula) {
      inputCedula.value = data.cedula || '';
      inputCedula.readOnly = true;
      inputCedula.required = true;
    }
    var nombre = document.getElementById('nombre');
    var usuario = document.getElementById('usuario');
    var email = document.getElementById('email');
    var rol = document.getElementById('rol');
    var estado = document.getElementById('estado');
    if (nombre) nombre.value = data.nombre || '';
    if (usuario) usuario.value = data.usuario || '';
    if (email) email.value = data.email || '';
    if (rol) rol.value = data.rol || 'asesor';
    if (estado) estado.value = data.estado || 'activo';
    if (inputClave) {
      inputClave.value = '';
      inputClave.required = false;
      inputClave.placeholder = 'Dejar vacío para no cambiar';
    }
    if (inputClaveConfirmar) {
      inputClaveConfirmar.value = '';
      inputClaveConfirmar.required = false;
    }
    if (wrapConfirmar) wrapConfirmar.hidden = true;
    if (helpClave) helpClave.hidden = false;
  }

  function abrirModal() {
    if (typeof modal.showModal === 'function') {
      modal.showModal();
    } else {
      modal.setAttribute('open', '');
    }
  }

  function cerrarModal() {
    if (modal.open) modal.close();
    modal.removeAttribute('open');
  }

  window.abrirModalUsuarioNuevo = function () {
    setModoNuevo();
    abrirModal();
  };

  window.abrirModalUsuarioEditar = function (data) {
    setModoEditar(data || {});
    abrirModal();
  };

  document.querySelectorAll('[data-abrir-usuario-nuevo]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      window.abrirModalUsuarioNuevo();
    });
  });

  document.querySelectorAll('.btn-editar-usuario').forEach(function (btn) {
    btn.addEventListener('click', function () {
      window.abrirModalUsuarioEditar({
        cedula: btn.getAttribute('data-cedula') || '',
        nombre: btn.getAttribute('data-nombre') || '',
        usuario: btn.getAttribute('data-usuario') || '',
        rol: btn.getAttribute('data-rol') || 'asesor',
        email: btn.getAttribute('data-email') || '',
        estado: btn.getAttribute('data-estado') || 'activo',
      });
    });
  });

  modal.querySelectorAll('[data-usuario-modal-cerrar]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      cerrarModal();
    });
  });

  modal.addEventListener('click', function (e) {
    if (e.target === modal) cerrarModal();
  });

  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape' && modal.open) cerrarModal();
  });

  if (window.USUARIO_EDIT_INICIAL && typeof window.USUARIO_EDIT_INICIAL === 'object') {
    window.abrirModalUsuarioEditar(window.USUARIO_EDIT_INICIAL);
    if (history.replaceState) {
      try {
        var url = new URL(window.location.href);
        url.searchParams.delete('cedula');
        history.replaceState({}, '', url.pathname + url.search);
      } catch (e) { /* ignore */ }
    }
  }
})();
