(() => {
  const root = document.querySelector('.smg-foundation-modules');
  if (!root || typeof smgFoundationModules === 'undefined') return;

  const cards = [...root.querySelectorAll('.smg-foundation-card')];
  const search = root.querySelector('#smg-foundation-search');
  const drawer = root.querySelector('#smg-foundation-drawer');
  const drawerTitle = root.querySelector('#smg-foundation-drawer-title');
  const drawerBody = root.querySelector('.smg-foundation-drawer-body');
  const drawerForm = root.querySelector('#smg-foundation-settings-form');
  const moduleInput = drawerForm?.querySelector('input[name="module"]');
  const saveStatus = root.querySelector('.smg-foundation-save-status');
  let category = 'all';
  let status = 'all';

  const applyFilters = () => {
    const query = (search?.value || '').trim().toLowerCase();
    cards.forEach((card) => {
      const visible =
        (category === 'all' || card.dataset.category === category) &&
        (status === 'all' || card.dataset.status === status) &&
        (!query || (card.dataset.search || '').includes(query));
      card.hidden = !visible;
    });
  };

  root.querySelectorAll('.smg-foundation-sidebar button').forEach((button) => {
    button.addEventListener('click', () => {
      root.querySelectorAll('.smg-foundation-sidebar button').forEach((item) => item.classList.remove('is-active'));
      button.classList.add('is-active');
      category = button.dataset.category;
      applyFilters();
    });
  });

  root.querySelectorAll('.smg-foundation-toolbar button').forEach((button) => {
    button.addEventListener('click', () => {
      root.querySelectorAll('.smg-foundation-toolbar button').forEach((item) => item.classList.remove('is-active'));
      button.classList.add('is-active');
      status = button.dataset.status;
      applyFilters();
    });
  });

  search?.addEventListener('input', applyFilters);

  root.querySelectorAll('.smg-foundation-toggle').forEach((toggle) => {
    toggle.addEventListener('change', async () => {
      const card = toggle.closest('.smg-foundation-card');
      const settingsButton = card?.querySelector('.smg-foundation-settings-button');
      toggle.disabled = true;

      const body = new URLSearchParams({
        action: smgFoundationModules.toggleAction,
        nonce: smgFoundationModules.nonce,
        module: toggle.value,
        enabled: String(toggle.checked),
      });

      try {
        const response = await fetch(smgFoundationModules.ajaxUrl, {
          method: 'POST',
          credentials: 'same-origin',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
          body,
        });
        const result = await response.json();
        if (!result.success) throw new Error(result?.data?.message || smgFoundationModules.error);

        card.dataset.status = toggle.checked ? 'active' : 'inactive';
        if (settingsButton) settingsButton.disabled = !toggle.checked;
        applyFilters();
      } catch (error) {
        toggle.checked = !toggle.checked;
        window.alert(error.message);
      } finally {
        toggle.disabled = false;
      }
    });
  });

  const closeDrawer = () => {
    if (!drawer || drawer.hidden) return;
    drawer.classList.remove('is-open');
    drawer.setAttribute('aria-hidden', 'true');
    document.body.classList.remove('smg-foundation-drawer-open');
    window.setTimeout(() => {
      drawer.hidden = true;
      if (drawerBody) drawerBody.innerHTML = '';
      if (saveStatus) saveStatus.textContent = '';
    }, 220);
  };

  const openDrawer = async (button) => {
    if (!drawer || !drawerBody || !drawerTitle || !moduleInput) return;

    const module = button.dataset.module || '';
    const title = button.dataset.title || 'Settings';

    drawer.hidden = false;
    drawer.setAttribute('aria-hidden', 'false');
    drawerTitle.textContent = title;
    moduleInput.value = module;
    drawerBody.innerHTML = '<p class="smg-foundation-drawer-loading"></p>';
    drawerBody.querySelector('.smg-foundation-drawer-loading').textContent = smgFoundationModules.loading || 'Loading settings…';
    if (saveStatus) saveStatus.textContent = '';
    document.body.classList.add('smg-foundation-drawer-open');

    requestAnimationFrame(() => drawer.classList.add('is-open'));

    const body = new URLSearchParams({
      action: smgFoundationModules.settingsAction,
      nonce: smgFoundationModules.nonce,
      module,
    });

    try {
      const response = await fetch(smgFoundationModules.ajaxUrl, {
        method: 'POST',
        credentials: 'same-origin',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
        body,
      });
      const result = await response.json();
      if (!result.success) throw new Error(result?.data?.message || smgFoundationModules.error);
      drawerTitle.textContent = result.data?.title || title;
      drawerBody.innerHTML = result.data?.html || '';
      drawer.querySelector('input, select, textarea, button')?.focus();
    } catch (error) {
      drawerBody.innerHTML = '<div class="notice notice-error inline"><p></p></div>';
      drawerBody.querySelector('p').textContent = error.message;
    }
  };

  root.querySelectorAll('.smg-foundation-settings-button').forEach((button) => {
    button.addEventListener('click', () => openDrawer(button));
  });

  root.querySelector('.smg-foundation-drawer-close')?.addEventListener('click', closeDrawer);
  root.querySelector('.smg-foundation-drawer-cancel')?.addEventListener('click', closeDrawer);
  root.querySelector('.smg-foundation-drawer-overlay')?.addEventListener('click', closeDrawer);

  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape' && drawer && !drawer.hidden) closeDrawer();
  });

  drawerForm?.addEventListener('submit', async (event) => {
    event.preventDefault();
    const submit = drawerForm.querySelector('button[type="submit"]');
    if (submit) submit.disabled = true;
    if (saveStatus) saveStatus.textContent = '';

    const formData = new FormData(drawerForm);
    formData.append('action', smgFoundationModules.saveSettingsAction);
    formData.append('nonce', smgFoundationModules.nonce);

    try {
      const response = await fetch(smgFoundationModules.ajaxUrl, {
        method: 'POST',
        credentials: 'same-origin',
        body: formData,
      });
      const result = await response.json();
      if (!result.success) throw new Error(result?.data?.message || smgFoundationModules.error);
      if (saveStatus) {
        saveStatus.textContent = result.data?.message || smgFoundationModules.saved || 'Settings saved.';
        saveStatus.classList.add('is-success');
      }
    } catch (error) {
      if (saveStatus) {
        saveStatus.textContent = error.message;
        saveStatus.classList.remove('is-success');
      }
    } finally {
      if (submit) submit.disabled = false;
    }
  });
})();