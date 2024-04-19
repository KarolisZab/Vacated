import axios, { AxiosError, AxiosRequestConfig } from 'axios';
import authHeader from './auth-header';
import { API_URL } from '../config';
import handleError from './handler';

class ApiService {
    /* eslint-disable-next-line */
    private getConfig(params: Object | null = null): AxiosRequestConfig<any> {
        return {
            baseURL: API_URL,
            headers: authHeader(),
            params
        }
    }

    /* eslint-disable-next-line */
    async get<T>(url: string, params: Object | null = null): Promise<T> {
        return await axios.get<T>(url, this.getConfig(params))
            .then((response) => response.data)
            .catch((error: AxiosError) => {
                handleError(error);
                throw error;
            });
    }

    /* eslint-disable-next-line */
    async post<T>(url: string, data: any): Promise<T> {
        return await axios.post<T>(url, data, this.getConfig())
            .then((response) => response.data)
            .catch((error: AxiosError) => {
                handleError(error);
                throw error;
            });
    }
    
    /* eslint-disable-next-line */
    async patch<T>(url: string, data: any): Promise<T> {
        return await axios.patch<T>(url, data, this.getConfig())
            .then((response) => response.data)
            .catch((error: AxiosError) => {
                handleError(error);
                throw error;
            });
    }
    
    async delete(url: string): Promise<void> {
        await axios.delete(url, this.getConfig())
            .catch((error: AxiosError) => {
                handleError(error);
                throw error;
            });
    }
}

export default new ApiService();