import { defineStore } from "pinia";
import { i18n } from "../i18n";

export const useLangStore = defineStore("lang", {
    state: () => ({
        locale: i18n.global.locale.value,
    }),
    actions: {
        setLocale(newLocale: string) {
            this.locale = newLocale;
            // @ts-ignore
            i18n.global.locale.value = newLocale;
        },
    },
});
