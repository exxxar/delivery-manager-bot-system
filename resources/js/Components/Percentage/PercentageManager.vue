```
<template>
    <section class="pm">
        <header class="pm__header">
            <div class="pm__heading">
                <div class="pm__icon"><i class="fa-solid fa-percent"></i></div>
                <div>
                    <h5 class="pm__title">Распределение процентов</h5>
                    <p class="pm__subtitle mb-0">
                        {{ ownerName || 'Настройка процентных начислений' }}
                    </p>
                </div>
            </div>
            <div class="pm__counter">
                <span class="pm__counter-value">{{ percentages.length }}</span>
                <span class="pm__counter-label">назначений</span>
            </div>
        </header>

        <div class="pm__own-percent">
            <div class="pm__own-percent-icon"><i class="fa-solid fa-percent"></i></div>
            <div class="pm__own-percent-copy">
                <strong>Собственный процент</strong>
                <span>Процент, который получает этот пользователь</span>
            </div>
            <div class="pm__own-percent-editor">
                <div class="input-group input-group-sm">
                    <input v-model.number="ownPercentValue" class="form-control" type="number" min="0" max="100" step="0.01" aria-label="Собственный процент пользователя">
                    <span class="input-group-text">%</span>
                    <button type="button" class="btn btn-primary" :disabled="savingOwnPercent || !isValidOwnPercent || Number(ownPercentValue) === Number(ownerPercent ?? 0)" @click="saveOwnPercent">
                        <span v-if="savingOwnPercent" class="spinner-border spinner-border-sm"></span>
                        <i v-else class="fa-solid fa-floppy-disk"></i>
                    </button>
                </div>
            </div>
        </div>

        <div class="pm__direction-switch" role="tablist" aria-label="Направление процента">
            <button
                type="button"
                class="pm__direction"
                :class="{ 'is-active': direction === 'outgoing' }"
                role="tab"
                :aria-selected="direction === 'outgoing'"
                @click="switchDirection('outgoing')"
            >Кому начисляет</button>
            <button
                type="button"
                class="pm__direction"
                :class="{ 'is-active': direction === 'incoming' }"
                role="tab"
                :aria-selected="direction === 'incoming'"
                @click="switchDirection('incoming')"
            >От кого получает</button>
        </div>

        <div class="pm__toolbar">
            <div>
                <h6 class="pm__section-title">
                    {{ direction === 'incoming' && isUserOwner ? 'Входящие начисления' : 'Исходящие начисления' }}
                </h6>
                <p class="pm__hint mb-0">
                    {{ direction === 'incoming' && isUserOwner
                    ? 'Настройте, от каких пользователей администратору начисляется процент.'
                    : 'Настройте, кому этот владелец начисляет процент.' }}
                </p>
            </div>
            <button type="button" class="pm__add-button" @click="toggleForm">
                <i :class="showAddForm ? 'fa-solid fa-xmark' : 'fa-solid fa-plus'"></i>
                {{ showAddForm ? 'Закрыть' : 'Добавить правило' }}
            </button>
        </div>

        <form v-if="showAddForm" class="pm__form" @submit.prevent="addPercentage">
            <div class="pm__form-heading">
                <span class="pm__form-icon"><i class="fa-solid fa-sliders"></i></span>
                <div>
                    <h6 class="mb-1">Новое правило</h6>
                    <p class="pm__hint mb-0">{{ isIncomingUserDirection ? 'Выберите плательщика: процент будет начисляться администратору.' : 'Выберите получателя: процент будет начисляться ему от текущего владельца.' }}</p>
                </div>
            </div>

            <div class="pm__flow">
                <div class="pm__person-card">
                    <label class="pm__label">{{ isIncomingUserDirection ? 'От кого начисляется' : 'Плательщик — от кого' }}</label>
                    <div class="pm__person">
            <span class="pm__avatar" :class="{ 'pm__avatar--fixed': isPayerFixed }">
              <i :class="isPayerFixed ? 'fa-solid fa-user-shield' : 'fa-solid fa-user'"></i>
            </span>
                        <div class="pm__person-info">
                            <strong>{{ isPayerFixed ? (ownerName || `Администратор #${ownerId}`) : (selectedUser?.name || 'Выберите плательщика') }}</strong>
                            <small>{{ isPayerFixed ? 'Текущий владелец' : 'Пользователь' }}</small>
                        </div>
                        <i v-if="isPayerFixed" class="fa-solid fa-lock pm__lock"></i>
                    </div>
                    <template v-if="!isPayerFixed">
                        <input
                            v-model.trim="userSearch"
                            type="search"
                            class="form-control pm__search"
                            placeholder="Найти плательщика по имени или email"
                            autocomplete="off"
                        >
                        <select v-model.number="newPercentage.user_id" class="form-select pm__select" required>
                            <option :value="null" disabled>{{ isIncomingUserDirection ? 'Выберите плательщика' : 'Выберите владельца' }}</option>
                            <option v-for="user in filteredUsers" :key="user.id" :value="Number(user.id)">
                                {{ user.name }}{{ user.email ? ` — ${user.email}` : '' }}
                            </option>
                        </select>
                        <small v-if="!filteredUsers.length" class="pm__field-note">Совпадений не найдено.</small>
                    </template>
                </div>

                <div class="pm__flow-arrow" :class="{ 'pm__flow-arrow--incoming': direction === 'incoming' && isUserOwner }">
                    <span>{{ formatPercent(newPercentage.percentage) }}%</span>
                    <i class="fa-solid fa-arrow-right"></i>
                </div>

                <div class="pm__person-card">
                    <label class="pm__label">{{ isIncomingUserDirection ? 'Кому начисляется' : 'Получатель — кому' }}</label>
                    <div class="pm__person">
            <span class="pm__avatar" :class="{ 'pm__avatar--fixed': isRecipientFixed }">
              <i :class="isRecipientFixed ? 'fa-solid fa-user-shield' : 'fa-solid fa-user'"></i>
            </span>
                        <div class="pm__person-info">
                            <strong>{{ isRecipientFixed ? (ownerName || `Администратор #${ownerId}`) : (selectedUser?.name || 'Выберите получателя') }}</strong>
                            <small>{{ isRecipientFixed ? 'Администратор получает процент' : 'Пользователь получит процент' }}</small>
                        </div>
                        <i v-if="isRecipientFixed" class="fa-solid fa-lock pm__lock"></i>
                    </div>
                    <template v-if="!isRecipientFixed">
                        <input
                            v-model.trim="userSearch"
                            type="search"
                            class="form-control pm__search"
                            placeholder="Найти получателя по имени или email"
                            autocomplete="off"
                        >
                        <select v-model.number="newPercentage.user_id" class="form-select pm__select" required>
                            <option :value="null" disabled>{{ isIncomingUserDirection ? 'Администратор — получатель' : 'Выберите получателя' }}</option>
                            <option v-for="user in filteredUsers" :key="user.id" :value="Number(user.id)">
                                {{ user.name }}{{ user.email ? ` — ${user.email}` : '' }}
                            </option>
                        </select>
                        <small v-if="!filteredUsers.length" class="pm__field-note">Совпадений не найдено.</small>
                    </template>
                </div>
            </div>

            <div class="pm__form-bottom">
                <div class="pm__percent-field">
                    <label class="pm__label" for="percentage-value">Размер процента</label>
                    <div class="input-group">
                        <input id="percentage-value" v-model.number="newPercentage.percentage" class="form-control" type="number" min="0.01" max="100" step="0.01" required>
                        <span class="input-group-text">%</span>
                    </div>
                </div>
                <div class="pm__form-note">
                    <i class="fa-solid fa-circle-info"></i>
                    <span>{{ isIncomingUserDirection
                        ? 'Направление: выбранный пользователь → текущий администратор.'
                        : 'Направление: текущий владелец → выбранный пользователь.' }}</span>
                </div>
                <button class="pm__save-button" type="submit" :disabled="saving || !newPercentage.user_id">
                    <span v-if="saving" class="spinner-border spinner-border-sm"></span>
                    <i v-else class="fa-solid fa-check"></i>
                    {{ saving ? 'Сохраняем…' : 'Сохранить правило' }}
                </button>
            </div>
        </form>

        <div v-if="loading" class="pm__loading">
            <span class="spinner-border spinner-border-sm"></span>
            Загружаем начисления…
        </div>

        <div v-else-if="!percentages.length" class="pm__empty">
            <div class="pm__empty-icon"><i class="fa-solid fa-arrows-left-right-to-line"></i></div>
            <h6>Пока нет правил начисления</h6>
            <p>Добавьте правило, чтобы настроить направление и размер процента.</p>
            <button type="button" class="pm__empty-button" @click="toggleForm">
                <i class="fa-solid fa-plus me-1"></i> Добавить первое правило
            </button>
        </div>

        <div v-else class="pm__table-wrap">
            <table class="table pm__table align-middle mb-0">
                <thead>
                <tr>
                    <th>{{ isUserOwner && direction === 'incoming' ? 'Плательщик' : 'Получатель' }}</th>
                    <th>Размер</th>
                    <th>Состояние</th>
                    <th class="text-end">Управление</th>
                </tr>
                </thead>
                <tbody>
                <tr v-for="item in percentages" :key="item.id">
                    <td>
                        <div class="pm__table-person">
                            <span class="pm__table-avatar"><i class="fa-solid fa-user"></i></span>
                            <span>
                  <strong>{{ relatedUser(item)?.name || ('Пользователь #' + relatedUserId(item)) }}</strong>
                  <small v-if="relatedUser(item)?.email">{{ relatedUser(item).email }}</small>
                </span>
                        </div>
                    </td>
                    <td><span class="pm__percent-pill">{{ formatPercent(item.percentage) }}%</span></td>
                    <td>
              <span class="pm__status" :class="item.is_active ? 'pm__status--active' : 'pm__status--inactive'">
                <span></span>{{ item.is_active ? 'Активен' : 'Отключён' }}
              </span>
                    </td>
                    <td class="text-end text-nowrap">
                        <button class="pm__icon-button" type="button" :disabled="busyId === item.id" :title="item.is_active ? 'Отключить' : 'Включить'" @click="toggleActive(item)">
                            <i :class="item.is_active ? 'fa-solid fa-pause' : 'fa-solid fa-play'"></i>
                        </button>
                        <button class="pm__icon-button pm__icon-button--danger" type="button" :disabled="busyId === item.id" title="Удалить" @click="removePercentage(item)">
                            <i class="fa-solid fa-trash"></i>
                        </button>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
    </section>
