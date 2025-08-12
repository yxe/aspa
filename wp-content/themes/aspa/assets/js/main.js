document.addEventListener('DOMContentLoaded', () => {
  // Header scroll
  const header = document.querySelector('.main-header');
  
  if (!header) {
    return;
  }

  let isTicking = false;

  const updateHeaderOnScroll = () => {
    if (window.scrollY > 50) {
      header.classList.add('scrolled');
    }
    else {
      header.classList.remove('scrolled');
    }
    
    isTicking = false;
  };

  window.addEventListener('scroll', () => {
    if (!isTicking) {
      window.requestAnimationFrame(updateHeaderOnScroll);
      isTicking = true;
    }
  });

 // Mobile navigation
  const menuToggle = document.querySelector('.mobile-menu-toggle');
  const navContainer = document.querySelector('.main-navigation');

  if (menuToggle && navContainer) {
    menuToggle.addEventListener('click', (event) => {
      const button = event.currentTarget;
      const isExpanded = button.getAttribute('aria-expanded') === 'true';

      button.setAttribute('aria-expanded', !isExpanded);
      navContainer.classList.toggle('is-open');
      button.classList.toggle('is-open');
    });
  }

  // Submenu toggle
  const menuItemsWithChildren = document.querySelectorAll('.main-navigation .menu-item-has-children');

  menuItemsWithChildren.forEach((menuItem) => {
    const link = menuItem.querySelector('a');
    
    const toggleButton = document.createElement('button');
    toggleButton.classList.add('submenu-toggle');
    toggleButton.setAttribute('aria-expanded', 'false');

    // Use the SVG passed from PHP and set the accessible label
    if (typeof aspa_globals !== 'undefined' && aspa_globals.chevronIcon) {
      toggleButton.innerHTML = aspa_globals.chevronIcon;
    }

    if (link) {
      toggleButton.setAttribute('aria-label', `Show submenu for ${link.textContent}`);
    }

    if (link) {
      link.after(toggleButton);
    }

    toggleButton.addEventListener('click', (event) => {
      event.preventDefault();
      const button = event.currentTarget;
      const parentLi = button.closest('.menu-item-has-children');
      const isExpanded = button.getAttribute('aria-expanded') === 'true';
      
      button.setAttribute('aria-expanded', !isExpanded);
      if (parentLi) {
        parentLi.classList.toggle('is-open');
      }
    });
  }); 
});
