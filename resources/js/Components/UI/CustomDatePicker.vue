<template>
    <div class="custom-datepicker">
        <div class="date-input-wrap" @click="openPicker">
            <div class="form-floating date-floating">
                <input
                    type="text"
                    class="form-control date-input"
                    :id="inputId"
                    :value="displayValue"
                    :placeholder="placeholder"
                    readonly
                    :class="{ 'is-invalid': invalid }"
                    :aria-invalid="invalid ? 'true' : 'false'"
                >
                <label :for="inputId">{{ label }}</label>
            </div>
            <span class="date-input-icon" aria-hidden="true">
                <i class="fa-solid fa-calendar-days"></i>
            </span>
        </div>

        <div class="modal fade datepicker-modal" :id="modalId" tabindex="-1" ref="modalRef">
            <div class="modal-dialog modal-dialog-centered modal-sm">
                <div class="modal-content datepicker-content">
                    <div class="calendar-heading">
                        <div class="calendar-heading-copy">
                            <span class="calendar-eyebrow">ВЫБОР ДАТЫ</span>
                            <h5 class="modal-title">Календарь</h5>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
                    </div>

                    <div class="calendar-navigation">
                        <button type="button" class="month-arrow" @click="prevMonth" aria-label="Предыдущий месяц">
                            <i class="fa-solid fa-chevron-left"></i>
                        </button>
                        <div class="month-year-selectors">
                            <select class="form-select month-select" v-model="viewMonth" aria-label="Месяц">
                                <option v-for="(m, i) in months" :key="i" :value="i">{{ m }}</option>
                            </select>
                            <input
                                type="number"
                                class="form-control year-input"
                                v-model.number="viewYear"
                                min="1900"
                                max="2100"
                                aria-label="Год"
                            >
                        </div>
                        <button type="button" class="month-arrow" @click="nextMonth" aria-label="Следующий месяц">
                            <i class="fa-solid fa-chevron-right"></i>
                        </button>
                    </div>

                    <div class="modal-body calendar-body">
                        <div class="weekdays">
                            <div v-for="day in weekDays" :key="day" class="weekday">{{ day }}</div>
                        </div>
                        <div class="days-grid">
                            <button
                                v-for="(day, idx) in calendarDays"
                                :key="idx"
                                type="button"
                                class="day-cell"
                                :class="{
                                    'other-month': !day.currentMonth,
                                    'today': day.isToday,
                                    'selected': day.isSelected,
                                    'disabled': day.isDisabled,
                                    'weekend': day.isWeekend && day.currentMonth
                                }"
                                :disabled="day.isDisabled"
                                :aria-pressed="day.isSelected ? 'true' : 'false'"
                                @click="selectDay(day)"
                            >{{ day.date }}</button>
                        </div>
                    </div>

                    <div class="calendar-footer">
                        <button
                            v-if="required === false"
                            type="button"
                            class="btn calendar-clear"
                            @click="clearDate"
                        >Очистить</button>
                        <div class="footer-spacer"></div>
                        <button type="button" class="btn calendar-today" @click="selectToday">
                            <i class="fa-regular fa-calendar-check me-1"></i> Сегодня
                        </button>
                        <button type="button" class="btn calendar-done" @click="closePicker">Готово</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'CustomDatePicker',
    props: {
        modelValue: {
            type: String,
            default: ''
        },
        label: {
            type: String,
            default: 'Дата'
        },
        placeholder: {
            type: String,
            default: 'Выберите дату'
        },
        inputId: {
            type: String,
            required: true
        },
        required: {
            type: Boolean,
            default: false
        },
        invalid: {
            type: Boolean,
            default: false
        },
        minDate: {
            type: String,
            default: null
        },
        maxDate: {
            type: String,
            default: null
        }
    },
    emits: ['update:modelValue'],
    data() {
        const now = new Date()
        return {
            viewMonth: now.getMonth(),
            viewYear: now.getFullYear(),
            modalInstance: null,
            months: [
                'Январь', 'Февраль', 'Март', 'Апрель',
                'Май', 'Июнь', 'Июль', 'Август',
                'Сентябрь', 'Октябрь', 'Ноябрь', 'Декабрь'
            ],
            weekDays: ['Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб', 'Вс']
        }
    },
    computed: {
        modalId() {
            return `datepicker-modal-${this.inputId}`
        },
        displayValue() {
            if (!this.modelValue) return ''
            return this.formatDisplayDate(this.modelValue)
        },
        selectedDate() {
            if (!this.modelValue) return null
            return this.parseDate(this.modelValue)
        },
        calendarDays() {
            const days = []
            const firstDay = new Date(this.viewYear, this.viewMonth, 1)
            const lastDay = new Date(this.viewYear, this.viewMonth + 1, 0)

            // День недели первого дня месяца (0=Вс, 1=Пн, ...)
            // Нам нужно начать с понедельника
            let startWeekday = firstDay.getDay()
            if (startWeekday === 0) startWeekday = 7

            // Дни предыдущего месяца
            const prevMonthLastDay = new Date(this.viewYear, this.viewMonth, 0).getDate()
            for (let i = startWeekday - 1; i > 0; i--) {
                const date = prevMonthLastDay - i + 1
                const month = this.viewMonth - 1
                const year = month < 0 ? this.viewYear - 1 : this.viewYear
                const actualMonth = month < 0 ? 11 : month
                days.push({
                    date,
                    fullDate: this.formatISODate(year, actualMonth, date),
                    currentMonth: false,
                    isToday: false,
                    isSelected: false,
                    isWeekend: this.isWeekendDay(year, actualMonth, date),
                    isDisabled: this.isDateDisabled(year, actualMonth, date)
                })
            }

            // Дни текущего месяца
            const today = new Date()
            for (let d = 1; d <= lastDay.getDate(); d++) {
                const fullDate = this.formatISODate(this.viewYear, this.viewMonth, d)
                days.push({
                    date: d,
                    fullDate,
                    currentMonth: true,
                    isToday: this.isSameDay(today, this.viewYear, this.viewMonth, d),
                    isSelected: this.selectedDate ? this.isSameDay(this.selectedDate, this.viewYear, this.viewMonth, d) : false,
                    isWeekend: this.isWeekendDay(this.viewYear, this.viewMonth, d),
                    isDisabled: this.isDateDisabled(this.viewYear, this.viewMonth, d)
                })
            }

            // Дни следующего месяца (до 42 клеток — 6 недель)
            const remaining = 42 - days.length
            for (let d = 1; d <= remaining; d++) {
                const nextMonth = this.viewMonth + 1
                const nextYear = nextMonth > 11 ? this.viewYear + 1 : this.viewYear
                const actualMonth = nextMonth > 11 ? 0 : nextMonth
                days.push({
                    date: d,
                    fullDate: this.formatISODate(nextYear, actualMonth, d),
                    currentMonth: false,
                    isToday: false,
                    isSelected: false,
                    isWeekend: this.isWeekendDay(nextYear, actualMonth, d),
                    isDisabled: this.isDateDisabled(nextYear, actualMonth, d)
                })
            }

            return days
        }
    },
    watch: {
        modelValue(newVal) {
            if (newVal) {
                const d = this.parseDate(newVal)
                if (d) {
                    this.viewMonth = d.getMonth()
                    this.viewYear = d.getFullYear()
                }
            }
        }
    },
    methods: {
        openPicker() {
            const modalEl = this.$refs.modalRef
            if (!this.modalInstance) {
                this.modalInstance = new bootstrap.Modal(modalEl)
            }
            this.modalInstance.show()
        },
        closePicker() {
            if (this.modalInstance) {
                this.modalInstance.hide()
            }
        },
        prevMonth() {
            if (this.viewMonth === 0) {
                this.viewMonth = 11
                this.viewYear--
            } else {
                this.viewMonth--
            }
        },
        nextMonth() {
            if (this.viewMonth === 11) {
                this.viewMonth = 0
                this.viewYear++
            } else {
                this.viewMonth++
            }
        },
        selectDay(day) {
            if (day.isDisabled) return
            this.$emit('update:modelValue', day.fullDate)
        },
        selectToday() {
            const today = new Date()
            const iso = this.formatISODate(
                today.getFullYear(),
                today.getMonth(),
                today.getDate()
            )
            this.$emit('update:modelValue', iso)
            this.viewMonth = today.getMonth()
            this.viewYear = today.getFullYear()
        },
        clearDate() {
            this.$emit('update:modelValue', '')
            this.closePicker()
        },
        formatISODate(year, month, day) {
            const m = String(month + 1).padStart(2, '0')
            const d = String(day).padStart(2, '0')
            return `${year}-${m}-${d}`
        },
        formatDisplayDate(isoDate) {
            if (!isoDate) return ''
            const [y, m, d] = isoDate.split('-')
            return `${d}.${m}.${y}`
        },
        parseDate(isoDate) {
            if (!isoDate) return null
            const [y, m, d] = isoDate.split('-').map(Number)
            return new Date(y, m - 1, d)
        },
        isSameDay(date, year, month, day) {
            return (
                date.getFullYear() === year &&
                date.getMonth() === month &&
                date.getDate() === day
            )
        },
        isWeekendDay(year, month, day) {
            const d = new Date(year, month, day)
            const wd = d.getDay()
            return wd === 0 || wd === 6
        },
        isDateDisabled(year, month, day) {
            const iso = this.formatISODate(year, month, day)
            if (this.minDate && iso < this.minDate) return true
            if (this.maxDate && iso > this.maxDate) return true
            return false
        }
    },
    mounted() {
        if (this.modelValue) {
            const d = this.parseDate(this.modelValue)
            if (d) {
                this.viewMonth = d.getMonth()
                this.viewYear = d.getFullYear()
            }
        }
    }
}
</script>

