/* =====================================================
   MUNCAK.KUY — MAIN JAVASCRIPT
   Preloader, navbar, parallax, scroll reveal, toast, dsb.
   ===================================================== */

document.addEventListener('DOMContentLoaded', () => {

  /* ---------- PRELOADER ---------- */
  /* ---------- PRELOADER ---------- */
const preloader = document.getElementById('preloader');

if (preloader) {
  const hidePreloader = () => {
    preloader.classList.add('hidden');

    // Pastikan benar-benar tidak menghalangi halaman
    setTimeout(() => {
      preloader.style.display = 'none';
    }, 650);
  };

  if (document.readyState === 'complete') {
    setTimeout(hidePreloader, 350);
  } else {
    window.addEventListener('load', () => {
      setTimeout(hidePreloader, 350);
    });
  }

  // Fallback
  setTimeout(hidePreloader, 2500);
}

  /* ---------- NAVBAR SCROLL STATE ---------- */
  const navbar = document.querySelector('.navbar');
  if (navbar) {
    const onScroll = () => navbar.classList.toggle('scrolled', window.scrollY > 30);
    window.addEventListener('scroll', onScroll);
    onScroll();
  }

  /* ---------- MOBILE HAMBURGER ---------- */
  const hamburger = document.querySelector('.hamburger');
  const navLinks = document.querySelector('.nav-links');
  if (hamburger && navLinks) {
    hamburger.addEventListener('click', () => navLinks.classList.toggle('open'));
  }
  const sidebarToggle = document.querySelector('.sidebar-toggle');
  const sidebar = document.querySelector('.sidebar');
  if (sidebarToggle && sidebar) {
    sidebarToggle.addEventListener('click', () => sidebar.classList.toggle('open'));
  }



  /* ---------- PARALLAX TIPIS PADA HERO ---------- */
  const heroBg = document.querySelector('.hero-bg');
  if (heroBg) {
    window.addEventListener('scroll', () => {
      const offset = window.scrollY;
      if (offset < window.innerHeight) {
        heroBg.style.transform = `scale(1.08) translateY(${offset * 0.18}px)`;
      }
    }, { passive: true });
  }

  /* ---------- SCROLL REVEAL (Intersection Observer) ---------- */
  const revealEls = document.querySelectorAll('.reveal, .reveal-scale');
  if (revealEls.length) {
    const observer = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.classList.add('in-view');
          observer.unobserve(entry.target);
        }
      });
    }, { threshold: 0.15, rootMargin: '0px 0px -40px 0px' });
    revealEls.forEach(el => observer.observe(el));
  }

  /* ---------- INIT TOAST DARI SESSION (PHP -> JS) ---------- */
  if (window.__TOAST__) {
    showToast(window.__TOAST__.type, window.__TOAST__.message);
  }

  /* ---------- QTY CONTROL DI HALAMAN BOOKING ---------- */
  document.querySelectorAll('.qty-control').forEach(control => {
    const input = control.querySelector('input[type="number"]');
    const minBtn = control.querySelector('.qty-min');
    const plusBtn = control.querySelector('.qty-plus');
    if (!input) return;
    const clamp = (v) => Math.max(parseInt(input.min || 1), Math.min(parseInt(input.max || 10), v));
    const trigger = () => input.dispatchEvent(new Event('change'));
    if (minBtn) minBtn.addEventListener('click', () => { input.value = clamp(parseInt(input.value) - 1); trigger(); });
    if (plusBtn) plusBtn.addEventListener('click', () => { input.value = clamp(parseInt(input.value) + 1); trigger(); });
  });

  /* ---------- WISHLIST HEART TOGGLE (AJAX) ---------- */
  document.querySelectorAll('.wishlist-btn').forEach(btn => {
    btn.addEventListener('click', async (e) => {
      e.preventDefault();
      const gunungId = btn.dataset.gunungId;
      try {
        const res = await fetch(`${window.BASE_URL || ''}/ajax_wishlist.php`, {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
          body: `gunung_id=${encodeURIComponent(gunungId)}`
        });
        const data = await res.json();
        if (data.status === 'ok') {
          btn.classList.toggle('active', data.action === 'added');
          showToast('success', data.action === 'added' ? 'Ditambahkan ke wishlist' : 'Dihapus dari wishlist');
        } else {
          showToast('error', data.message || 'Silakan login dahulu.');
        }
      } catch (err) {
        showToast('error', 'Terjadi kesalahan jaringan.');
      }
    });
  });
});

/* =====================================================
   TOAST NOTIFICATION — pengganti alert() bawaan browser
   Cara pakai: showToast('success' | 'error' | 'warning' | 'info', 'Pesan...')
   ===================================================== */
function showToast(type = 'success', message = '') {
  let container = document.getElementById('toast-container');
  if (!container) {
    container = document.createElement('div');
    container.id = 'toast-container';
    document.body.appendChild(container);
  }

  const icons = {
    success: '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>',
    error:   '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>',
    warning: '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>',
    info:    '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>',
  };

  const toast = document.createElement('div');
  toast.className = `toast ${type}`;
  toast.innerHTML = `
    <div class="icon">${icons[type] || icons.info}</div>
    <div class="msg">${message}</div>
    <span class="close-toast">&times;</span>
  `;
  container.appendChild(toast);

  const remove = () => {
    toast.classList.add('hide');
    setTimeout(() => toast.remove(), 350);
  };
  toast.querySelector('.close-toast').addEventListener('click', remove);
  setTimeout(remove, 4500);
}

/* =====================================================
   NAVIGATION BACK / KEMBALI
   Mengikuti riwayat halaman browser.
   Dipakai oleh tombol dengan class .btn-back
   ===================================================== */

function goBack() {
  /*
   * Jika masih memiliki riwayat halaman sebelumnya,
   * browser akan kembali ke halaman sebelumnya.
   */
  if (window.history.length > 1) {
    window.history.back();
    return;
  }

  /*
   * Jika halaman dibuka langsung tanpa riwayat,
   * gunakan Beranda sebagai fallback.
   */
  const baseUrl = window.BASE_URL || '';
  window.location.href = `${baseUrl}/index.php`;
}


/* Event delegation:
   tombol Back bisa ditambahkan ke halaman mana pun
   tanpa perlu menambahkan event listener satu per satu.
*/
document.addEventListener('click', (event) => {
  const backButton = event.target.closest('.btn-back');

  if (!backButton) return;

  event.preventDefault();
  goBack();
});