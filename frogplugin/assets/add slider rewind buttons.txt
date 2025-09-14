(function(){
  function createRewindBtn(swiper, nextBtn){
    const btn = document.createElement('button');
    btn.className = 'swiper-rewind-button';
    btn.innerHTML = '⟲'; // you can swap with an SVG
    btn.title = 'Rewind to start';

    // Copy position from nextBtn ⟲◀◀
    const style = window.getComputedStyle(nextBtn);
    btn.style.position = 'absolute';
    //btn.style.top = style.top;
    //btn.style.right = style.right;
    //btn.style.left = style.left;
    //btn.style.bottom = style.bottom;
    //btn.style.width = style.width;
    //btn.style.height = style.height;

    // Basic styling
    btn.style.background = 'rgba(0,0,0,0.0)';
    btn.style.color = '#fff';
    btn.style.border = 'none';
    //btn.style.borderRadius = '50%';
    btn.style.cursor = 'pointer';
    btn.style.zIndex = '10';

    btn.addEventListener('click', e=>{
      e.stopPropagation();
      if(typeof swiper.slideToLoop==='function'){
        swiper.slideToLoop(0,600);
      } else {
        swiper.slideTo(0,600);
      }
    });

    return btn;
  }

  function attachRewindLogic(swiper){
    let rewindBtn = null;
    const container = swiper.el;
    const nextBtn = container.querySelector('.swiper-button-next');
    if(!nextBtn) return;

    function update(){
      const isLocked = nextBtn.classList.contains('swiper-button-lock');
      const isDisabled = nextBtn.classList.contains('swiper-button-disabled') ||
                         nextBtn.offsetParent === null ||
                         window.getComputedStyle(nextBtn).display === 'none';

      if (!isLocked && isDisabled && !rewindBtn){
        // Insert rewind button when next is disabled
        rewindBtn = createRewindBtn(swiper, nextBtn);
        nextBtn.parentNode.appendChild(rewindBtn);
      } else if ((isLocked || !isDisabled) && rewindBtn){
        // Remove rewind button if next is active again OR locked
        rewindBtn.remove();
        rewindBtn = null;
      }
    }

    // Hook into Swiper events
    swiper.on('slideChange', update);
    swiper.on('reachEnd', update);
    swiper.on('fromEdge', update);
	swiper.on('transitionEnd', update);
	  
    // Initial check
    update();
  }

  // Attach logic to all existing Swipers
  function init(){
    document.querySelectorAll('.swiper, .swiper-container').forEach(el=>{
      const swiper = el.swiper;
      if(swiper && !swiper._rewindLogicAttached){
        swiper._rewindLogicAttached = true;
        attachRewindLogic(swiper);
      }
    });
  }

  // Initial run and watch DOM for new swipers
  init();
  const observer = new MutationObserver(init);
  observer.observe(document.body,{childList:true,subtree:true});
})();
