function showToast() {
  const toast = document.getElementById('toast-success');
  if (toast) {
    toast.classList.add('show');

    setTimeout(() => {
      closeToast();
    }, 5000);
  }
}

function closeToast() {
  const toast = document.getElementById('toast-success');
  if (toast) {
    toast.classList.remove('show');
  }
}