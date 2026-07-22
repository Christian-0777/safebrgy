/**
 * Admin Landing Page JavaScript
 * Handles burger menu toggle for the admin landing page navigation.
 * On mobile, clicking the burger button toggles the admin nav dropdown.
 */

(function () {
  'use strict';

  document.addEventListener('DOMContentLoaded', () => {
    const navToggle = document.getElementById('navToggle');
    const adminNav = document.getElementById('adminNav');

    if (!navToggle || !adminNav) return;

    let isOpen = false;

    function openMenu() {
      adminNav.classList.add('active');
      navToggle.classList.add('active');
      navToggle.setAttribute('aria-expanded', 'true');
      isOpen = true;
    }

    function closeMenu() {
      adminNav.classList.remove('active');
      navToggle.classList.remove('active');
      navToggle.setAttribute('aria-expanded', 'false');
      isOpen = false;
    }

    function toggleMenu() {
      if (isOpen) {
        closeMenu();
      } else {
        openMenu();
      }
    }

    // Burger button click
    navToggle.addEventListener('click', (e) => {
      e.stopPropagation();
      toggleMenu();
    });

    // Close menu when a nav link is clicked
    adminNav.querySelectorAll('a').forEach((link) => {
      link.addEventListener('click', () => closeMenu());
    });

    // Close menu when clicking outside
    document.addEventListener('click', (e) => {
      if (isOpen && !adminNav.contains(e.target) && !navToggle.contains(e.target)) {
        closeMenu();
      }
    });

    // Close menu on Escape key
    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape' && isOpen) {
        closeMenu();
      }
    });

    // Reset menu state on resize to desktop
    window.addEventListener('resize', () => {
      if (window.innerWidth > 768 && isOpen) {
        closeMenu();
      }
    });
  });
})();
