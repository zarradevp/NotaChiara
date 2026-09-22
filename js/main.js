(() => {
  const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  const wait = (ms) => new Promise((resolve) => window.setTimeout(resolve, ms));
  const parser = new DOMParser();
  const pageCache = new Map();
  let pageLive = 0;

  const typeText = async (element, text, speed, alive) => {
    const isField = element instanceof HTMLTextAreaElement || element instanceof HTMLInputElement;

    if (isField) {
      element.value = '';
    } else {
      element.textContent = '';
    }

    for (const char of text) {
      if (alive && !alive()) {
        return;
      }

      if (isField) {
        element.value += char;
        element.dispatchEvent(new Event('input'));
      } else {
        element.textContent += char;
      }

      await wait(speed);
    }
  };

  const setupReveal = () => {
    const nodes = document.querySelectorAll('[data-reveal]');

    if (nodes.length === 0) {
      return;
    }

    const revealNow = (node) => {
      node.classList.add('is-visible');
    };

    if (reduceMotion || !('IntersectionObserver' in window)) {
      nodes.forEach(revealNow);
      return;
    }

    const observer = new IntersectionObserver((entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          revealNow(entry.target);
          observer.unobserve(entry.target);
        }
      });
    }, { threshold: 0.08, rootMargin: '0px 0px -6% 0px' });

    nodes.forEach((node) => {
      const rect = node.getBoundingClientRect();

      if (rect.top < window.innerHeight * 0.92) {
        revealNow(node);
        return;
      }

      observer.observe(node);
    });
  };

  const setupCompareDemo = () => {
    const gen = pageLive;
    const alive = () => gen === pageLive;
    const source = document.querySelector('[data-typewriter="before"]');
    const after = document.querySelector('[data-after-panel]');
    const flow = document.querySelector('.nc-compare__flow');

    if (!source || !after) {
      return;
    }

    const fullText = source.textContent ?? '';

    if (reduceMotion) {
      after.classList.add('is-ready');
      return;
    }

    const play = async () => {
      if (!alive()) {
        return;
      }

      after.classList.remove('is-ready');
      flow?.classList.remove('is-active');
      source.textContent = '';
      await wait(400);

      if (!alive()) {
        return;
      }

      await typeText(source, fullText, 18, alive);
      flow?.classList.add('is-active');
      await wait(500);

      if (alive()) {
        after.classList.add('is-ready');
      }
    };

    const loop = async () => {
      await play();

      if (!alive()) {
        return;
      }

      await wait(4200);

      if (alive()) {
        loop();
      }
    };

    loop();
  };

  const examples = {
    disco: {
      service: 'Portale dipendenti',
      severity: 'degradato',
      note: 'Alert: disco /var al 93% su vm-web-02. Apache timeout intermittenti su /login. In corso pulizia log e rotate. ETA 40 min. Non è un problema di password utenti.',
    },
    vpn: {
      service: 'VPN aziendale',
      severity: 'down',
      note: 'Tunnel IPSec down da 20 min su gw-vpn-01. Handshake fallito, utenti non entrano in rete interna. Restart servizio pianificato, ETA 15 minuti.',
    },
    posta: {
      service: 'Posta elettronica',
      severity: 'degradato',
      note: 'Coda SMTP a 8000 messaggi dopo blocco relay. Postfix in retry. Nessuna evidenza di perdita. Smaltimento in corso, circa 1 ora.',
    },
  };

  const updateNoteCount = () => {
    const note = document.querySelector('#note');
    const count = document.querySelector('#note-count');

    if (note && count) {
      count.textContent = String(note.value.length);
    }
  };

  const setupPageControls = () => {
    let typing = 0;

    document.addEventListener('input', (event) => {
      if (event.target.id === 'note') {
        updateNoteCount();
      }
    });

    document.addEventListener('click', async (event) => {
      const tab = event.target.closest('[data-tab]');

      if (tab) {
        const board = tab.closest('.nc-board');
        const key = tab.getAttribute('data-tab');
        const copyMap = { user: 'out-user', ticket: 'out-ticket', status: 'out-status' };

        if (!board || !key) {
          return;
        }

        board.querySelectorAll('[data-tab]').forEach((button) => {
          const active = button === tab;
          button.classList.toggle('is-active', active);
          button.setAttribute('aria-selected', active ? 'true' : 'false');
        });

        board.querySelectorAll('[data-panel]').forEach((panel) => {
          const active = panel.getAttribute('data-panel') === key;
          panel.classList.toggle('is-active', active);
          panel.hidden = !active;
        });

        const copyButton = board.querySelector('[data-copy-active]');

        if (copyButton && copyMap[key]) {
          copyButton.setAttribute('data-copy', copyMap[key]);
        }

        return;
      }

      const copyAll = event.target.closest('[data-copy-all]');

      if (copyAll) {
        const chunks = ['out-user', 'out-ticket', 'out-status']
          .map((id) => document.getElementById(id)?.textContent?.trim() ?? '')
          .filter(Boolean);
        const original = copyAll.textContent;

        try {
          await navigator.clipboard.writeText(chunks.join('\n\n'));
          copyAll.textContent = 'Copiati';
          copyAll.classList.add('is-copied');
        } catch {
          copyAll.textContent = 'Seleziona e copia';
        }

        window.setTimeout(() => {
          copyAll.textContent = original;
          copyAll.classList.remove('is-copied');
        }, 1600);

        return;
      }

      const chip = event.target.closest('[data-example]');

      if (chip) {
        const key = chip.getAttribute('data-example');
        const example = key ? examples[key] : null;
        const form = document.querySelector('.nc-form');
        const note = document.querySelector('#note');
        const service = document.querySelector('#service');

        if (!example || !form) {
          return;
        }

        document.querySelectorAll('[data-example]').forEach((button) => {
          button.classList.toggle('is-active', button === chip);
        });

        if (service) {
          service.value = example.service;
        }

        const radio = form.querySelector(`input[name="severity"][value="${example.severity}"]`);

        if (radio) {
          radio.checked = true;
        }

        if (!note) {
          return;
        }

        typing += 1;
        const run = typing;

        if (reduceMotion) {
          note.value = example.note;
          updateNoteCount();
          note.focus();
          return;
        }

        note.focus();
        await typeText(note, example.note, 8, () => run === typing);

        if (run === typing) {
          updateNoteCount();
        }

        return;
      }

      const copyButton = event.target.closest('[data-copy]');

      if (!copyButton) {
        return;
      }

      const id = copyButton.getAttribute('data-copy');
      const target = id ? document.getElementById(id) : null;

      if (!target) {
        return;
      }

      const original = copyButton.textContent;

      try {
        await navigator.clipboard.writeText(target.textContent || '');
        copyButton.textContent = 'Copiato';
        copyButton.classList.add('is-copied');
      } catch {
        copyButton.textContent = 'Seleziona e copia';
      }

      window.setTimeout(() => {
        copyButton.textContent = original;
        copyButton.classList.remove('is-copied');
      }, 1600);
    });

    document.addEventListener('submit', (event) => {
      if (!event.target.classList.contains('nc-form')) {
        return;
      }

      event.target.querySelector('button[type="submit"]')?.classList.add('is-busy');
    });
  };

  const initPage = () => {
    pageLive += 1;
    updateNoteCount();
    setupReveal();
    setupCompareDemo();
  };

  const setupTheme = () => {
    const storageKey = 'nc-theme';
    const colors = { light: '#fffaf6', dark: '#141210' };
    const button = document.querySelector('[data-theme-toggle]');

    const storedTheme = () => {
      try {
        return localStorage.getItem(storageKey);
      } catch {
        return null;
      }
    };

    const preferredTheme = () => {
      const stored = storedTheme();

      if (stored === 'dark' || stored === 'light') {
        return stored;
      }

      return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
    };

    const applyTheme = (theme) => {
      document.documentElement.setAttribute('data-theme', theme);
      document.documentElement.style.colorScheme = theme;
      document.querySelector('meta[name="theme-color"]')?.setAttribute('content', colors[theme]);

      if (!button) {
        return;
      }

      const isDark = theme === 'dark';
      button.setAttribute('aria-pressed', String(isDark));
      button.setAttribute('aria-label', isDark ? 'Attiva modalità chiara' : 'Attiva modalità notturna');
      button.setAttribute('title', isDark ? 'Modalità chiara' : 'Modalità notturna');
    };

    applyTheme(preferredTheme());

    button?.addEventListener('click', () => {
      const next = document.documentElement.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';

      try {
        localStorage.setItem(storageKey, next);
      } catch {
        // Private mode can block localStorage.
      }

      applyTheme(next);
    });

    const media = window.matchMedia('(prefers-color-scheme: dark)');
    const onSchemeChange = (event) => {
      if (storedTheme() === 'dark' || storedTheme() === 'light') {
        return;
      }

      applyTheme(event.matches ? 'dark' : 'light');
    };

    if (typeof media.addEventListener === 'function') {
      media.addEventListener('change', onSchemeChange);
    } else if (typeof media.addListener === 'function') {
      media.addListener(onSchemeChange);
    }
  };

  const setupHeader = () => {
    const header = document.querySelector('.nc-header');

    if (!header) {
      return;
    }

    const update = () => {
      header.classList.toggle('is-scrolled', window.scrollY > 10);
    };

    update();
    window.addEventListener('scroll', update, { passive: true });
  };

  const snapshotDocument = (doc) => {
    const mainNode = doc.querySelector('main');

    return {
      title: doc.title,
      mainHTML: mainNode?.innerHTML ?? '',
      mainClass: mainNode?.getAttribute('class') ?? '',
    };
  };

  const setupHubNav = () => {
    const nav = document.querySelector('.nc-nav');
    const glider = document.querySelector('.nc-nav__glider');
    const main = document.querySelector('main');

    if (!nav || !glider || !main) {
      return;
    }

    const links = [...nav.querySelectorAll('a')];

    const pageKey = (href) => {
      const file = new URL(href, window.location.href).pathname.split('/').pop() || 'index.php';

      if (file === '' || file === 'index.php' || file === 'index.html') {
        return 'home';
      }

      if (file.endsWith('.php')) {
        return file.replace('.php', '');
      }

      return 'other';
    };

    const pageIndex = (key) => links.findIndex((link) => pageKey(link.href) === key);

    const gliderMetrics = (link) => {
      const navBox = nav.getBoundingClientRect();
      const box = link.getBoundingClientRect();

      return {
        left: box.left - navBox.left,
        top: box.top - navBox.top,
        width: box.width,
        height: box.height,
      };
    };

    const setGlider = (metrics, moving) => {
      glider.classList.toggle('is-moving', moving);
      glider.style.width = `${metrics.width}px`;
      glider.style.height = `${metrics.height}px`;
      glider.style.transform = `translate(${metrics.left}px, ${metrics.top}px)`;
      glider.classList.add('is-on');
    };

    const currentLink = () => nav.querySelector('[aria-current="page"]') || links[0];

    const placeCurrent = (moving) => {
      const active = currentLink();

      if (active) {
        setGlider(gliderMetrics(active), moving);
      }
    };

    const livePages = new Set(['stato', 'archivio']);
    let navigating = false;
    let swapGen = 0;
    let transitionTimer = 0;

    const applyDir = (dir, kind) => {
      main.classList.remove('nc-enter-right', 'nc-enter-left', 'nc-leave-right', 'nc-leave-left');

      if (dir && kind) {
        main.classList.add(`nc-${kind}-${dir}`);
      }
    };

    const hardNav = (href, dir) => {
      try {
        if (dir) {
          sessionStorage.setItem('nc-nav-dir', dir);
        }
      } catch {
        // Private mode can block sessionStorage.
      }

      window.location.href = href;
    };

    const rememberPage = () => {
      const key = pageKey(window.location.href);

      if (key === 'home' && pageCache.has(key)) {
        return;
      }

      pageCache.set(key, snapshotDocument(document));
    };

    const loadPage = async (url, force = false) => {
      const key = pageKey(url);

      if (!force && pageCache.has(key)) {
        return pageCache.get(key);
      }

      const response = await fetch(url, { credentials: 'same-origin' });

      if (!response.ok) {
        throw new Error('Prefetch failed');
      }

      const html = await response.text();
      const doc = parser.parseFromString(html, 'text/html');
      const page = snapshotDocument(doc);

      pageCache.set(key, page);
      return page;
    };

    const prefetchHub = () => {
      links.forEach((link) => {
        loadPage(link.href).catch(() => {});
      });
    };

    const updateNav = (key) => {
      links.forEach((item) => {
        const active = pageKey(item.href) === key;
        item.classList.remove('is-target');

        if (active) {
          item.setAttribute('aria-current', 'page');
        } else {
          item.removeAttribute('aria-current');
        }
      });
    };

    const finishTransition = (gen) => {
      if (gen !== swapGen) {
        return;
      }

      window.clearTimeout(transitionTimer);
      document.documentElement.classList.remove('nc-is-transitioning');
      applyDir('', '');
      navigating = false;
    };

    const swapTo = async (url, dir, push) => {
      const gen = ++swapGen;
      const key = pageKey(url.href);
      const fromKey = pageKey(window.location.href);
      const pending = loadPage(url.href, livePages.has(key));

      window.clearTimeout(transitionTimer);

      if (fromKey !== 'home') {
        rememberPage();
      }

      const targetLink = links.find((item) => pageKey(item.href) === key);

      links.forEach((item) => item.classList.toggle('is-target', item === targetLink));
      document.documentElement.classList.add('nc-is-transitioning');
      document.documentElement.classList.remove('nc-enter-pending-right', 'nc-enter-pending-left');

      if (targetLink) {
        window.requestAnimationFrame(() => setGlider(gliderMetrics(targetLink), true));
      }

      if (!reduceMotion && dir) {
        applyDir(dir, 'leave');
        await wait(320);
      }

      if (gen !== swapGen) {
        return;
      }

      let page = pageCache.get(key);

      try {
        page = await pending;
      } catch {
        page = pageCache.get(key);
      }

      if (gen !== swapGen) {
        return;
      }

      if (!page) {
        hardNav(url.href, dir);
        return;
      }

      pageLive += 1;
      main.innerHTML = page.mainHTML;
      document.title = page.title;
      updateNav(key);
      window.scrollTo(0, 0);

      if (push) {
        history.pushState({ hub: key }, page.title, url.pathname + url.search);
      }

      const enterClass = !reduceMotion && dir ? ` nc-enter-${dir}` : '';
      main.className = `${page.mainClass}${enterClass}`.trim();
      initPage();
      placeCurrent(false);

      if (!enterClass) {
        finishTransition(gen);
        return;
      }

      const onEnterEnd = (event) => {
        if (event.target !== main || gen !== swapGen) {
          return;
        }

        main.removeEventListener('animationend', onEnterEnd);
        finishTransition(gen);
      };

      main.addEventListener('animationend', onEnterEnd);
      transitionTimer = window.setTimeout(() => finishTransition(gen), 700);
    };

    rememberPage();
    prefetchHub();
    history.replaceState({ hub: pageKey(window.location.href) }, document.title, window.location.href);

    const startGlider = () => placeCurrent(false);

    if (document.fonts?.ready) {
      document.fonts.ready.then(startGlider);
    } else {
      startGlider();
    }

    window.setTimeout(() => {
      document.documentElement.classList.remove('nc-enter-pending-right', 'nc-enter-pending-left');
    }, 520);

    window.addEventListener('resize', () => placeCurrent(false));

    if (typeof ResizeObserver === 'function') {
      new ResizeObserver(() => placeCurrent(false)).observe(nav);
    }

    links.forEach((link) => {
      link.addEventListener('pointerenter', () => {
        const key = pageKey(link.href);
        loadPage(link.href, livePages.has(key)).catch(() => {});
      });
    });

    document.addEventListener('click', (event) => {
      const link = event.target.closest('a[href]');

      if (!link || event.defaultPrevented || event.button !== 0 || navigating) {
        return;
      }

      if (event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) {
        return;
      }

      if (link.target && link.target !== '_self') {
        return;
      }

      const hrefAttr = link.getAttribute('href');

      if (!hrefAttr || hrefAttr.startsWith('#') || hrefAttr.startsWith('mailto:')) {
        return;
      }

      let nextUrl;

      try {
        nextUrl = new URL(link.href, window.location.href);
      } catch {
        return;
      }

      if (nextUrl.origin !== window.location.origin) {
        return;
      }

      const fromKey = pageKey(window.location.href);
      const toKey = pageKey(nextUrl.href);
      const from = pageIndex(fromKey);
      const to = pageIndex(toKey);

      if (from < 0 || to < 0) {
        return;
      }

      event.preventDefault();

      if (from === to) {
        window.scrollTo({ top: 0, behavior: reduceMotion ? 'auto' : 'smooth' });
        return;
      }

      navigating = true;
      swapTo(nextUrl, to > from ? 'right' : 'left', true).catch(() => {
        hardNav(nextUrl.href, to > from ? 'right' : 'left');
      });
    });

    window.addEventListener('popstate', () => {
      const toKey = pageKey(window.location.href);
      const to = pageIndex(toKey);
      const current = links.findIndex((item) => item.getAttribute('aria-current') === 'page');

      if (to < 0) {
        window.location.reload();
        return;
      }

      const dir = to === current ? '' : (to > current ? 'right' : 'left');
      const url = new URL(window.location.href);

      navigating = true;
      swapTo(url, dir, false).catch(() => window.location.reload());
    });
  };

  setupTheme();
  setupHeader();
  setupPageControls();
  setupHubNav();
  initPage();
})();
