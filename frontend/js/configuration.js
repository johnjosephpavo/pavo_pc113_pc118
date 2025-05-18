
  function sidebarColor(element) {
    const color = element.getAttribute('data-color');
    const sidebar = document.querySelector('.sidenav');
    const colorClasses = ['bg-primary', 'bg-gradient-dark', 'bg-gradient-info', 'bg-gradient-success', 'bg-gradient-warning', 'bg-gradient-danger'];
    sidebar.classList.remove(...colorClasses);
    sidebar.classList.add(`bg-${color}`);
    localStorage.setItem('sidebarColor', color);
    document.querySelectorAll('.badge.filter').forEach(el => el.classList.remove('active'));
    element.classList.add('active');
  }


  function navbarFixed(checkbox) {
    const navbar = document.querySelector('nav.navbar');

    if (checkbox.checked) {
      navbar.classList.add('position-sticky', 'top-0', 'z-index-sticky');
      localStorage.setItem('navbarFixed', 'true');
    } else {
      navbar.classList.remove('position-sticky', 'top-0', 'z-index-sticky');
      localStorage.setItem('navbarFixed', 'false');
    }
  }


  function sidebarType(button) {
    const sidenav = document.querySelector('.sidenav');
    const type = button.getAttribute('data-class');

    sidenav.classList.remove('bg-transparent', 'bg-white');
    sidenav.classList.add(type);
    localStorage.setItem('sidebarType', type);
    document.querySelectorAll('[onclick^="sidebarType"]').forEach(btn => btn.classList.remove('active'));
    button.classList.add('active');
  } 

  function resetTheme() {
    localStorage.removeItem('sidebarColor');
    localStorage.removeItem('sidebarType');
    localStorage.removeItem('navbarFixed');

    const sidebar = document.querySelector('.sidenav');
    const navbar = document.querySelector('nav.navbar');

    if (sidebar) {
      sidebar.classList.remove(
        'bg-primary',
        'bg-gradient-dark',
        'bg-gradient-info',
        'bg-gradient-success',
        'bg-gradient-warning',
        'bg-gradient-danger',
        'bg-white',
        'bg-transparent'
      );
    }
    if (navbar) {
      navbar.classList.remove('position-sticky', 'top-0', 'z-index-sticky');
    }
    window.location.reload();
  } 


  document.addEventListener('DOMContentLoaded', () => {
      const sidebar = document.querySelector('.sidenav');
      const navbar = document.querySelector('nav.navbar');

      const savedSidebarColor = localStorage.getItem('sidebarColor');
      const savedSidebarType = localStorage.getItem('sidebarType');
      const savedNavbarFixed = localStorage.getItem('navbarFixed');

      if (savedSidebarColor && sidebar) {
        sidebar.classList.add(`bg-${savedSidebarColor}`);
        const badge = document.querySelector(`.badge.filter[data-color="${savedSidebarColor}"]`);
        if (badge) badge.classList.add('active');
      }

      if (savedSidebarType && sidebar) {
        sidebar.classList.add(savedSidebarType);
        const button = document.querySelector(`[data-class="${savedSidebarType}"]`);
        if (button) button.classList.add('active');
      }

      if (savedNavbarFixed === 'true' && navbar) {
        navbar.classList.add('position-sticky', 'top-0', 'z-index-sticky');
        const checkbox = document.getElementById('navbarFixed');
        if (checkbox) checkbox.checked = true;
      }
  });




