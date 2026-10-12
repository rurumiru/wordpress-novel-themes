(() => {
  'use strict';
  const activateSpotlight = (root, index) => {
    const panels = [...root.querySelectorAll('[data-xv-spotlight]')];
    const picks = [...root.querySelectorAll('[data-xv-spotlight-pick]')];
    panels.forEach((panel, i) => {
      const active = i === index;
      panel.hidden = !active;
      panel.classList.toggle('is-active', active);
      panel.querySelectorAll('a').forEach(link => link.tabIndex = active ? 0 : -1);
    });
    picks.forEach((pick, i) => {
      const active = i === index;
      pick.classList.toggle('is-active', active);
      pick.setAttribute('aria-pressed', String(active));
    });
  };
  const setupBookTabs = page => {
    const nav = page.querySelector('.xv-book-jump');
    const body = page.querySelector('.xv-novel-body');
    const chapters = page.querySelector('#xv-book-chapters');
    if (!nav || !body || !chapters) return;
    const synopsis = page.querySelector('[data-xv-synopsis]');
    const comments = page.querySelector('.xv-novel-comments');
    const existingOverview = page.querySelector('#xv-book-description');
    const overview = existingOverview || document.createElement('section');
    overview.dataset.xvBookPanel = 'overview';
    overview.hidden = true;
    overview.classList.add('xv-book-overview');
    if (synopsis) {
      const heading = synopsis.querySelector('h2');
      if (heading) heading.remove();
      overview.append(synopsis);
      synopsis.hidden = false;
    }
    body.insertBefore(overview, chapters);
    chapters.dataset.xvBookPanel = 'chapters';
    let discussion = null;
    if (comments) {
      discussion = document.createElement('section');
      discussion.dataset.xvBookPanel = 'discussion';
      discussion.hidden = true;
      discussion.className = 'xv-book-discussion';
      discussion.append(comments);
      body.insertBefore(discussion, page.querySelector(':scope > .xv-novel-body > aside'));
    }
    const map = new Map([['#xv-book-description', 'overview'], ['#xv-book-chapters', 'chapters'], ['#comments', 'discussion']]);
    const tabs = [...nav.querySelectorAll('a')].map(link => {
      const key = map.get(link.getAttribute('href'));
      if (!key || (key === 'discussion' && !discussion) || (key === 'overview' && !synopsis && !existingOverview)) {
        if ((key === 'discussion' && !discussion) || (key === 'overview' && !synopsis && !existingOverview)) link.remove();
        return null;
      }
      link.dataset.xvBookTab = key;
      link.setAttribute('role', 'tab');
      link.setAttribute('aria-selected', 'false');
      return link;
    }).filter(Boolean);
    nav.setAttribute('role', 'tablist');
    const panels = [...body.querySelectorAll('[data-xv-book-panel]')];
    panels.forEach(panel => {
      panel.id = 'xv-panel-' + panel.dataset.xvBookPanel;
      panel.setAttribute('role', 'tabpanel');
      panel.tabIndex = 0;
    });
    tabs.forEach(tab => {
      tab.id = 'xv-tab-' + tab.dataset.xvBookTab;
      tab.setAttribute('aria-controls', 'xv-panel-' + tab.dataset.xvBookTab);
    });
    const show = key => {
      const valid = panels.some(panel => panel.dataset.xvBookPanel === key);
      if (!valid) key = 'chapters';
      tabs.forEach(tab => {
        const active = tab.dataset.xvBookTab === key;
        tab.setAttribute('aria-selected', String(active));
        if (active) tab.setAttribute('aria-current', 'location'); else tab.removeAttribute('aria-current');
      });
      panels.forEach(panel => panel.hidden = panel.dataset.xvBookPanel !== key);
      if (history.replaceState) history.replaceState(null, '', key === 'chapters' ? location.pathname + location.search : '#' + (key === 'overview' ? 'xv-book-description' : 'comments'));
    };
    page.querySelectorAll('.xv-world-title-rating a[href="#comments"]').forEach(link => link.addEventListener('click', event => { event.preventDefault(); show('discussion'); }));
    tabs.forEach((tab, index) => {
      tab.addEventListener('click', event => { event.preventDefault(); show(tab.dataset.xvBookTab); });
      tab.addEventListener('keydown', event => {
        if (!['ArrowLeft', 'ArrowRight', 'Home', 'End'].includes(event.key)) return;
        event.preventDefault();
        const next = event.key === 'Home' ? 0 : event.key === 'End' ? tabs.length - 1 : (index + (event.key === 'ArrowRight' ? 1 : -1) + tabs.length) % tabs.length;
        tabs[next].focus(); show(tabs[next].dataset.xvBookTab);
      });
    });
    const initial = location.hash === '#comments' ? 'discussion' : location.hash === '#xv-book-chapters' ? 'chapters' : 'overview';
    show(initial);
  };
  document.addEventListener('click', event => {
    if (event.target.closest('[data-xv-announcement-close]')) {
      const announcement = document.querySelector('[data-xv-announcement]');
      if (announcement) announcement.hidden = true;
    }
    const arrow = event.target.closest('[data-xv-carousel-prev], [data-xv-carousel-next]');
    if (arrow) {
      const root = arrow.closest('.xv-home-spotlight');
      const panels = [...root.querySelectorAll('[data-xv-spotlight]')];
      const current = panels.findIndex(panel => !panel.hidden);
      activateSpotlight(root, (current + (arrow.hasAttribute('data-xv-carousel-next') ? 1 : -1) + panels.length) % panels.length);
    }
    const pick = event.target.closest('[data-xv-spotlight-pick]');
    if (pick) {
      const root = pick.closest('.xv-home-spotlight');
      if (root) activateSpotlight(root, Number(pick.dataset.xvSpotlightPick));
    }
  });
  document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.xv-home-spotlight').forEach(root => activateSpotlight(root, 0));
    document.querySelectorAll('.xv-title-page').forEach(setupBookTabs);
  });
})();
(function(){
'use strict';
document.querySelectorAll('[data-xv-ranking]').forEach(function(root){
 var tabs=Array.from(root.querySelectorAll('[data-xv-rank-tab]'));
 function show(tab){tabs.forEach(function(item){item.setAttribute('aria-selected',String(item===tab));item.tabIndex=item===tab?0:-1;});root.querySelectorAll('[data-xv-rank-panel]').forEach(function(panel){panel.hidden=panel.dataset.xvRankPanel!==tab.dataset.xvRankTab;});}
 tabs.forEach(function(tab,index){tab.addEventListener('click',function(){show(tab);});tab.addEventListener('keydown',function(event){if(!['ArrowLeft','ArrowRight','Home','End'].includes(event.key))return;event.preventDefault();var next=event.key==='Home'?0:event.key==='End'?tabs.length-1:(index+(event.key==='ArrowRight'?1:-1)+tabs.length)%tabs.length;show(tabs[next]);tabs[next].focus();});});if(tabs.length)show(tabs[0]);
});
})();