</template>

<script>
import { makeAxiosFactory } from '@/stores/utillites/makeAxiosFactory'
import { useUsersStore } from '@/stores/users'
import { useAlertStore } from '@/stores/utillites/useAlertStore'

export default {
    name: 'PercentageManager',
    props: {
        ownerType: { type: String, required: true }, // agent, supplier, user
        ownerId: { type: Number, required: true },
        ownerName: { type: String, default: '' },
        ownerPercent: { type: [Number, String], default: null },
    },
    data() {
        return {
            direction: 'outgoing',
            ownPercentValue: this.ownerPercent === null || this.ownerPercent === '' ? 0 : Number(this.ownerPercent),
            savingOwnPercent: false,
            percentages: [],
            users: [],
            loading: false,
            saving: false,
            busyId: null,
            showAddForm: false,
            userSearch: '',
            newPercentage: { user_id: null, percentage: 0, is_active: true },
            alertStore: useAlertStore(),
        }
    },
    computed: {
        isValidOwnPercent() {
            const value = Number(this.ownPercentValue)
            return Number.isFinite(value) && value >= 0 && value <= 100
        },
        isUserOwner() {
            return ['user', 'admin'].includes(String(this.ownerType || '').toLowerCase())
        },
        filteredUsers() {
            const query = this.userSearch.toLocaleLowerCase()
            return this.users
                .filter(user => Number(user.id) !== Number(this.ownerId))
                .filter(user => `${user.name || ''} ${user.email || ''} ${user.id || ''}`.toLocaleLowerCase().includes(query))
        },
        selectedUser() {
            return this.users.find(user => Number(user.id) === Number(this.newPercentage.user_id)) || null
        },
        isPayerFixed() {
            return !this.isUserOwner || this.direction === 'outgoing'
        },
        isRecipientFixed() {
            return this.isIncomingUserDirection
        },
        isIncomingUserDirection() {
            return this.isUserOwner && this.direction === 'incoming'
        },
    },
    watch: {
        ownerPercent(value) {
            this.ownPercentValue = value === null || value === '' ? 0 : Number(value)
        },
        ownerId: {
            immediate: true,
            handler() {
                this.loadUsers()
                this.loadPercentages()
            },
        },
        ownerType() {
            this.loadPercentages()
        },
    },
    methods: {
        notify(message, type = 'success') {
            if (typeof this.alertStore?.show === 'function') this.alertStore.show(message, type)
            else if (type === 'error') console.error(message)
        },
        async saveOwnPercent() {
            if (!this.isValidOwnPercent) {
                this.notify('Процент должен быть от 0 до 100', 'error')
                return
            }
            this.savingOwnPercent = true
            try {
                const endpointType = String(this.ownerType).toLowerCase()
                const endpoint = endpointType === 'agent'
                    ? `/agents/${this.ownerId}`
                    : endpointType === 'supplier'
                        ? `/suppliers/${this.ownerId}`
                        : `/users/${this.ownerId}`

                await makeAxiosFactory(endpoint, 'PUT', { percent: Number(this.ownPercentValue) })
                this.$emit('update:ownerPercent', Number(this.ownPercentValue))
                this.notify('Собственный процент обновлён')
            } catch (error) {
                this.notify(error?.response?.data?.message || 'Не удалось обновить собственный процент', 'error')
            } finally {
                this.savingOwnPercent = false
            }
        },
        async loadUsers() {
            try {
                const store = useUsersStore()
                if (!store.items?.length) await store.fetchAll()
                this.users = store.items || []
            } catch (error) {
                this.notify('Не удалось загрузить пользователей', 'error')
            }
        },
        async loadPercentages() {
            this.loading = true
            try {
                const direction = this.isUserOwner ? this.direction : 'outgoing'
                const params = this.isUserOwner
                    ? `type=user&id=${this.ownerId}&direction=${direction}&_=${Date.now()}`
                    : `type=${this.ownerType}&id=${this.ownerId}&_=${Date.now()}`
                const { data } = await makeAxiosFactory(`/percentages?${params}`, 'GET')
                const rows = Array.isArray(data?.percentages) ? data.percentages : []

                // Защита от старого backend, который возвращает смешанный список.
                if (this.isUserOwner && direction === 'incoming') {
                    this.percentages = rows.filter(item =>
                        Number(item.user_id) === Number(this.ownerId) &&
                        String(item.percentable_type || '').toLowerCase().includes('user')
                    )
                } else if (this.isUserOwner) {
                    this.percentages = rows.filter(item =>
                        Number(item.percentable_id) === Number(this.ownerId) &&
                        String(item.percentable_type || '').toLowerCase().includes('user')
                    )
                } else {
                    this.percentages = rows
                }
            } catch (error) {
                this.percentages = []
                this.notify(error?.response?.data?.message || 'Не удалось загрузить проценты', 'error')
            } finally {
                this.loading = false
            }
        },
        async switchDirection(direction) {
            if (this.direction === direction) return
            this.direction = direction
            this.showAddForm = false
            this.userSearch = ''
            this.newPercentage = { user_id: null, percentage: 0, is_active: true }
            await this.loadPercentages()
        },
        toggleForm() {
            this.showAddForm = !this.showAddForm
            this.userSearch = ''
            this.newPercentage = { user_id: null, percentage: 0, is_active: true }
        },
        relatedUser(item) {
            return this.isUserOwner && this.direction === 'incoming' ? item.percentable : item.user
        },
        relatedUserId(item) {
            return this.isUserOwner && this.direction === 'incoming' ? item.percentable_id : item.user_id
        },
        formatPercent(value) {
            return Number(value || 0).toLocaleString('ru-RU', { maximumFractionDigits: 2 })
        },
        async addPercentage() {
            const value = Number(this.newPercentage.percentage)
            if (!this.newPercentage.user_id || !Number.isFinite(value) || value <= 0 || value > 100) {
                this.notify('Выберите пользователя и укажите процент больше 0 и не больше 100', 'error')
                return
            }
            this.saving = true
            try {
                const payload = this.isUserOwner
                    ? {
                        type: 'user',
                        owner_id: this.direction === 'incoming' ? this.newPercentage.user_id : this.ownerId,
                        direction: this.direction,
                        user_id: this.direction === 'outgoing' ? this.newPercentage.user_id : undefined,
                        recipient_admin_id: this.direction === 'incoming' ? this.ownerId : undefined,
                        percentage: value,
                        is_active: true,
                    }
                    : {
                        type: this.ownerType,
                        owner_id: this.ownerId,
                        user_id: this.newPercentage.user_id,
                        percentage: value,
                        is_active: true,
                    }
                // Передаём направление и в query string, и в JSON body: некоторые
                // существующие backend-обработчики читают только один из этих источников.
                const endpoint = this.isUserOwner
                    ? `/percentages?direction=${encodeURIComponent(this.direction)}`
                    : '/percentages'
                await makeAxiosFactory(endpoint, 'POST', payload)
                await this.loadPercentages()
                this.showAddForm = false
                this.userSearch = ''
                this.newPercentage = { user_id: null, percentage: 0, is_active: true }
                this.notify('Процент сохранён')
            } catch (error) {
                this.notify(error?.response?.data?.message || 'Не удалось сохранить процент', 'error')
            } finally {
                this.saving = false
            }
        },
        async toggleActive(item) {
            this.busyId = item.id
            try {
                const { data } = await makeAxiosFactory(`/percentages/${item.id}`, 'PUT', { is_active: !item.is_active })
                const index = this.percentages.findIndex(row => row.id === item.id)
                if (index !== -1) this.percentages.splice(index, 1, data)
                this.notify('Статус обновлён')
            } catch (error) {
                this.notify(error?.response?.data?.message || 'Не удалось обновить статус', 'error')
            } finally {
                this.busyId = null
            }
        },
        async removePercentage(item) {
            if (!window.confirm('Удалить назначение процента?')) return
            this.busyId = item.id
            try {
                await makeAxiosFactory(`/percentages/${item.id}`, 'DELETE')
                this.percentages = this.percentages.filter(row => row.id !== item.id)
                this.notify('Процент удалён')
            } catch (error) {
                this.notify(error?.response?.data?.message || 'Не удалось удалить процент', 'error')
            } finally {
                this.busyId = null
            }
        },
    },
}
</script>

