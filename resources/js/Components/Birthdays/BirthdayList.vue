<template>
    <section class="birthday-panel">
        <header class="birthday-header">
            <div class="birthday-heading-icon" aria-hidden="true">
                <i class="fa-solid fa-cake-candles"></i>
            </div>
            <div class="birthday-heading-copy">
                <h2 class="birthday-title">Ближайшие дни рождения</h2>
                <p class="birthday-subtitle">Именинники на ближайшую неделю</p>
            </div>
            <span class="birthday-count" v-if="!birthdayStore.loading">{{ filteredBirthdays.length }}</span>
        </header>

        <div class="birthday-search">
            <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
            <input
                id="birthdaySearchInput"
                v-model="search"
                type="search"
                class="form-control"
                placeholder="Поиск по имени или типу..."
                aria-label="Поиск именинников"
            />
            <button v-if="search" type="button" class="clear-search" aria-label="Очистить поиск" @click="search = ''">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <div v-if="birthdayStore.loading" class="birthday-loading" role="status" aria-live="polite">
            <span class="spinner-border text-primary" aria-hidden="true"></span>
            <span>Загружаем дни рождения…</span>
        </div>

        <div v-else-if="grouped.length === 0" class="birthday-empty">
            <div class="empty-icon"><i class="fa-regular fa-calendar-check"></i></div>
            <h3>{{ search ? 'Ничего не найдено' : 'Пока без именинников' }}</h3>
            <p>{{ search ? 'Попробуйте изменить поисковый запрос.' : 'В ближайшую неделю именинников нет.' }}</p>
            <button v-if="search" type="button" class="btn btn-outline-primary btn-sm" @click="search = ''">Сбросить поиск</button>
        </div>

        <div v-else class="birthday-groups">
            <section v-for="group in grouped" :key="group.date" class="birthday-day">
                <div class="day-heading">
                    <div class="day-date-icon"><i class="fa-regular fa-calendar"></i></div>
                    <div class="day-heading-text">
                        <h3>{{ formatDate(group.date) }}</h3>
                        <span>{{ group.weekday }}</span>
                    </div>
                    <span class="day-count">{{ group.items.length }} {{ group.items.length === 1 ? 'именинник' : (group.items.length < 5 ? 'именинника' : 'именинников') }}</span>
                </div>

                <ul class="birthday-list">
                    <li v-for="item in group.items" :key="item.name + item.type + item.date" class="birthday-item">
                        <div class="person-avatar" :class="item.type === 'пользователь' ? 'avatar-employee' : 'avatar-supplier'">
                            <i :class="item.type === 'пользователь' ? 'fa-solid fa-user' : 'fa-solid fa-truck'" aria-hidden="true"></i>
                        </div>
                        <div class="person-details">
                            <div class="person-name">{{ item.name }}</div>
                            <div class="person-type">{{ item.type === 'пользователь' ? 'Сотрудник' : 'Поставщик' }}</div>
                        </div>
                        <div class="person-birthday" v-if="item.date">
                            <i class="fa-solid fa-cake-candles" aria-hidden="true"></i>
                            <span>{{ item.date }}</span>
                        </div>
                    </li>
                </ul>
            </section>
        </div>
    </section>
</template>

<script>
import { useBirthdaysStore } from "@/stores/useBirthdaysStore";

export default {
    name: "BirthdayList",
    data() {
        return {
            search: "",
            birthdayStore: useBirthdaysStore()
        };
    },
    computed: {
        filteredBirthdays() {
            const items = this.birthdayStore.items || [];
            if (!this.search) return items;
            const q = this.search.toLowerCase().trim();
            return items.filter(item =>
                Object.values(item).some(val => val != null && String(val).toLowerCase().includes(q))
            );
        },
        grouped() {
            const groups = {};
            this.filteredBirthdays.forEach(item => {
                if (!item.date) return;
                const key = item.date.slice(5);
                if (!groups[key]) {
                    groups[key] = {
                        key,
                        date: item.date,
                        weekday: item.weekday,
                        items: []
                    };
                }
                groups[key].items.push(item);
            });
            return Object.values(groups).sort((a, b) => a.key.localeCompare(b.key));
        }
    },
    created() {
        this.fetchBirthdays();
    },
    methods: {
        async fetchBirthdays() {
            await this.birthdayStore.fetchNextWeek();
        },
        formatDate(date) {
            return new Date(date).toLocaleDateString("ru-RU", {
                day: "numeric",
                month: "long"
            });
        }
    }
};
</script>

