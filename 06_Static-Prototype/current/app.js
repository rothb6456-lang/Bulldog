(() => {
  const sidebar = document.querySelector('.sidebar');
  const sidebarToggle = document.querySelector('[data-sidebar-toggle]');

  sidebarToggle?.addEventListener('click', () => {
    const isOpen = sidebar.classList.toggle('open');
    sidebarToggle.setAttribute('aria-expanded', String(isOpen));
  });

  document.querySelectorAll('[data-tab-target]').forEach((control) => {
    control.addEventListener('click', (event) => {
      const targetId = control.dataset.tabTarget;
      const panel = document.getElementById(targetId);
      if (!panel) return;
      event.preventDefault();
      document.querySelectorAll('.tab').forEach((tab) => {
        const active = tab.dataset.tabTarget === targetId;
        tab.classList.toggle('active', active);
        tab.setAttribute('aria-selected', String(active));
      });
      document.querySelectorAll('.tab-panel').forEach((item) => item.classList.toggle('active', item.id === targetId));
      panel.scrollIntoView({ behavior: 'smooth', block: 'start' });
    });
  });

  document.querySelectorAll('[data-table-search]').forEach((input) => {
    const table = document.getElementById(input.dataset.tableSearch);
    input.addEventListener('input', () => {
      const query = input.value.toLowerCase().trim();
      table?.querySelectorAll('tbody tr').forEach((row) => {
        row.hidden = !row.textContent.toLowerCase().includes(query);
      });
    });
  });

  document.querySelectorAll('[data-prototype-notice]').forEach((button) => {
    button.addEventListener('click', () => window.alert(button.dataset.prototypeNotice));
  });
})();
