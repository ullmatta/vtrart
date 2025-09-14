// Lightbox Integration - Faster closing and drag prevention

// Faster close functionality
let mouseDownPos = null;

document.addEventListener('mousedown', function(e) {
    if (e.button !== 0) return; // Only left click
    if (e.target.classList.contains('pgc-img')) {
        mouseDownPos = { x: e.clientX, y: e.clientY };
    }
});

document.addEventListener('mouseup', function(e) {
    if (e.button !== 0) return; // Only left click
    if (e.target.classList.contains('pgc-img') && mouseDownPos) {
        const dx = e.clientX - mouseDownPos.x;
        const dy = e.clientY - mouseDownPos.y;
        const distance = Math.sqrt(dx * dx + dy * dy);

        // Only trigger close if it's a click, not a drag
        if (distance < 5) {
            const closeBtn = document.querySelector('.pgc-rev-item-menu-button[data-type="close"]');
            if (closeBtn) {
                closeBtn.click();
                document.body.classList.remove('lightbox-open'); // update cursor class
            }
        }

        mouseDownPos = null;
    }
});

// Shield to stop lightbox on drag
let shield = null;

document.addEventListener('pointerdown', e => {
  // Only act if the event is on a swiper slide image
  const slide = e.target.closest('.swiper-slide img');
  if (!slide) return;

  // Ignore pointerdowns inside the lightbox
  if (e.target.closest('.pgc-rev-lb-b-content')) return;

  let startX = e.clientX;
  let startY = e.clientY;
  let isDragging = false;

  function pointerMoveHandler(ev) {
    if (!isDragging && (Math.abs(ev.clientX - startX) > 10 || Math.abs(ev.clientY - startY) > 10)) {
      isDragging = true;
      // console.log('drag detected on slide image');

      if (!shield) {
        const rect = slide.getBoundingClientRect();
        shield = document.createElement('div');
        shield.style.position = 'fixed';
        shield.style.top = rect.top + 'px';
        shield.style.left = rect.left + 'px';
        shield.style.width = rect.width + 'px';
        shield.style.height = rect.height + 'px';
        shield.style.zIndex = 9999;
        shield.style.background = 'transparent';
		shield.style.cursor = 'grabbing';
        //shield.style.border = '2px solid red';  debug
        document.body.appendChild(shield);
      }

      document.removeEventListener('pointermove', pointerMoveHandler);
    }
  }

  document.addEventListener('pointermove', pointerMoveHandler);

  document.addEventListener('pointerup', () => {
    if (shield) {
      shield.remove();
      shield = null;
    }
    document.removeEventListener('pointermove', pointerMoveHandler);
  }, { once: true });
});
