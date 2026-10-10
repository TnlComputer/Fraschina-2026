{{-- Ojo para mostrar u ocultar la contraseña en los campos type="password" de la pantalla --}}
@push('css')
<style>
  .ver-clave { cursor: pointer; }
  .ver-clave .input-group-text { min-width: 42px; justify-content: center; }
</style>
@endpush
@push('js')
<script>
  document.querySelectorAll('input[type="password"]').forEach(function (input) {
    var grupo = input.closest('.input-group');
    var boton = document.createElement('div');
    boton.className = 'input-group-append ver-clave';
    boton.title = 'Mostrar contraseña';
    boton.setAttribute('role', 'button');
    boton.setAttribute('tabindex', '0');
    boton.innerHTML = '<div class="input-group-text"><span class="fas fa-eye"></span></div>';
    var alternar = function () {
      var oculta = input.type === 'password';
      input.type = oculta ? 'text' : 'password';
      boton.querySelector('span').className = oculta ? 'fas fa-eye-slash' : 'fas fa-eye';
      boton.title = oculta ? 'Ocultar contraseña' : 'Mostrar contraseña';
      input.focus();
    };
    boton.addEventListener('click', alternar);
    boton.addEventListener('keydown', function (e) { if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); alternar(); } });
    if (grupo) {
      // reemplaza el candado de AdminLTE por el ojo
      var candado = grupo.querySelector('.input-group-append');
      if (candado) { candado.replaceWith(boton); } else { grupo.appendChild(boton); }
    } else {
      var envoltura = document.createElement('div');
      envoltura.className = 'input-group';
      input.parentNode.insertBefore(envoltura, input);
      envoltura.appendChild(input);
      envoltura.appendChild(boton);
    }
  });
</script>
@endpush
