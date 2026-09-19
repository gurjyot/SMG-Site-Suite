(() => {
  const config = window.smgSiteSuiteFomo;
  if (!config || !Array.isArray(config.items) || config.items.length === 0) return;

  const items = [...config.items];
  for (let i = items.length - 1; i > 0; i -= 1) {
    const j = Math.floor(Math.random() * (i + 1));
    [items[i], items[j]] = [items[j], items[i]];
  }

  const wrap = document.createElement('div');
  wrap.className = 'smgss-fomo smgss-fomo--' + (config.position || 'bottom-left');
  wrap.hidden = true;
  wrap.innerHTML = '<a class="smgss-fomo__link" href="#"><img class="smgss-fomo__image" alt=""><span class="smgss-fomo__text"></span></a>';
  document.body.appendChild(wrap);

  const link = wrap.querySelector('.smgss-fomo__link');
  const image = wrap.querySelector('.smgss-fomo__image');
  const text = wrap.querySelector('.smgss-fomo__text');

  let index = 0;
  let hideTimer;

  const show = () => {
    const item = items[index % items.length];
    index += 1;

    const city = item.city ? ' in ' + item.city : '';
    const message = String(config.template || '{{customer}}{{city}} purchased {{product}}')
      .replace('{{customer}}', item.customer || 'Someone')
      .replace('{{city}}', city)
      .replace('{{product}}', item.product || '');

    text.textContent = message;
    image.src = item.image || '';
    image.hidden = !item.image;
    link.href = item.url || '#';
    link.toggleAttribute('aria-disabled', !item.url);

    wrap.hidden = false;
    requestAnimationFrame(() => wrap.classList.add('is-visible'));

    clearTimeout(hideTimer);
    hideTimer = setTimeout(() => {
      wrap.classList.remove('is-visible');
      setTimeout(() => { wrap.hidden = true; }, 250);
    }, Number(config.duration) || 5000);
  };

  setTimeout(() => {
    show();
    setInterval(show, Number(config.interval) || 8000);
  }, Number(config.delay) || 5000);
})();