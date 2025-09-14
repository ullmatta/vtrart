document.addEventListener("DOMContentLoaded", () => {
  // Remove "skip to content" links
  document.querySelectorAll('a.skip-link, a.screen-reader-text, .screen-reader-text').forEach(el => {
    el.remove();
  });

  // Remove ARIA landmarks that are only for accessibility
  document.querySelectorAll('[role="main"], [role="banner"], [role="complementary"], [role="navigation"]').forEach(el => {
    // Optionally you can just remove the role, or remove the element entirely
    el.removeAttribute('role');
  });

  // Remove visually hidden elements
  document.querySelectorAll('.assistive-text, .sr-only').forEach(el => el.remove());
});
