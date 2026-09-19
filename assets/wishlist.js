(() => {
  const config = window.smgSiteSuiteWishlist;
  if (!config) return;

  const setState = (productId, added, text) => {
    document.querySelectorAll('.smgss-wishlist-toggle[data-product-id="' + productId + '"]').forEach((button) => {
      button.classList.toggle('is-active', added);
      button.setAttribute('aria-pressed', added ? 'true' : 'false');
      button.textContent = text;
    });

    if (!added) {
      document.querySelectorAll('.smgss-wishlist-item[data-product-id="' + productId + '"]').forEach((item) => item.remove());
      const grid = document.querySelector('.smgss-wishlist-grid');
      if (grid && !grid.querySelector('.smgss-wishlist-item')) {
        grid.outerHTML = '<div class="smgss-wishlist-empty">Your wishlist is empty.</div>';
      }
    }

    document.dispatchEvent(new CustomEvent('smgSiteSuiteWishlistChanged', {
      detail: { productId: Number(productId), added }
    }));
  };

  document.addEventListener('click', async (event) => {
    const button = event.target.closest('.smgss-wishlist-toggle');
    if (!button) return;

    event.preventDefault();
    const productId = button.dataset.productId;
    if (!productId || button.disabled) return;
    button.disabled = true;

    const body = new URLSearchParams({
      action: 'smg_site_suite_wishlist_toggle',
      nonce: config.nonce,
      product_id: productId,
    });

    try {
      const response = await fetch(config.ajaxUrl, {
        method: 'POST',
        credentials: 'same-origin',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
        body,
      });
      const result = await response.json();
      if (!result.success) throw new Error(result?.data?.message || 'Unable to update wishlist.');
      setState(productId, Boolean(result.data.added), result.data.text || (result.data.added ? config.removeText : config.addText));
    } catch (error) {
      window.alert(error.message);
    } finally {
      button.disabled = false;
    }
  });
})();