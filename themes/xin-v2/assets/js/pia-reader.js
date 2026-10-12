(function(){
'use strict';
var shell=document.querySelector('[data-xv-reader]');
if(!shell)return;
var overlay=document.querySelector('.xv-pia-reader-backdrop');
var panels={toc:document.getElementById('xv-reader-toc-panel'),settings:document.getElementById('xv-reader-settings-panel')};
var opener=null;
function close(restore){Object.keys(panels).forEach(function(key){if(panels[key])panels[key].hidden=true;});document.querySelectorAll('[data-xv-reader-panel]').forEach(function(button){button.setAttribute('aria-expanded','false');});if(overlay)overlay.hidden=true;document.body.classList.remove('xv-reader-panel-open');if(restore&&opener)opener.focus();}
document.addEventListener('click',function(event){var button=event.target.closest('[data-xv-reader-panel]');if(button){var panel=panels[button.dataset.xvReaderPanel];if(!panel)return;var open=panel.hidden;close(false);if(open){opener=button;panel.hidden=false;button.setAttribute('aria-expanded','true');if(overlay)overlay.hidden=false;document.body.classList.add('xv-reader-panel-open');var first=panel.querySelector('button,a,input');if(first)first.focus();}return;}if(event.target.closest('[data-xv-reader-dismiss]'))close(true);});
document.addEventListener('keydown',function(event){if(event.key==='Escape')close(true);if(event.key==='Tab'&&document.body.classList.contains('xv-reader-panel-open')){var panel=Object.values(panels).find(function(item){return item&&!item.hidden;});if(!panel)return;var focusable=Array.from(panel.querySelectorAll('a[href],button:not([disabled]),input,select')).filter(function(item){return item.getClientRects().length;});if(!focusable.length)return;var first=focusable[0],last=focusable[focusable.length-1];if(event.shiftKey&&document.activeElement===first){event.preventDefault();last.focus();}else if(!event.shiftKey&&document.activeElement===last){event.preventDefault();first.focus();}}});
})();
