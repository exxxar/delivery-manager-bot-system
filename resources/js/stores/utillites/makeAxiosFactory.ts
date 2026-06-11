import axios, {AxiosResponse, AxiosRequestConfig} from "axios";
import {useAlertStore} from "./useAlertStore";
import {useConfigStore} from "../config.js";

export async function makeAxiosFactory(
    link: string,
    method: string = "GET",
    data: any = null,
    config: AxiosRequestConfig | null = null
):  // @ts-ignore
    Promise<AxiosResponse<any>> {
    if (!navigator.onLine) {
        const alertStore = useAlertStore();
        alertStore.show("У вас отключился интернет!");
        // @ts-ignore
        return Promise.reject("У вас отключился интернет!");
    }

    axios.defaults.withCredentials = true;
    axios.defaults.baseURL = 'http://127.0.0.1:8000';

    const tgData = (window as any).Telegram?.WebApp.initData || null;

    if (tgData)
        axios.defaults.headers.common["X-TG-DATA"] = tgData ? btoa(tgData) : null;

    const configStore = useConfigStore();

    link =  "/" + configStore.apiPrefix + link

    if (configStore.apiPrefix === "api") {
        axios.defaults.withXSRFToken = true;

        const token = decodeURIComponent(
            document.cookie
                .split('; ')
                .find(row => row.startsWith('XSRF-TOKEN='))
                ?.split('=')[1]
        )

       // axios.defaults.headers.common['X-CSRF-TOKEN'] = token;
        axios.defaults.headers.common['X-XSRF-TOKEN'] = token

        config = {
            ...config,
            headers: {
                'X-XSRF-TOKEN': token,
                'Accept': 'application/json'
            }
        }
    }


    //console.log("check token", token)
    const alertStore = useAlertStore();



    try {
        let response: AxiosResponse<any>;
        let needSuccessAlert = false
        switch (method.toUpperCase()) {
            case "POST":
                response = await axios.post(link, data, config || undefined);
                break;
            case "PUT":
                needSuccessAlert = true
                response = await axios.put(link, data);
                break;
            case "DELETE":
                needSuccessAlert = true
                response = await axios.delete(link);
                break;
            case "GET":
            default:
                response = await axios.get(link, config || undefined);
                break;
        }

        // Успешный результат

        if (needSuccessAlert)
            alertStore.show("Операция выполнена успешно", "success");

        return response;
    } catch (error: any) {

        // Проверка на 419 ошибку
        if (error?.response?.status === 419) {
            alertStore.show("Сессия истекла, страница будет перезагружена", "warning");
            window.location.href = "/login"

            // @ts-ignore
            return Promise.reject("Сессия истекла");
        }

        if (error?.response?.status === 401) {
            alertStore.show("Вы не авторизованы", "warning");
            window.location.href = "/login"

            // @ts-ignore
            return Promise.reject("Пользователь не авторизован");
        }
        // Ошибка
        alertStore.show(`Ошибка: ${error?.message || "Неизвестная ошибка"}`);
        // @ts-ignore
        return Promise.reject(error);
    }
}