<style scoped>
.custom-datepicker {
    --calendar-blue: #2457d6;
    --calendar-blue-dark: #173b99;
    --calendar-red: #e34b5f;
    --calendar-ink: #18243b;
    --calendar-muted: #7d899e;
    width: 100%;
}

.date-input-wrap {
    position: relative;
    cursor: pointer;
}

.date-floating .date-input {
    min-height: 58px;
    padding-right: 3.25rem;
    border: 1px solid #dce4f0;
    border-radius: 14px;
    background: #fff;
    color: var(--calendar-ink);
    font-weight: 600;
    box-shadow: 0 3px 12px rgba(24, 48, 91, 0.035);
    cursor: pointer;
    transition: border-color .2s ease, box-shadow .2s ease;
}

.date-floating .date-input:focus {
    border-color: var(--calendar-blue);
    box-shadow: 0 0 0 4px rgba(36, 87, 214, .1);
}

.date-floating label {
    color: var(--calendar-muted);
}

.date-input-icon {
    position: absolute;
    top: 50%;
    right: 1rem;
    transform: translateY(-50%);
    display: grid;
    place-items: center;
    width: 36px;
    height: 36px;
    border-radius: 11px;
    background: #edf3ff;
    color: var(--calendar-blue);
    pointer-events: none;
}

