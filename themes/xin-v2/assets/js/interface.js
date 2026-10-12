(function(){
'use strict';
document.querySelectorAll('[data-xv-curation]').forEach(function(root){
 const track=root.querySelector('[data-xv-curation-track]'), cards=Array.from(track.children), dots=Array.from(root.querySelectorAll('[data-xv-curation-dot]'));
 const reduced=matchMedia('(prefers-reduced-motion: reduce)');
 function current(){let nearest=0,distance=Infinity;cards.forEach((card,i)=>{const d=Math.abs(card.offsetLeft-cards[0].offsetLeft-track.scrollLeft);if(d<distance){distance=d;nearest=i;}});return nearest;}
 function go(index){track.scrollTo({left:cards[index].offsetLeft-cards[0].offsetLeft,behavior:reduced.matches?'instant':'smooth'});}
 function sync(){const at=current();dots.forEach((dot,i)=>dot.setAttribute('aria-pressed',String(i===at)));root.querySelector('[data-xv-curation-prev]').disabled=track.scrollLeft<2;root.querySelector('[data-xv-curation-next]').disabled=track.scrollLeft+track.clientWidth>=track.scrollWidth-2;}
 root.querySelector('[data-xv-curation-prev]').addEventListener('click',()=>go(Math.max(0,current()-1)));
 root.querySelector('[data-xv-curation-next]').addEventListener('click',()=>go(Math.min(cards.length-1,current()+1)));
 dots.forEach((dot,i)=>dot.addEventListener('click',()=>go(i)));
 track.addEventListener('scroll',sync,{passive:true});window.addEventListener('resize',sync);
 track.addEventListener('keydown',event=>{if(!['ArrowLeft','ArrowRight','Home','End'].includes(event.key))return;event.preventDefault();go(event.key==='Home'?0:event.key==='End'?cards.length-1:Math.max(0,Math.min(cards.length-1,current()+(event.key==='ArrowRight'?1:-1))));});sync();
});
document.querySelectorAll('[data-xv-rate-panel]').forEach(function(root){
 const buttons=Array.from(root.querySelectorAll('[data-xv-rate-value]')),status=root.querySelector('.xv-rate-status');
 buttons.forEach(button=>button.addEventListener('click',async function(){
  buttons.forEach(b=>b.disabled=true);status.textContent='';
  try{
   const response=await fetch(xvData.root+'rate',{method:'POST',credentials:'same-origin',headers:{'Content-Type':'application/json','X-WP-Nonce':xvData.nonce},body:JSON.stringify({novel:Number(root.dataset.novel),value:Number(button.dataset.xvRateValue)})});
   const result=await response.json();if(!response.ok)throw new Error(result.message||root.dataset.error);
   buttons.forEach(b=>b.setAttribute('aria-pressed',String(b===button)));status.textContent=root.dataset.saved+' · '+button.dataset.xvRateValue+'/5';
   const rating=document.querySelector('.xv-world-title-rating');
   if(rating){rating.querySelector('b').textContent=new Intl.NumberFormat(document.documentElement.lang,{minimumFractionDigits:1,maximumFractionDigits:1}).format(result.value);rating.querySelector('a').textContent=result.label+' ›';const stars=rating.querySelector('.xv-stars');if(stars && result.stars)stars.outerHTML=result.stars;}
  }catch(error){status.textContent=error.message||root.dataset.error;}finally{buttons.forEach(b=>b.disabled=false);}
 }));
});
})();
