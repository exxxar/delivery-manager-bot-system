import axios, { AxiosResponse, AxiosRequestConfig } from "axios";
import { useAlertStore } from "./useAlertStore";
import { useConfigStore } from "../config.js";
import { generateIdempotencyKey } from '@/utilites/idempotency.js'
import { markOffline, markOnline } from '@/utilites/networkStatus.js'

// 🔹 Глобальные настройки axios (один раз при загрузке модуля)
axios.defaults.baseURL = window.location.origin;
axios.defaults.withCredentials = true;
axios.defaults.withXSRFToken = true;

console.log("TEST", axios.defaults.baseURL)
// 🔹 Перехватчик ответов
axios.interceptors.response.use(
    (response) => {
        markOnline();
        return response;
    },
    (error) => {
        if (!error.response) {
            markOffline();
        }
        return Promise.reject(error);
    }
);

// 🔹 Перехватчик запросов (идемпотентность)
axios.interceptors.request.use((config) => {
    if (config.method === 'post' || config.method === 'put' || config.method === 'patch') {
        if (!config.headers['X-Idempotency-Key']) {
            config.headers['X-Idempotency-Key'] = generateIdempotencyKey();
        }
    }
    return config;
});


/**
 * Формирует корректный URL из относительного пути.
 * Гарантирует отсутствие дублирования префикса и корректные слеши.
 */
function buildUrl(link: string, apiPrefix: string): string {
    // 🔹 1. Если это уже полный URL — возвращаем как есть
    if (link.startsWith('http://') || link.startsWith('https://')) {
        return link;
    }

    // 🔹 2. Защита от пустой строки
    if (!link || link.trim() === '') {
        return '/' + apiPrefix;
    }

    // 🔹 3. Убираем ВСЕ начальные и конечные слеши с обеих частей
    const cleanLink = link.replace(/^\/+|\/+$/g, '');
    const cleanPrefix = apiPrefix.replace(/^\/+|\/+$/g, '');

    // 🔹 4. Разбиваем link на части и проверяем, не начинается ли он с apiPrefix
    const linkParts = cleanLink.split('/');
    if (linkParts[0] === cleanPrefix) {
        // Уже содержит префикс — не дублируем
        return '/' + cleanLink;
    }

    // 🔹 5. Склеиваем префикс и путь
    return '/' + cleanPrefix + '/' + cleanLink;
}

export async function makeAxiosFactory(
    link: string,
    method: string = "GET",
    data: any = null,
    config: AxiosRequestConfig | null = null
): Promise<AxiosResponse<any>> {
    // 🔹 Проверка интернета
    if (!navigator.onLine) {
        const alertStore = useAlertStore();
        alertStore.show("У вас отключился интернет!");
        return Promise.reject(new Error("У вас отключился интернет!"));
    }

    // 🔹 Получаем конфиг
    const configStore = useConfigStore();
    const apiPrefix = configStore.apiPrefix || 'api';

    // 🔹 Telegram WebApp данные
    const tgData = (window as any).Telegram?.WebApp?.initData || null;
    if (tgData) {
        axios.defaults.headers.common["X-TG-DATA"] = btoa(tgData);
    }

    // 🔹 Формируем корректный URL
    const url = buildUrl(link, apiPrefix);

    console.log("build url", url)
    // 🔹 CSRF-токен для обычного API
    let headers: Record<string, string> = {
        'Accept': 'application/json',
    };

    if (apiPrefix === "api") {
        const xsrfCookie = document.cookie
            .split('; ')
            .find(row => row.startsWith('XSRF-TOKEN='))
            ?.split('=')[1];

        if (xsrfCookie) {
            const token = decodeURIComponent(xsrfCookie);
            headers['X-XSRF-TOKEN'] = token;
            axios.defaults.headers.common['X-XSRF-TOKEN'] = token;
        }
    }

    // 🔹 Объединяем заголовки с переданным конфигом
    const finalConfig: AxiosRequestConfig = {
        ...(config || {}),
        headers: {
            ...(config?.headers || {}),
            ...headers,
        },
    };

    const alertStore = useAlertStore();

    try {
        let response: AxiosResponse<any>;
        let needSuccessAlert = false;

        switch (method.toUpperCase()) {
            case "POST":
                response = await axios.post(url, data, finalConfig);
                break;
            case "PUT":
                needSuccessAlert = true;
                response = await axios.put(url, data, finalConfig);
                break;
            case "PATCH":
                needSuccessAlert = true;
                response = await axios.patch(url, data, finalConfig);
                break;
            case "DELETE":
                needSuccessAlert = true;
                response = await axios.delete(url, finalConfig);
                break;
            case "GET":
            default:
                response = await axios.get(url, finalConfig);
                break;
        }

        if (needSuccessAlert) {
            alertStore.show("Операция выполнена успешно", "success");
        }

        return response;
    } catch (error: any) {
        // 🔹 419 — сессия истекла
        if (error?.response?.status === 419) {
            alertStore.show("Сессия истекла, страница будет перезагружена", "warning");
            setTimeout(() => {
                window.location.href = "/login";
            }, 1500);
            return Promise.reject(new Error("Сессия истекла"));
        }

        // 🔹 401 — не авторизован
        if (error?.response?.status === 401) {
            alertStore.show("Вы не авторизованы", "warning");
            setTimeout(() => {
                window.location.href = "/login";
            }, 1500);
            return Promise.reject(new Error("Пользователь не авторизован"));
        }

        // 🔹 Ошибки валидации (422) и другие — пробрасываем дальше
        // НЕ показываем алерт для 422 (ошибки формы будут показаны компонентом)
        if (error?.response?.status !== 422) {
            const message = error?.response?.data?.message || error?.message || "Неизвестная ошибка";
            alertStore.show(`Ошибка: ${message}`, "error");
        }

        return Promise.reject(error);
    }
}
