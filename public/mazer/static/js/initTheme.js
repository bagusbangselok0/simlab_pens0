const body = document.body;
const theme = localStorage.getItem('theme') || 'light';

document.documentElement.setAttribute('data-bs-theme', theme);
if (document.body) {
  document.body.classList.remove('dark', 'light', 'theme-dark', 'theme-light');
  document.body.classList.add(theme);
}
