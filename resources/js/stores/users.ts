import {defineStore} from 'pinia'
import {makeAxiosFactory} from './utillites/makeAxiosFactory'
import {useAlertStore} from './utillites/useAlertStore'
import {useConfigStore} from "./config";
import axios from 'axios'

export interface User {
    id: number
    name: string
    fio_from_telegram?: string
    email: string
    telegram_chat_id?: string
    role: number
    percent: number
    mentor_percent: number
    is_work: boolean
    email_verified_at?: string
    blocked_at?: string
    blocked_message?: string
}


const path: string = '/users'


export const useUsersStore = defineStore('users', {
    state: () => ({
        items: [] as User[],
        self: null,
        loading: false,
        token: null,
        error: null as string | null,
    }),
    getters: {
        byId: (s) => (id: number) => s.items.find(u => u.id === id),
    },
    actions: {
        setRole(role) {
            this.self.role = role
        },

        async login(form) {
            this.loading = true;

            try {
                const response = await axios.post(
                    '/api/auth/login',
                    form,
                    {
                        withCredentials: true
                    }
                );

                this.user = response.data;

                return true;
            } finally {
                this.loading = false;
            }
        },

        async loginTelegram(payload) {
            const response = await axios.post(
                '/api/auth/telegram',
                payload,
                {
                    withCredentials: true
                }
            );

            this.user = response.data;
        },

        async me() {
            try {
                const response = await axios.get(
                    '/api/auth/me',
                    {
                        withCredentials: true
                    }
                );

                this.user = response.data;
            } catch (e) {
                this.user = null;
            }
        },

        async logout() {
            await axios.post(
                '/api/auth/logout',
                {},
                {
                    withCredentials: true
                }
            );

            this.user = null;
        },
        async csrf() {
            axios.defaults.withCredentials = true

            await axios.get('/sanctum/csrf-cookie').then(() => {
                axios.defaults.withXSRFToken = true

                const token = decodeURIComponent(
                    document.cookie
                        .split('; ')
                        .find(row => row.startsWith('XSRF-TOKEN='))
                        ?.split('=')[1]
                )


                const res = makeAxiosFactory('/users/self', 'post', {}, {
                    withCredentials: true,
                    headers: {
                        'X-XSRF-TOKEN': token,
                        'Accept': 'application/json'
                    }
                }).then(resp => {
                    this.self = resp.data
                })


                //this.token = res.data.token
                //axios.defaults.headers.common['Authorization'] = `Bearer ${this.token}`
            })

        },
        async fetchSelf() {

            const configStore = useConfigStore();

            if (configStore.apiPrefix === 'api') {

                await this.csrf()
                return true
            }


            this.loading = true
            this.error = null
            try {
                const {data} = await makeAxiosFactory(`${path}/self`, 'POST')
                this.self = data
            } catch (e: any) {
                this.error = e?.message || 'Failed to load agents'
            } finally {
                this.loading = false
            }
            return true
        },
        // @ts-ignore
        async fetchFiltered(page = 1) {
            this.loading = true
            this.error = null
            try {
                const params = new URLSearchParams()

                // фильтры
                // @ts-ignore
                Object.entries(this.filters).forEach(([key, value]) => {
                    if (value !== null && value !== undefined && value !== '' && value !== false) {
                        params.append(key, String(value))
                    }
                })

                // сортировка
                params.append('sort_field', this.sort.field)
                params.append('sort_direction', this.sort.direction)

                // пагинация
                params.append('page', String(page))

                const {data} = await makeAxiosFactory(`${path}?${params.toString()}`, 'GET')
                this.items = data.data
                this.pagination = data
            } catch (error: any) {
                this.error = error.response?.data?.message ?? 'Ошибка загрузки пользователей'
            } finally {
                this.loading = false
            }
        },

        setFilters(filters: Record<string, any>) {
            this.filters = filters
        },

        setSort(field: string, direction: 'asc' | 'desc') {
            this.sort = {field, direction}
        },
        // @ts-ignore
        async fetchAll() {
            this.loading = true
            this.error = null
            try {
                const {data} = await makeAxiosFactory(`${path}`, 'GET')
                this.items = data.data
                console.log("data=>", data)
            } catch (e: any) {
                this.error = e?.message || 'Failed to load users'
            } finally {
                this.loading = false
            }
        },
        // @ts-ignore
        async fetchAllByPage(page = 1) {
            const {data} = await makeAxiosFactory(`${path}?page=${page}`, 'GET')
            this.items = data.data
            // @ts-ignore
            this.pagination = data
        },
        // @ts-ignore
        async fetchByUrl(url: string) {
            const {data} = await makeAxiosFactory(url, 'GET')
            this.items = data.data
            // @ts-ignore
            this.pagination = data
        },


        async fetchOne(id: number) {
            try {
                const {data} = await makeAxiosFactory(`${path}/${id}`, 'GET')
                return data as User
            } catch (e: any) {
                this.error = e?.message || 'Failed to load user'
                throw e
            }
        },

        async createPrimary(payload: object) {
            const {data} = await makeAxiosFactory(`${path}/primary`, 'POST', payload)
            this.items.push(data)
            return data as User
        },
        async create(payload: Omit<User, 'id'>) {
            const {data} = await makeAxiosFactory(`${path}`, 'POST', payload)
            this.items.push(data)
            return data as User
        },

        async update(id: number, payload: object) {
            const {data} = await makeAxiosFactory(`${path}/${id}`, 'PUT', payload)
            const idx = this.items.findIndex(u => u.id === id)
            if (idx !== -1) this.items[idx] = data
            return data as User
        },
        // @ts-ignore
        async selfRoleRequest() {
            await makeAxiosFactory(`${path}/request-role`, 'GET')
        },
        // @ts-ignore
        async remove(id: number) {

            await makeAxiosFactory(`${path}/${id}`, 'DELETE')
            this.items = this.items.filter(u => u.id !== id)
        },
        // @ts-ignore
        async getTelegramLink(id: number) {
            await makeAxiosFactory(`${path}/${id}/tg`, 'GET')
        },


        // 🔹 Дополнительные экшены
        async updateRole(id: number, role: number) {
            const {data} = await makeAxiosFactory(`${path}/${id}/role`, 'POST', {
                role: role
            })
            const idx = this.items.findIndex(u => u.id === id)
            if (idx !== -1) this.items[idx] = data
            return data as User
        },

        async updatePercent(id: number, percent: number) {
            const {data} = await makeAxiosFactory(`${path}/${id}/percent`, 'PATCH', {percent})
            const idx = this.items.findIndex(u => u.id === id)
            if (idx !== -1) this.items[idx] = data
            return data as User
        },

        async updateWorkStatus(id: number, is_work: boolean) {
            const alertStore = useAlertStore()
            const {data} = await makeAxiosFactory(`${path}/${id}/work-status`, 'POST', {
                is_work: is_work
            })

            alertStore.show("Статус успешно обновлен")

            const idx = this.items.findIndex(u => u.id === id)
            if (idx !== -1) this.items[idx] = data
            return data as User
        },

        async block(id: number, blocked_message?: string) {
            const {data} = await makeAxiosFactory(`${path}/${id}/block`, 'PATCH', {blocked_message})
            const idx = this.items.findIndex(u => u.id === id)
            if (idx !== -1) this.items[idx] = data
            return data as User
        },

        async unblock(id: number) {
            const {data} = await makeAxiosFactory(`${path}/${id}/unblock`, 'PATCH')
            const idx = this.items.findIndex(u => u.id === id)
            if (idx !== -1) this.items[idx] = data
            return data as User
        },
    },
})