<style scoped>
.birthday-panel {
    --birthday-navy: #172b4d;
    --birthday-blue: #2563eb;
    --birthday-red: #dc3545;
    background: #fff;
    border: 1px solid #e8edf5;
    border-radius: 20px;
    padding: 22px;
    box-shadow: 0 8px 28px rgba(23, 43, 77, .06);
    color: var(--birthday-navy);
}
.birthday-header { display: flex; align-items: center; gap: 13px; margin-bottom: 20px; }
.birthday-heading-icon { width: 48px; height: 48px; flex: 0 0 48px; display: grid; place-items: center; border-radius: 15px; color: var(--birthday-blue); background: #edf4ff; font-size: 1.25rem; }
.birthday-heading-copy { min-width: 0; flex: 1; }
.birthday-title { margin: 0; font-size: 1.1rem; font-weight: 750; letter-spacing: -.02em; }
.birthday-subtitle { margin: 4px 0 0; color: #748198; font-size: .85rem; }
.birthday-count { min-width: 32px; height: 32px; display: inline-grid; place-items: center; padding: 0 9px; border-radius: 11px; color: #1d4ed8; background: #edf4ff; font-size: .85rem; font-weight: 750; }
.birthday-search { display: flex; align-items: center; gap: 10px; padding: 0 12px; border: 1px solid #dfe6f0; border-radius: 12px; background: #f8faff; margin-bottom: 20px; }
.birthday-search > i { color: #8290a7; }
.birthday-search .form-control { min-width: 0; height: 46px; padding-left: 0; border: 0; outline: 0; box-shadow: none; background: transparent; color: var(--birthday-navy); }
.birthday-search .form-control:focus { box-shadow: none; }
.clear-search { border: 0; padding: 5px; background: transparent; color: #8290a7; }
.birthday-loading { min-height: 150px; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 12px; color: #748198; font-size: .9rem; }
.birthday-loading .spinner-border { width: 1.8rem; height: 1.8rem; }
.birthday-empty { padding: 34px 16px; text-align: center; border: 1px dashed #dbe3ef; border-radius: 16px; background: #fafcff; }
.empty-icon { width: 54px; height: 54px; display: grid; place-items: center; margin: 0 auto 12px; border-radius: 17px; color: var(--birthday-blue); background: #edf4ff; font-size: 1.35rem; }
.birthday-empty h3 { margin: 0 0 6px; font-size: 1rem; font-weight: 750; }
.birthday-empty p { margin: 0 0 14px; color: #748198; font-size: .9rem; }
.birthday-groups { display: flex; flex-direction: column; gap: 18px; }
.birthday-day { overflow: hidden; border: 1px solid #e8edf5; border-radius: 15px; }
.day-heading { display: flex; align-items: center; gap: 11px; padding: 13px 15px; background: #f8faff; border-bottom: 1px solid #e8edf5; }
.day-date-icon { width: 37px; height: 37px; flex: 0 0 37px; display: grid; place-items: center; border-radius: 11px; color: var(--birthday-blue); background: #eaf1ff; }
.day-heading-text { flex: 1; min-width: 0; }
.day-heading-text h3 { margin: 0; font-size: .96rem; font-weight: 750; text-transform: capitalize; }
.day-heading-text span { display: block; margin-top: 2px; color: #748198; font-size: .8rem; }
.day-count { padding: 5px 9px; border-radius: 9px; background: #fff; border: 1px solid #e5ebf4; color: #65738a; font-size: .75rem; white-space: nowrap; }
.birthday-list { list-style: none; padding: 0; margin: 0; }
.birthday-item { display: flex; align-items: center; gap: 12px; padding: 13px 15px; }
.birthday-item + .birthday-item { border-top: 1px solid #f0f3f8; }
.person-avatar { width: 39px; height: 39px; flex: 0 0 39px; display: grid; place-items: center; border-radius: 13px; }
.avatar-employee { color: #2563eb; background: #edf4ff; }
.avatar-supplier { color: #c2414b; background: #fff0f1; }
.person-details { flex: 1; min-width: 0; }
.person-name { overflow-wrap: anywhere; font-weight: 700; font-size: .92rem; }
.person-type { margin-top: 3px; color: #748198; font-size: .8rem; }
.person-birthday { display: flex; align-items: center; gap: 6px; color: #6b7890; font-size: .8rem; white-space: nowrap; }
.person-birthday i { color: var(--birthday-red); }
@media (max-width: 575.98px) {
    .birthday-panel { padding: 15px; border-radius: 16px; }
    .birthday-header { gap: 10px; margin-bottom: 16px; }
    .birthday-heading-icon { width: 42px; height: 42px; flex-basis: 42px; border-radius: 13px; }
    .birthday-title { font-size: 1rem; }
    .birthday-subtitle { font-size: .78rem; }
    .day-heading { padding: 11px; gap: 8px; }
    .day-count { font-size: .68rem; padding: 4px 6px; }
    .birthday-item { padding: 12px 11px; gap: 9px; }
    .person-avatar { width: 35px; height: 35px; flex-basis: 35px; }
    .person-name { font-size: .86rem; }
    .person-birthday { font-size: .73rem; }
}
</style>
