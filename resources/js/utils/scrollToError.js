export function scrollToError(root = document) {
    const target = root.querySelector(
        '[data-scroll-error="true"]'
    )

    if (!target) return

    target.scrollIntoView({
        behavior: 'smooth',
        block: 'center',
        inline: 'nearest',
    })

    const input = target.matches('input, select, textarea')
        ? target
        : target.querySelector('input, select, textarea')

    if (input) {
        setTimeout(() => input.focus({ preventScroll: true }), 400)
    }
}