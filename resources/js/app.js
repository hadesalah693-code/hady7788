// Close header menus when clicking outside them or pressing Escape.
const closeMenus = (event) => {
    document.querySelectorAll('details[data-menu][open]').forEach((menu) => {
        if (!menu.contains(event.target)) {
            menu.open = false;
        }
    });
};

document.addEventListener('click', closeMenus);

document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') {
        document.querySelectorAll('details[data-menu][open]').forEach((menu) => {
            menu.open = false;
        });
    }
});

// Dismiss flash messages.
document.addEventListener('click', (event) => {
    const dismiss = event.target.closest('[data-dismiss]');

    if (dismiss) {
        dismiss.closest('[data-flash]')?.remove();
    }
});
