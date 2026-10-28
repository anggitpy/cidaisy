(() => {
  const storageKey = 'cidaisy-theme';
  const applyTheme = (theme) => {
    document.documentElement.dataset.theme = theme;
    document.querySelectorAll('[data-theme-toggle]').forEach(button => {
      button.setAttribute('aria-pressed', String(theme === 'dark'));
      button.textContent = theme === 'dark' ? 'Light theme' : 'Dark theme';
    });
  };
  let theme = matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
  try { theme = localStorage.getItem(storageKey) || theme; } catch (_) {}
  applyTheme(theme === 'dark' ? 'dark' : 'light');
  document.querySelectorAll('[data-theme-toggle]').forEach(button => button.addEventListener('click', () => {
    const next = document.documentElement.dataset.theme === 'dark' ? 'light' : 'dark';
    applyTheme(next);
    try { localStorage.setItem(storageKey, next); } catch (_) {}
  }));
  const dialog = document.querySelector('#confirm-dialog');
  let pendingForm = null;
  document.querySelectorAll('form[data-confirm]').forEach(form => form.addEventListener('submit', event => {
    if (!dialog) return;
    event.preventDefault();
    pendingForm = form;
    dialog.querySelector('[data-confirm-message]').textContent = form.dataset.confirm;
    dialog.showModal();
  }));
  dialog?.querySelector('[data-confirm-accept]').addEventListener('click', () => {
    dialog.close();
    pendingForm?.submit();
  });
})();