<style scoped>
.pm {
    --pm-primary: var(--bs-primary, #3459e6);
    --pm-border: var(--bs-border-color, #e5e7eb);
    --pm-muted: var(--bs-secondary-color, #737b8c);
    color: var(--bs-body-color);
    min-width: 0;
    border: 1px solid var(--pm-border);
    border-radius: 1rem;
    background: var(--bs-body-bg);
    padding: clamp(1rem, 2.5vw, 1.5rem);
    box-shadow: 0 8px 30px rgba(30, 41, 59, .045);
}
.pm__header, .pm__heading, .pm__toolbar, .pm__form-heading, .pm__person, .pm__table-person {
    display: flex;
    align-items: center;
}
.pm__header { justify-content: space-between; gap: 1rem; margin-bottom: 1.4rem; }
.pm__heading { gap: .85rem; min-width: 0; }
.pm__icon {
    width: 46px; height: 46px; flex: 0 0 46px; display: grid; place-items: center;
    border-radius: 14px; color: var(--pm-primary); background: color-mix(in srgb, var(--pm-primary) 10%, transparent);
    font-size: 1.15rem;
}
.pm__title { margin: 0 0 .2rem; font-size: 1.08rem; font-weight: 750; letter-spacing: -.02em; }
.pm__subtitle, .pm__hint { color: var(--pm-muted); font-size: .83rem; }
.pm__counter { display: flex; flex-direction: column; align-items: flex-end; }
.pm__counter-value { font-size: 1.35rem; font-weight: 750; line-height: 1.1; }
.pm__counter-label { color: var(--pm-muted); font-size: .73rem; }
.pm__own-percent {
    display: flex; align-items: center; gap: .85rem; padding: .9rem 1rem;
    margin: 0 0 1.25rem; border: 1px solid var(--pm-border); border-radius: .85rem;
    background: var(--bs-tertiary-bg, #f8fafc);
}
.pm__own-percent-icon {
    display: grid; place-items: center; width: 42px; height: 42px; flex: 0 0 42px;
    border-radius: 12px; color: var(--pm-primary);
    background: color-mix(in srgb, var(--pm-primary) 10%, transparent);
}
.pm__own-percent-copy { display: flex; flex-direction: column; gap: .15rem; min-width: 0; }
.pm__own-percent-copy strong { font-size: .9rem; }
.pm__own-percent-copy span { color: var(--pm-muted); font-size: .78rem; }
.pm__own-percent-editor { margin-left: auto; width: min(220px, 42%); }
.pm__own-percent-editor .input-group { flex-wrap: nowrap; }
.pm__own-percent-editor input { min-width: 0; }
.pm__own-percent-editor .btn { min-width: 38px; }
.pm__direction-switch {
    display: inline-flex; gap: .25rem; padding: .25rem; margin-bottom: 1.25rem;
    border: 1px solid var(--pm-border); border-radius: .7rem;
    background: var(--bs-tertiary-bg, #f4f6f9);
}
.pm__direction {
    border: 0; border-radius: .5rem; padding: .6rem 1rem;
    background: transparent; color: var(--pm-muted); font-size: .86rem;
    font-weight: 650; white-space: nowrap; transition: background .15s, color .15s, box-shadow .15s;
}
.pm__direction:hover { color: var(--bs-body-color); }
.pm__direction.is-active {
    color: var(--pm-primary); background: var(--bs-body-bg);
    box-shadow: 0 1px 3px rgba(0, 0, 0, .10);
}
.pm__toolbar { justify-content: space-between; gap: 1rem; margin-bottom: 1rem; }
.pm__section-title { font-size: .95rem; font-weight: 750; margin: 0 0 .25rem; }
.pm__add-button, .pm__save-button, .pm__empty-button {
    display: inline-flex; align-items: center; justify-content: center; gap: .5rem;
    border: 0; border-radius: .7rem; padding: .7rem 1rem; font-size: .84rem; font-weight: 650;
    background: var(--pm-primary); color: #fff; transition: transform .15s, opacity .15s;
}
.pm__add-button:hover, .pm__save-button:hover, .pm__empty-button:hover { transform: translateY(-1px); }
.pm__form { border: 1px solid var(--pm-border); border-radius: .9rem; padding: clamp(.9rem, 2vw, 1.25rem); margin-bottom: 1.5rem; background: var(--bs-tertiary-bg, #f8fafc); }
.pm__form-heading { gap: .7rem; margin-bottom: 1rem; }
.pm__form-heading h6 { font-size: .95rem; font-weight: 750; }
.pm__form-icon { display: grid; place-items: center; width: 38px; height: 38px; border-radius: 11px; background: var(--bs-body-bg); color: var(--pm-primary); border: 1px solid var(--pm-border); }
.pm__flow { display: grid; grid-template-columns: minmax(0, 1fr) 54px minmax(0, 1fr); gap: .65rem; align-items: center; }
.pm__person-card { min-width: 0; border: 1px solid var(--pm-border); border-radius: .8rem; padding: .85rem; background: var(--bs-body-bg); }
.pm__label { display: block; margin-bottom: .55rem; color: var(--pm-muted); font-size: .76rem; font-weight: 700; }
.pm__person { gap: .65rem; min-width: 0; margin-bottom: .75rem; }
.pm__avatar { display: grid; place-items: center; width: 38px; height: 38px; flex: 0 0 38px; border-radius: 50%; color: var(--pm-muted); background: var(--bs-tertiary-bg, #f3f4f6); }
.pm__avatar--fixed { color: var(--pm-primary); background: color-mix(in srgb, var(--pm-primary) 10%, transparent); }
.pm__person-info { display: flex; flex-direction: column; min-width: 0; gap: .15rem; }
.pm__person-info strong { overflow-wrap: anywhere; font-size: .82rem; }
.pm__person-info small { color: var(--pm-muted); font-size: .72rem; }
.pm__lock { margin-left: auto; color: var(--pm-muted); font-size: .75rem; }
.pm__search, .pm__select { font-size: .82rem; }
.pm__search { margin-bottom: .5rem; }
.pm__field-note { display: block; margin-top: .4rem; color: var(--pm-muted); }
.pm__flow-arrow { display: flex; flex-direction: column; align-items: center; justify-content: center; gap: .45rem; color: var(--pm-primary); }
.pm__flow-arrow span { border-radius: 99px; padding: .25rem .55rem; background: color-mix(in srgb, var(--pm-primary) 10%, transparent); font-size: .8rem; font-weight: 800; white-space: nowrap; }
.pm__flow-arrow i { font-size: 1.05rem; transition: transform .2s ease; }
.pm__flow-arrow--incoming i { transform: rotate(180deg); }
.pm__form-bottom { display: grid; grid-template-columns: minmax(150px, 220px) minmax(0, 1fr) auto; align-items: end; gap: 1rem; margin-top: 1rem; }
.pm__percent-field .input-group-text { background: var(--bs-body-bg); }
.pm__form-note { display: flex; align-items: flex-start; gap: .5rem; color: var(--pm-muted); font-size: .77rem; line-height: 1.45; padding-bottom: .5rem; }
.pm__form-note i { color: var(--pm-primary); margin-top: .1rem; }
.pm__save-button:disabled { opacity: .6; cursor: not-allowed; transform: none; }
.pm__loading { display: flex; justify-content: center; align-items: center; gap: .6rem; padding: 3rem 1rem; color: var(--pm-muted); font-size: .85rem; }
.pm__empty { padding: 2.5rem 1rem; text-align: center; border: 1px dashed var(--pm-border); border-radius: .85rem; }
.pm__empty-icon { display: grid; place-items: center; width: 54px; height: 54px; margin: 0 auto .85rem; border-radius: 16px; background: var(--bs-tertiary-bg, #f8fafc); color: var(--pm-muted); font-size: 1.25rem; }
.pm__empty h6 { font-weight: 750; font-size: .95rem; }
.pm__empty p { color: var(--pm-muted); font-size: .82rem; margin-bottom: 1rem; }
.pm__empty-button { padding: .6rem .85rem; font-size: .8rem; }
.pm__table-wrap { overflow: hidden; border: 1px solid var(--pm-border); border-radius: .8rem; }
.pm__table thead th { padding: .8rem 1rem; color: var(--pm-muted); background: var(--bs-tertiary-bg, #f8fafc); border-bottom: 1px solid var(--pm-border); font-size: .73rem; font-weight: 750; text-transform: uppercase; letter-spacing: .035em; white-space: nowrap; }
.pm__table tbody td { padding: .85rem 1rem; border-color: var(--pm-border); }
.pm__table tbody tr:last-child td { border-bottom: 0; }
.pm__table-person { gap: .65rem; min-width: 180px; }
.pm__table-avatar { display: grid; place-items: center; width: 34px; height: 34px; flex: 0 0 34px; border-radius: 11px; background: var(--bs-tertiary-bg, #f8fafc); color: var(--pm-muted); }
.pm__table-person > span:last-child { display: flex; flex-direction: column; min-width: 0; gap: .15rem; }
.pm__table-person strong { font-size: .83rem; }
.pm__table-person small { color: var(--pm-muted); font-size: .73rem; }
.pm__percent-pill { display: inline-flex; padding: .35rem .6rem; border-radius: .55rem; background: rgba(21, 150, 106, .1); color: #13845f; font-size: .82rem; font-weight: 800; }
.pm__status { display: inline-flex; align-items: center; gap: .4rem; font-size: .78rem; font-weight: 650; white-space: nowrap; }
.pm__status > span { width: 7px; height: 7px; border-radius: 50%; background: currentColor; }
.pm__status--active { color: #13845f; }
.pm__status--inactive { color: var(--pm-muted); }
.pm__icon-button { display: inline-grid; place-items: center; width: 33px; height: 33px; border: 1px solid var(--pm-border); border-radius: .55rem; color: var(--pm-muted); background: var(--bs-body-bg); margin-left: .25rem; transition: .15s; }
.pm__icon-button:hover:not(:disabled) { color: var(--pm-primary); border-color: var(--pm-primary); }
.pm__icon-button--danger:hover:not(:disabled) { color: #dc3545; border-color: #dc3545; }
.pm__icon-button:disabled { opacity: .5; cursor: wait; }
@media (max-width: 900px) {
    .pm__form-bottom { grid-template-columns: minmax(140px, 200px) minmax(0, 1fr); }
    .pm__save-button { grid-column: 1 / -1; }
}
@media (max-width: 650px) {
    .pm__direction-switch { display: flex; max-width: 100%; }
    .pm__direction { padding: .55rem .7rem; font-size: .8rem; }
    .pm__toolbar { align-items: flex-start; }
    .pm__add-button { white-space: nowrap; }
    .pm__flow { grid-template-columns: 1fr; }
    .pm__flow-arrow { flex-direction: row; padding: .15rem 0; }
    .pm__flow-arrow i { transform: rotate(90deg); }
    .pm__flow-arrow--incoming i { transform: rotate(270deg); }
    .pm__form-bottom { grid-template-columns: 1fr; }
    .pm__save-button { grid-column: auto; width: 100%; }
    .pm__header { align-items: flex-start; }
    .pm__own-percent { flex-wrap: wrap; }
    .pm__own-percent-editor { width: 100%; margin-left: 0; }
}
</style>

```
