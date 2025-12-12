
<template>

    <h6 class="mb-3">Ближайшие дни рождения</h6>

    <!-- Поиск -->
    <div class="form-floating mb-2">
        <input type="search"
               v-model="search"
               class="form-control"
               id="searchInput"
               placeholder="Поиск..." />
        <label for="searchInput">Поиск</label>
    </div>

    <!-- Лоадер -->
    <div v-if="birthdayStore.loading" class="text-center py-4">
        <div class="spinner-border text-primary"></div>
    </div>

    <!-- Список -->
    <div v-else>

        <div v-if="grouped.length === 0" class="text-muted">
            В ближайшую неделю именинников нет
        </div>

        <div v-for="group in grouped" :key="group.date" class="mb-2">

            <!-- Заголовок даты -->
            <h5 class="border-bottom pb-1 mb-3">
                {{ formatDate(group.date) }} — {{ group.weekday }}
            </h5>

            <ul class="list-group">

                <li v-for="item in group.items"
                    :key="item.name + item.type"
                    class="list-group-item d-flex justify-content-between align-items-center">

                    <div>
                        <div class="fw-bold">{{ item.name }}</div>
                        <small class="text-muted">
                            {{ item.type === 'пользователь' ? 'Сотрудник' : 'Поставщик' }}
                        </small>
                    </div>

                    <span class="badge bg-primary">
                        {{ item.weekday }}
                    </span>

                </li>

            </ul>

        </div>

    </div>

</template>
<script>
import { useBirthdaysStore } from "@/stores/useBirthdaysStore";

export default {
    name: "BirthdayList",

    data() {
        return {
            search: "",
            birthdayStore: useBirthdaysStore()
        }
    },

    computed: {
        filteredBirthdays() {
            if (!this.search) return this.birthdayStore.items || []

            const q = this.search.toLowerCase()

            return this.birthdayStore.items.filter(item =>
                Object.values(item).some(val =>
                    val ? String(val).toLowerCase().includes(q) : false
                )
            )
        },

        grouped() {
            const groups = {}

            this.filteredBirthdays.forEach(item => {
                // Ключ только месяц-день
                const key = item.date.slice(5) // "MM-DD"

                if (!groups[key]) {
                    groups[key] = {
                        key: key,
                        date: item.date,       // можно оставить для сортировки
                        weekday: item.weekday,

                        items: []
                    }
                }

                groups[key].items.push(item)
            })

            // Сортировка по MM-DD
            return Object.values(groups).sort((a, b) => a.key.localeCompare(b.key))
        }
    },

    created() {
        this.fetchBirthdays()
    },

    methods: {
        async fetchBirthdays() {
            await this.birthdayStore.fetchNextWeek()
        },

        formatDate(date) {
            return new Date(date).toLocaleDateString("ru-RU", {
                day: "numeric",
                month: "long"
            })
        }
    }
}
</script>

<style scoped>
.list-group-item {
    cursor: default;
}
</style>
