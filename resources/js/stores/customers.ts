import { defineStore } from 'pinia'
import axios from 'axios'
import {makeAxiosFactory} from "./utillites/makeAxiosFactory";

const path: string = '/bot-api/customers'

export interface Customer {
    id: number
    name: string
    company_name: string
    address: string
    phone: string
    email: string
}

export const useCustomersStore = defineStore('customers', {
    state: () => ({
        items: [] as Customer[],
        loading: false,
        error: null as string | null
    }),
    getters: {
        byId: (s) => (id: number) => s.items.find(c => c.id === id)
    },
    actions: {
        // @ts-ignore
        async fetchAll() {
            this.loading = true; this.error = null
            try {
                const { data } = await makeAxiosFactory('/api/customers')
                this.items = data
            } catch (e: any) {
                this.error = e?.message || 'Failed to load customers'
            } finally {
                this.loading = false
            }
        },
        // @ts-ignore
        async fetchAllByPage(page = 1) {
            const { data } = await makeAxiosFactory(`${path}/?page=${page}`, 'GET')
            this.items = data.data
            this.pagination = data
        },
        // @ts-ignore
        async fetchByUrl(url: string) {
            const { data } = await makeAxiosFactory(url, 'GET')
            this.items = data.data
            this.pagination = data
        },
        async create(payload: Omit<Customer, 'id'>) {
            const { data } = await makeAxiosFactory(path,'POST', payload)
            this.items.push(data); return data
        },
        async update(id: number, payload: Partial<Customer>) {
            const { data } = await makeAxiosFactory(path, 'PUT', payload)
            const i = this.items.findIndex(c => c.id === id)
            if (i !== -1) this.items[i] = data; return data
        },
        // @ts-ignore
        async remove(id: number) {
            await makeAxiosFactory(`${path}/${id}`,'DELETE')
            this.items = this.items.filter(c => c.id !== id)
        }
    }
})
