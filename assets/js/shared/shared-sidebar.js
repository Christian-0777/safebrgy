class SharedSidebar {
  constructor() {
    this.sidebar = document.querySelector('.sidebar');
    this.mainContent = document.querySelector('.main-content');
    this.toggle = document.querySelector('.sidebar-toggle');
    this.backdrop = document.querySelector('.sidebar-backdrop');
    this.mobileNav = document.querySelector('.mobile-nav');
    if (!this.mobileNav && this.toggle?.closest('.header')) {
      this.mobileNav = document.createElement('nav');
      this.mobileNav.className = 'mobile-nav';
      this.mobileNav.setAttribute('aria-label', 'Mobile navigation');
      this.toggle.closest('.header').appendChild(this.mobileNav);
    }
    if (!this.backdrop) {
      this.backdrop = document.createElement('div');
      this.backdrop.className = 'sidebar-backdrop';
      document.body.appendChild(this.backdrop);
    }
    this.mobile = window.innerWidth <= 768;
    if (!this.sidebar) return;
    this.init();
  }
  init() {
    this.setActiveItem();
    this.buildMobileNav();
    this.toggle?.setAttribute('aria-expanded', 'false');
    this.toggle?.setAttribute('aria-controls', 'mobile-navigation');
    this.mobileNav?.setAttribute('id', 'mobile-navigation');
    this.toggle?.addEventListener('click', (event) => { event.stopPropagation(); this.mobile ? this.toggleMobile() : this.toggleDesktop(); });
    this.mobileNav?.addEventListener('click', (event) => {
      if (event.target.closest('a')) this.closeMobile();
    });
    document.addEventListener('click', (event) => {
      if (!event.target.closest('.mobile-nav') && !event.target.closest('.sidebar-toggle')) this.closeMobile();
    });
    window.addEventListener('resize', () => this.handleResize());
  }

  buildMobileNav() {
    if (!this.mobileNav) return;
    const links = [
      ...document.querySelectorAll('.sidebar-menu a'),
      ...document.querySelectorAll('.sidebar-footer a')
    ];
    this.mobileNav.innerHTML = '';
    links.forEach((link) => {
      const clone = link.cloneNode(true);
      clone.classList.remove('active');
      this.mobileNav.appendChild(clone);
    });
  }
  setActiveItem() {
    const current = window.location.pathname.split('/').pop() || 'index.php';
    document.querySelectorAll('.sidebar-menu a').forEach((item) => {
      if ((item.getAttribute('href') || '').split('/').pop().split('?')[0] === current) item.classList.add('active');
    });
  }
  toggleDesktop() { this.sidebar.classList.toggle('collapsed'); this.mainContent?.classList.toggle('collapsed'); }
  toggleMobile() {
    const isOpen = this.mobileNav?.classList.toggle('open') ?? false;
    this.toggle?.setAttribute('aria-expanded', String(isOpen));
  }
  closeMobile() {
    this.mobileNav?.classList.remove('open');
    this.toggle?.setAttribute('aria-expanded', 'false');
  }
  handleResize() {
    const nextMobile = window.innerWidth <= 768;
    if (nextMobile !== this.mobile) {
      this.mobile = nextMobile;
      this.sidebar.classList.remove('open', 'collapsed');
      this.mainContent?.classList.remove('collapsed');
      this.backdrop?.classList.remove('show');
      this.closeMobile();
    }
  }
}
document.addEventListener('DOMContentLoaded', () => { window.sharedSidebar = new SharedSidebar(); });
