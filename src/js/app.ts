/**
 * Site-wide behaviour: mobile navigation and dropdown sub-menus.
 * Loaded as an ES module on every page; keep it small and dependency-free.
 */

const DESKTOP = window.matchMedia('(min-width: 64rem)');

function setSubmenu(toggle: HTMLButtonElement, open: boolean): void {
  const submenu = document.getElementById(toggle.getAttribute('aria-controls') ?? '');
  toggle.setAttribute('aria-expanded', String(open));
  submenu?.classList.toggle('is-open', open);
}

function closeSubmenus(scope: ParentNode, except?: HTMLButtonElement): void {
  scope.querySelectorAll<HTMLButtonElement>('.cik-menu__toggle[aria-expanded="true"]').forEach((toggle) => {
    if (toggle !== except) setSubmenu(toggle, false);
  });
}

function initSubmenus(nav: HTMLElement): void {
  nav.addEventListener('click', (event) => {
    const toggle = (event.target as Element).closest<HTMLButtonElement>('.cik-menu__toggle');
    if (!toggle) return;

    const open = toggle.getAttribute('aria-expanded') !== 'true';
    // On desktop only one branch of dropdowns is visible at a time.
    if (open && DESKTOP.matches) {
      const branch = toggle.closest('.sub-menu') ?? nav;
      closeSubmenus(branch, toggle);
    }
    setSubmenu(toggle, open);
  });

  nav.addEventListener('keydown', (event) => {
    if (event.key !== 'Escape') return;
    const submenu = (event.target as Element).closest<HTMLElement>('.sub-menu.is-open');
    if (!submenu) return;

    const toggle = nav.querySelector<HTMLButtonElement>(`[aria-controls="${submenu.id}"]`);
    if (toggle) {
      event.stopPropagation();
      setSubmenu(toggle, false);
      toggle.focus();
    }
  });

  document.addEventListener('click', (event) => {
    if (DESKTOP.matches && !nav.contains(event.target as Node)) closeSubmenus(nav);
  });

  nav.addEventListener('focusout', (event) => {
    const next = event.relatedTarget as Node | null;
    if (DESKTOP.matches && next && !nav.contains(next)) closeSubmenus(nav);
  });
}

function initMobileMenu(toggle: HTMLButtonElement, panel: HTMLElement): void {
  const setOpen = (open: boolean): void => {
    toggle.setAttribute('aria-expanded', String(open));
    panel.classList.toggle('is-open', open);
    document.body.classList.toggle('cik-menu-open', open);
  };

  toggle.addEventListener('click', () => {
    const open = toggle.getAttribute('aria-expanded') !== 'true';
    setOpen(open);
    if (open) panel.querySelector<HTMLElement>('a, button')?.focus();
  });

  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape' && toggle.getAttribute('aria-expanded') === 'true') {
      setOpen(false);
      toggle.focus();
    }
  });

  DESKTOP.addEventListener('change', (event) => {
    if (event.matches) setOpen(false);
  });
}

const nav = document.getElementById('cik-primary-nav');
const menuToggle = document.getElementById('cik-menu-toggle');

if (nav) initSubmenus(nav);
if (nav && menuToggle instanceof HTMLButtonElement) initMobileMenu(menuToggle, nav);