.datepicker-modal .modal-dialog {
    max-width: 370px;
    width: calc(100% - 1.5rem);
}

.datepicker-content {
    overflow: hidden;
    border: 1px solid rgba(214, 224, 240, .9);
    border-radius: 22px;
    background: #fff;
    box-shadow: 0 24px 70px rgba(19, 37, 75, .22);
}

.calendar-heading {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1.15rem 1.25rem .9rem;
    background: linear-gradient(135deg, #f2f6ff, #fff 75%);
    border-bottom: 1px solid #edf1f8;
}

.calendar-eyebrow {
    display: block;
    margin-bottom: .25rem;
    color: var(--calendar-blue);
    font-size: .65rem;
    font-weight: 800;
    letter-spacing: .12em;
}

.calendar-heading .modal-title {
    color: var(--calendar-ink);
    font-size: 1.15rem;
    font-weight: 800;
}

.calendar-navigation {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: .55rem;
    padding: 1rem 1rem .5rem;
}

.month-arrow {
    display: grid;
    place-items: center;
    flex: 0 0 34px;
    width: 34px;
    height: 34px;
    border: 1px solid #e1e8f4;
    border-radius: 11px;
    background: #fff;
    color: var(--calendar-blue);
    transition: background .2s ease, color .2s ease, border-color .2s ease;
}

.month-arrow:hover {
    border-color: var(--calendar-blue);
    background: var(--calendar-blue);
    color: #fff;
}

.month-year-selectors {
    display: flex;
    align-items: center;
    justify-content: center;
    flex: 1;
    gap: .45rem;
    min-width: 0;
}

.month-select,
.year-input {
    min-height: 36px;
    border-color: #e1e8f4;
    border-radius: 10px;
    color: var(--calendar-ink);
    font-size: .82rem;
    font-weight: 700;
}

.month-select {
    width: 100%;
    max-width: 150px;
}

.year-input {
    width: 78px;
    padding-inline: .4rem;
    text-align: center;
}

.calendar-body {
    padding: .5rem 1rem 1rem;
}

.weekdays,
.days-grid {
    display: grid;
    grid-template-columns: repeat(7, minmax(0, 1fr));
    gap: 4px;
}

.weekdays {
    padding-bottom: .55rem;
    margin-bottom: .45rem;
    border-bottom: 1px solid #edf1f7;
}

.weekday {
    text-align: center;
    color: var(--calendar-muted);
    font-size: .7rem;
    font-weight: 800;
}

.weekday:nth-child(6),
.weekday:nth-child(7) {
    color: var(--calendar-red);
}

.day-cell {
    display: grid;
    place-items: center;
    aspect-ratio: 1;
    min-width: 0;
    padding: 0;
    border: 1px solid transparent;
    border-radius: 11px;
    background: transparent;
    color: var(--calendar-ink);
    font-size: .84rem;
    font-weight: 600;
    transition: background .15s ease, color .15s ease, transform .15s ease;
}

.day-cell:hover:not(:disabled):not(.selected) {
    background: #edf3ff;
    color: var(--calendar-blue);
    transform: translateY(-1px);
}

.day-cell.other-month {
    color: #b6c0d0;
    font-weight: 500;
}

.day-cell.weekend:not(.selected) {
    color: var(--calendar-red);
}

.day-cell.today:not(.selected) {
    border-color: var(--calendar-blue);
    color: var(--calendar-blue);
    font-weight: 800;
}

.day-cell.selected {
    background: var(--calendar-blue);
    color: #fff;
    box-shadow: 0 4px 10px rgba(36, 87, 214, .24);
    font-weight: 800;
}

.day-cell:disabled,
.day-cell.disabled {
    color: #d5dce7;
    cursor: not-allowed;
    pointer-events: none;
}

.calendar-footer {
    display: flex;
    align-items: center;
    gap: .45rem;
    padding: .85rem 1rem 1rem;
    border-top: 1px solid #edf1f8;
    background: #fbfcff;
}

.footer-spacer {
    flex: 1;
}

.calendar-footer .btn {
    min-height: 36px;
    padding: .45rem .7rem;
    border-radius: 10px;
    font-size: .78rem;
    font-weight: 700;
    white-space: nowrap;
}

.calendar-clear {
    border: 1px solid #f2cbd1;
    background: #fff;
    color: var(--calendar-red);
}

.calendar-clear:hover {
    background: #fff0f2;
    color: #bd3045;
}

.calendar-today {
    border: 1px solid #dce6fa;
    background: #fff;
    color: var(--calendar-blue);
}

.calendar-today:hover {
    background: #edf3ff;
}

.calendar-done {
    border: 1px solid var(--calendar-blue);
    background: var(--calendar-blue);
    color: #fff;
}

.calendar-done:hover {
    border-color: var(--calendar-blue-dark);
    background: var(--calendar-blue-dark);
    color: #fff;
}

@media (max-width: 380px) {
    .calendar-heading {
        padding-inline: 1rem;
    }

    .calendar-navigation {
        padding-inline: .7rem;
    }

    .calendar-body {
        padding-inline: .7rem;
    }

    .calendar-footer {
        gap: .3rem;
        padding-inline: .7rem;
    }

    .calendar-footer .btn {
        padding-inline: .5rem;
        font-size: .73rem;
    }

    .weekdays,
    .days-grid {
        gap: 2px;
    }

    .day-cell {
        border-radius: 8px;
    }
}
</style>
