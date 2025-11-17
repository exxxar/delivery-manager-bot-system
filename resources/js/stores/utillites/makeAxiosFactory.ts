import axios, { AxiosResponse, AxiosRequestConfig } from "axios";

export async function makeAxiosFactory(
    link: string,
    method: string = "GET",
    data: any = null,
    config: AxiosRequestConfig | null = null
):   // @ts-ignore
    Promise<AxiosResponse<any>> {
    if (!navigator.onLine) {
        // @ts-ignore
        return Promise.reject("Вы не в сети!");
    }

    const tgData = (window as any).Telegram?.WebApp.initData || null;

    axios.defaults.headers.common["X-TG-DATA"] = tgData ? btoa(tgData) : null;

    switch (method.toUpperCase()) {
        case "POST":
            return await axios.post(link, data, config || undefined);
        case "PUT":
            return await axios.put(link, data);
        case "DELETE":
            return await axios.delete(link);
        case "GET":
        default:
            return await axios.get(link);
    }
}
