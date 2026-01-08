
let sharedState = {
    spent_time_counter: 0,
}

export function getSpentTimeCounter() {
    return parseInt(sharedState.spent_time_counter || '0')
}

export function checkTimer(){
    const counter = parseInt(localStorage.getItem("delivery_click_counter") || '0')

    if (counter > 0) {
        startTimer(counter)
        return true
    }
    return false
}

export function startTimer(time) {
    sharedState.spent_time_counter = parseInt(time) != null ? Math.min(parseInt(time), 10) : 10;

    let counterId = setInterval(() => {
            if (sharedState.spent_time_counter > 0)
                sharedState.spent_time_counter--
            else {
                clearInterval(counterId)
                sharedState.spent_time_counter = null
            }
            localStorage.setItem("delivery_click_counter", sharedState.spent_time_counter)

            window.dispatchEvent(new CustomEvent('trigger-spent-timer', {'detail': sharedState.spent_time_counter}));
        }, 1000
    )

}
