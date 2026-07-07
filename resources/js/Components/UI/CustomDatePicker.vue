<template>
    <div class="custom-datepicker">
        <div
            class="form-floating"
            @click="openPicker"
            style="cursor: pointer;"
        >
            <input
                type="text"
                class="form-control"
                :id="inputId"
                :value="displayValue"
                :placeholder="placeholder"
                readonly
                :class="{ 'is-invalid': invalid }"
            >
            <label :for="inputId">
                <i class="fa-solid fa-calendar-days text-primary me-1"></i>
                {{ label }}
            </label>
        </div>

        <!-- Модальное окно с календарём -->
        <div
            class="modal fade"
            :id="modalId"
            tabindex="-1"
            ref="modalRef"
        >
            <div class="modal-dialog modal-dialog-centered modal-sm">
                <div class="modal-content">
                    <div class="modal-header py-2">
                        <div class="d-flex align-items-center w-100 justify-content-between">
                            <button
                                type="button"
                                class="btn btn-sm btn-light"
                                @click="prevMonth"
                            >
                                <i class="fa-solid fa-chevron-left"></i>
                            </button>

                            <div class="d-flex align-items-center gap-2">
                                <select
                                    class="form-select form-select-sm"
                                    v-model="viewMonth"
                                    style="width: auto;"
                                >
                                    <option v-for="(m, i) in months" :key="i" :value="i">
                                        {{ m }}
                                    </option>
                                </select>

                                <input
                                    type="number"
                                    class="form-control form-control-sm"
                                    v-model.number="viewYear"
                                    style="width: 80px;"
                                    min="1900"
                                    max="2100"
                                >
                            </div>

                            <button
                                type="button"
                                class="btn btn-sm btn-light"
                                @click="nextMonth"
                            >
                                <i class="fa-solid fa-chevron-right"></i>
                            </button>
                        </div>
                    </div>

                    <div class="modal-body p-2">
                        <!-- Дни недели -->
                        <div class="weekdays d-grid mb-1" style="grid-template-columns: repeat(7, 1fr);">
                            <div
                                v-for="day in weekDays"
                                :key="day"
                                class="text-center small fw-bold text-muted py-1"
                            >
                                {{ day }}
                            </div>
                        </div>

                        <!-- Сетка дней -->
                        <div class="days d-grid" style="grid-template-columns: repeat(7, 1fr); gap: 2px;">
                            <div
                                v-for="(day, idx) in calendarDays"
                                :key="idx"
                                class="day-cell text-center py-2 rounded"
                                :class="{
                                    'other-month': !day.currentMonth,
                                    'today': day.isToday,
                                    'selected': day.isSelected,
                                    'disabled': day.isDisabled,
                                    'weekend': day.isWeekend && day.currentMonth
                                }"
                                @click="selectDay(day)"
                            >
                                {{ day.date }}
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer py-2">
                        <button
                            type="button"
                            class="btn btn-sm btn-outline-secondary"
                            @click="clearDate"
                            v-if="required === false"
                        >
                            Очистить
                        </button>
                        <button
                            type="button"
                            class="btn btn-sm btn-outline-primary"
                            @click="selectToday"
                        >
                            Сегодня
                        </button>
                        <button
                            type="button"
                            class="btn btn-sm btn-primary"
                            @click="closePicker"
                        >
                            Готово
                        </button>
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
.day-cell {
    cursor: pointer;
    transition: all 0.15s ease;
    font-size: 0.9rem;
    user-select: none;
}

.day-cell:hover:not(.disabled):not(.selected) {
    background-color: #e9ecef;
}

.day-cell.other-month {
    color: #adb5bd;
}

.day-cell.today {
    font-weight: bold;
    color: #0d6efd;
    background-color: rgba(13, 110, 253, 0.08);
}

.day-cell.selected {
    background-color: #0d6efd !important;
    color: #fff !important;
    font-weight: bold;
}

.day-cell.weekend {
    color: #dc3545;
}

.day-cell.disabled {
    color: #dee2e6;
    cursor: not-allowed;
    pointer-events: none;
}

.weekdays {
    border-bottom: 1px solid #dee2e6;
    padding-bottom: 4px;
}

/* Чтобы select и input в шапке были компактные */
.modal-header select,
.modal-header input[type="number"] {
    font-size: 0.875rem;
}
</style>
