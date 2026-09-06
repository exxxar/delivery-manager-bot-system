export default {
    mounted(el, binding) {
        const delay = binding.value || 1500 // по умолчанию 1.5 секунды
        let isDisabled = false

        el.addEventListener('click', (event) => {
            if (isDisabled) {
                event.preventDefault()
                event.stopPropagation()
                return
            }

            isDisabled = true
            el.classList.add('btn-submitting')

            // Блокируем визуально
            const originalHTML = el.innerHTML
            el.disabled = true

            // Разблокируем через заданное время
            setTimeout(() => {
                isDisabled = false
                el.disabled = false
                el.classList.remove('btn-submitting')
                el.innerHTML = originalHTML
            }, delay)
        }, true) // true = capture phase, чтобы перехватить до родного обработчика
    }
}
