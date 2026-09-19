(() => {
  const app = document.querySelector('.smgss-app');
  if (!app || typeof smgSiteSuite === 'undefined') return;

  const cards = [...app.querySelectorAll('.smgss-card')];
  const search = app.querySelector('#smgss-search');
  let category = 'all';
  let status = 'all';

  const filter = () => {
    const query = search.value.trim().toLowerCase();
    cards.forEach((card) => {
      const categoryOk = category === 'all' || card.dataset.category === category;
      const statusOk = status === 'all' || card.dataset.status === status;
      const searchOk = !query || card.textContent.toLowerCase().includes(query);
      card.hidden = !(categoryOk && statusOk && searchOk);
    });
  };

  app.querySelectorAll('.smgss-category').forEach((button) => {
    button.addEventListener('click', () => {
      app.querySelectorAll('.smgss-category').forEach((item) => item.classList.remove('is-active'));
      button.classList.add('is-active');
      category = button.dataset.category;
      filter();
    });
  });

  app.querySelectorAll('.smgss-status-filters button').forEach((button) => {
    button.addEventListener('click', () => {
      app.querySelectorAll('.smgss-status-filters button').forEach((item) => item.classList.remove('is-active'));
      button.classList.add('is-active');
      status = button.dataset.status;
      filter();
    });
  });

  search.addEventListener('input', filter);

  app.querySelectorAll('.smgss-module-toggle').forEach((toggle) => {
    toggle.addEventListener('change', async () => {
      const card = toggle.closest('.smgss-card');
      toggle.disabled = true;
      const data = new URLSearchParams({
        action: 'smg_site_suite_toggle_module',
        nonce: smgSiteSuite.nonce,
        module: toggle.value,
        enabled: String(toggle.checked),
      });

      try {
        const response = await fetch(smgSiteSuite.ajaxUrl, {
          method: 'POST',
          credentials: 'same-origin',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
          body: data,
        });
        const result = await response.json();
        if (!result.success) throw new Error(result?.data?.message || 'Unable to update module.');
        card.dataset.status = toggle.checked ? 'active' : 'inactive';
        filter();
      } catch (error) {
        toggle.checked = !toggle.checked;
        window.alert(error.message);
      } finally {
        toggle.disabled = false;
      }
    });
  });
})();
