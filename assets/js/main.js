/* DonaTu - Main JS - Sprint 1 */

document.addEventListener('DOMContentLoaded', () => {

  /* ── Progress bars animate on load ── */
  document.querySelectorAll('.progress-bar-fill').forEach(bar => {
    const target = bar.getAttribute('data-width') || '0';
    setTimeout(() => { bar.style.width = target + '%'; }, 200);
  });

  /* ── Password toggle ── */
  document.querySelectorAll('[data-toggle-password]').forEach(btn => {
    btn.addEventListener('click', () => {
      const input = document.querySelector(btn.dataset.togglePassword);
      if (!input) return;
      const isText = input.type === 'text';
      input.type = isText ? 'password' : 'text';
      btn.querySelector('i').className = isText ? 'fas fa-eye' : 'fas fa-eye-slash';
    });
  });

  /* ── Auto-hide alerts ── */
  document.querySelectorAll('.alert[data-auto-hide]').forEach(alert => {
    setTimeout(() => {
      alert.style.transition = 'opacity .4s';
      alert.style.opacity = '0';
      setTimeout(() => alert.remove(), 400);
    }, 3500);
  });

  /* ── Confirm delete ── */
  document.querySelectorAll('[data-confirm]').forEach(el => {
    el.addEventListener('click', e => {
      if (!confirm(el.dataset.confirm || '¿Estás seguro?')) e.preventDefault();
    });
  });

  /* ── Format currency inputs ── */
  document.querySelectorAll('input[data-currency]').forEach(input => {
    input.addEventListener('blur', () => {
      const val = parseFloat(input.value);
      if (!isNaN(val)) input.value = val.toFixed(2);
    });
  });

});

/* Format number as currency Q */
function formatCurrency(amount) {
  return 'Q ' + parseFloat(amount).toLocaleString('es-GT', {
    minimumFractionDigits: 2, maximumFractionDigits: 2
  });
}
