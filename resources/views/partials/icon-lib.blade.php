@include('partials.icons')
<script>
  // Icon hydrate: <span data-icon="leaf" data-ic-class="ic-sm"></span>
  // Paths live in window.ICONS (JSON). Parsing happens on a real <svg> element:
  // a <template> HTML-parses void tags (<path>, <circle>), nesting them and breaking the icon.
  window.ic = (name, cls = 'ic') => {
    const ns = 'http://www.w3.org/2000/svg';
    const svg = document.createElementNS(ns, 'svg');
    ['viewBox:0 0 24 24', 'fill:none', 'stroke:currentColor', 'stroke-width:2',
     'stroke-linecap:round', 'stroke-linejoin:round'].forEach(a => {
      const i = a.indexOf(':'); svg.setAttribute(a.slice(0, i), a.slice(i + 1));
    });
    svg.setAttribute('class', cls);
    const tmp = document.createElementNS(ns, 'svg');
    tmp.innerHTML = window.ICONS[name] ?? '';
    [...tmp.children].forEach(n => svg.appendChild(n.cloneNode(true)));
    return svg;
  };
  window.hydrateIcons = (root = document) => {
    root.querySelectorAll('[data-icon]').forEach(el => {
      if (el.querySelector('svg')) return;
      el.appendChild(window.ic(el.dataset.icon, el.dataset.icClass || 'ic'));
    });
  };
  document.addEventListener('DOMContentLoaded', () => {
    window.hydrateIcons();
    // flash auto-dismiss
    const f = document.querySelector('.flash');
    if (f) setTimeout(() => { f.classList.add('out'); setTimeout(() => f.remove(), 400); }, 3500);
  });
</script>
