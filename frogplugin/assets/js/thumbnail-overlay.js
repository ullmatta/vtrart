


document.addEventListener("DOMContentLoaded", () => {
  const minWidth = 1000;
  let swiperEls = null;
  let thumbnailCache = null;
  let lastToggleTime = 0;
  const debounceDelay = 300;
  let isPreloadComplete = false;
  let overlay = null;
  let button = null;
  let resizeTimeout = null;

  // Function to check if feature should initialize
  function shouldInitialize() {
    return window.innerWidth >= minWidth;
  }

  // Function to check if Simply Gallery lightbox is active
  function isSimplyGalleryLightboxActive() {
    return !!document.querySelector(".pgc-rev-lb-b-view");
  }

  // Function to close Simply Gallery lightbox
  function closeSimplyGalleryLightbox() {
    const lightbox = document.querySelector(".pgc-rev-lb-b-view");
    if (!lightbox) return false;

    // Try clicking the close button
    const closeButton = lightbox.querySelector(".pgc-rev-lb-b-close") || lightbox.querySelector("[class*='close']");
    if (closeButton) {
      closeButton.click();
      console.log('Closed Simply Gallery lightbox via close button');
      return true;
    }

    // Fallback: Simulate Escape key
    const escEvent = new KeyboardEvent("keydown", {
      key: "Escape",
      keyCode: 27,
      bubbles: true,
      cancelable: true,
    });
    document.dispatchEvent(escEvent);
    console.log('Simulated Escape key to close Simply Gallery lightbox');
    return true;
  }

  // Function to get attachment ID from image
  function getAttachmentId(img) {
    if (!img) return null;
    if (img.dataset.attachmentId) return img.dataset.attachmentId;
    const classList = img.className.split(' ');
    const wpImageClass = classList.find(cls => cls.startsWith('wp-image-'));
    return wpImageClass ? wpImageClass.replace('wp-image-', '') : null;
  }

  // Function to get thumbnail from srcset (fallback)
  function getThumbnailUrl(img) {
    if (!img) return null;
    const src = img.dataset.src || img.src;
    const srcset = img.getAttribute('srcset');
    if (srcset) {
      const sources = srcset.split(',').map(s => s.trim().split(' ')[0]);
      const thumb = sources.find(url => url.match(/-500x\d+\.jpg$/));
      if (thumb) return thumb;
    }
    return src.replace(/-scaled\.jpg$|-2560x\d+\.jpg$/, '-500x500.jpg');
  }

  // Fetch thumbnail URLs via REST API
  async function fetchThumbnailUrls() {
    const attachmentIds = [];
    swiperEls.forEach(el => {
      const swiper = el.swiper;
      if (swiper && swiper.slides && swiper.slides.length > 0) {
        const img = swiper.slides[0].querySelector("img");
        const id = getAttachmentId(img);
        if (id) attachmentIds.push(id);
      }
    });

    if (attachmentIds.length && window.swiperThumbs?.rest_url) {
      try {
        const response = await fetch(window.swiperThumbs.rest_url, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-WP-Nonce': window.swiperThumbs.nonce,
          },
          body: JSON.stringify({
            attachment_ids: attachmentIds,
            thumbnail_size: window.swiperThumbs.thumbnail_size,
          }),
        });
        if (response.ok) {
          return await response.json();
        } else {
          console.error('API error:', response.status, response.statusText);
        }
      } catch (error) {
        console.error('Error fetching thumbnails:', error.message);
      }
    }
    return swiperEls.map(el => {
      const swiper = el.swiper;
      if (swiper && swiper.slides && swiper.slides.length > 0) {
        const img = swiper.slides[0].querySelector("img");
        return getThumbnailUrl(img);
      }
      return null;
    }).filter(url => url);
  }

  // Preload thumbnails and populate grid
  async function preloadThumbnails() {
    if (thumbnailCache) return thumbnailCache;
    thumbnailCache = await fetchThumbnailUrls();
    
    const grid = document.createElement("div");
    grid.className = "GR-thumbnail-grid";
    overlay.appendChild(grid);

    const preloadPromises = thumbnailCache.map((url, index) => {
      return new Promise((resolve) => {
        const preloadImg = new Image();
        preloadImg.src = url;
        preloadImg.onload = () => resolve();
        preloadImg.onerror = () => {
          const img = swiperEls[index]?.swiper?.slides[0]?.querySelector("img");
          if (img && img.src) {
            thumbnailCache[index] = img.src;
            preloadImg.src = img.src;
          }
          resolve();
        };
      });
    });

    await Promise.race([
      Promise.all(preloadPromises),
      new Promise(resolve => setTimeout(resolve, 5000)),
    ]);
    isPreloadComplete = true;

    thumbnailCache.forEach((url, index) => {
      const thumb = document.createElement("img");
      thumb.src = url;
      thumb.loading = "eager";
      thumb.addEventListener("click", (e) => {
        e.stopPropagation();
        const targetSwiper = swiperEls[index];
        if (targetSwiper) {
          targetSwiper.scrollIntoView({ behavior: "instant", block: "center" });
        }
        overlay.classList.add("GR-closing");
        button.classList.remove("GR-active");
        setTimeout(() => {
          overlay.classList.remove("GR-visible", "GR-closing");
        }, 600);
      });
      thumb.onerror = () => {
        const img = swiperEls[index]?.swiper?.slides[0]?.querySelector("img");
        if (img && img.src) {
          thumbnailCache[index] = img.src;
          thumb.src = img.src;
        }
      };
      grid.appendChild(thumb);
    });

    console.log('Thumbnail cache loaded, length:', thumbnailCache.length, thumbnailCache);
    return thumbnailCache;
  }

  // Toggle overlay function
  function toggleOverlay() {
    if (!shouldInitialize()) return;
    if (overlay.classList.contains("GR-visible")) {
      overlay.classList.add("GR-closing");
      button.classList.remove("GR-active");
      setTimeout(() => {
        overlay.classList.remove("GR-visible", "GR-closing");
      }, 600);
    } else {
      overlay.classList.remove("GR-closing");
      overlay.classList.add("GR-visible");
      button.classList.add("GR-active");
    }
  }

  // Initialize feature
  function initializeFeature() {
    if (!shouldInitialize()) {
      console.log('Thumbnail overlay skipped: Window width < 1000px');
      return;
    }

    swiperEls = Array.from(document.querySelectorAll(".wp-block-makeiteasy-slider.swiper"));
    if (!swiperEls.length) {
      console.log('Thumbnail overlay skipped: No swipers found');
      return;
    }

    // Create overlay
    overlay = document.createElement("div");
    overlay.className = "GR-thumbnail-overlay";
    document.body.appendChild(overlay);

    // Preload thumbnails
    preloadThumbnails();

    // Create button
    button = document.createElement("button");
    button.className = "GR-thumbnail-button";
    button.addEventListener("click", () => {
      const now = Date.now();
      if (now - lastToggleTime < debounceDelay) return;
      lastToggleTime = now;
      if (!isPreloadComplete) {
        console.log('Preloading not complete, waiting...');
        return;
      }
      toggleOverlay();
    });
    document.body.appendChild(button);

    // Keyboard event handler
    document.addEventListener("keydown", (e) => {
      // Check if the event target is an input or textarea
      if (e.target.tagName === "INPUT" || e.target.tagName === "TEXTAREA") {
        return;
      }

      // Spacebar handling
      if (e.key === " " || e.keyCode === 32) {
        e.preventDefault();
        const now = Date.now();
        if (now - lastToggleTime < debounceDelay) return;
        lastToggleTime = now;
        if (!isPreloadComplete) {
          console.log('Preloading not complete, waiting...');
          return;
        }
        if (isSimplyGalleryLightboxActive()) {
          closeSimplyGalleryLightbox();
        }
        toggleOverlay();
      }

      // WASD to arrow key mapping (lowercase only)
      const keyMap = {
        'w': 'ArrowUp',
        'a': 'ArrowLeft',
        's': 'ArrowDown',
        'd': 'ArrowRight'
      };

      if (keyMap[e.key]) {
        e.preventDefault();
        const arrowKey = keyMap[e.key];
        const keyCodeMap = {
          'ArrowUp': 38,
          'ArrowDown': 40,
          'ArrowLeft': 37,
          'ArrowRight': 39
        };

        const arrowEvent = new KeyboardEvent("keydown", {
          key: arrowKey,
          code: arrowKey,
          keyCode: keyCodeMap[arrowKey],
          which: keyCodeMap[arrowKey],
          altKey: false,
          ctrlKey: false,
          metaKey: false,
          shiftKey: false,
          bubbles: true,
          cancelable: true,
          repeat: false
        });

        document.dispatchEvent(arrowEvent);

        // Dispatch keyup after a short delay to simulate single press
        setTimeout(() => {
          const keyupEvent = new KeyboardEvent("keyup", {
            key: arrowKey,
            code: arrowKey,
            keyCode: keyCodeMap[arrowKey],
            which: keyCodeMap[arrowKey],
            altKey: false,
            ctrlKey: false,
            metaKey: false,
            shiftKey: false,
            bubbles: true,
            cancelable: true,
          });
          document.dispatchEvent(keyupEvent);
          console.log(`Dispatched keyup for ${arrowKey}`);
        }, 50);

        console.log(`Mapped ${e.key} to ${arrowKey}`);
      }
    });

    // Close overlay on click outside grid or on grid (but not thumbnails)
    overlay.addEventListener("click", (e) => {
      if (e.target === overlay || e.target.classList.contains("GR-thumbnail-grid")) {
        overlay.classList.add("GR-closing");
        button.classList.remove("GR-active");
        setTimeout(() => {
          overlay.classList.remove("GR-visible", "GR-closing");
        }, 600);
      }
    });
  }

  // Cleanup feature
  function cleanupFeature() {
    if (overlay && overlay.parentNode) {
      overlay.remove();
      overlay = null;
    }
    if (button && button.parentNode) {
      button.remove();
      button = null;
    }
    thumbnailCache = null;
    isPreloadComplete = false;
  }

  // Handle window resize
  function handleResize() {
    clearTimeout(resizeTimeout);
    resizeTimeout = setTimeout(() => {
      if (shouldInitialize() && !overlay) {
        initializeFeature();
      } else if (!shouldInitialize() && overlay) {
        cleanupFeature();
      }
    }, 200);
  }

  // Initial check
  initializeFeature();

  // Add resize listener
  window.addEventListener("resize", handleResize);
});