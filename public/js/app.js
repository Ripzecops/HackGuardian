document.addEventListener('DOMContentLoaded', () => {
    const animated = document.querySelectorAll('.card, .panel, .hero-panel');
    animated.forEach((item, index) => {
        item.classList.add('fade-in');
        item.style.animationDelay = `${Math.min(index * 0.05, 0.6)}s`;
    });

    document.querySelectorAll('form[data-confirm]').forEach((form) => {
        form.addEventListener('submit', (event) => {
            const message = form.getAttribute('data-confirm');
            if (message && !window.confirm(message)) {
                event.preventDefault();
            }
        });
    });
});
