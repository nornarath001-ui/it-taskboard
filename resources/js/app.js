import Swal from 'sweetalert2';

// SweetAlert with the project's colours.
const alertBox = Swal.mixin({
    confirmButtonColor: '#0f8b7d',
    cancelButtonColor: '#5c6e75',
});

window.Swal = alertBox;

// Small message in the top-right corner:  toast('success', 'Saved')
window.toast = (icon, title, text = '', timer = 4000) =>
    alertBox.fire({
        toast: true,
        position: 'top-end',
        icon,
        title,
        text,
        showConfirmButton: false,
        timer,
        timerProgressBar: true,
    });

// Any form with data-confirm="..." asks first (used for Delete and Remove).
document.addEventListener('submit', (event) => {
    const form = event.target;
    const message = form instanceof HTMLFormElement ? form.dataset.confirm : null;

    if (!message) return;

    event.preventDefault();

    alertBox
        .fire({
            title: 'Are you sure?',
            text: message,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, continue',
            confirmButtonColor: '#d9480f',
            cancelButtonText: 'Cancel',
            focusCancel: true,
        })
        .then((result) => {
            if (result.isConfirmed) form.submit();
        });
});
