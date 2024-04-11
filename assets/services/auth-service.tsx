import axios from "axios";
import { API_URL } from "../config";
import { EmployeeRegistrationData } from "./types";

export interface User {
    id: string;
    email: string;
    roles: string[];
    firstName: string;
    lastName: string;
    phoneNumber: string;
    access_token: string;
}

class AuthService {
    private authenticationChangeSubscribers: (() => void)[] = [];
    
    login(email: string, password: string): Promise<User> {
        return axios
            .post(API_URL + "/login", {
                email,
                password
            })
            .then(response => {
                if (response.data.access_token) {
                    localStorage.setItem("user", JSON.stringify(response.data));
                    this.notifySubscribers();
                }

            return response.data;
        });
    }

    logout(): void {
        if(localStorage.getItem("user")) {
            localStorage.removeItem("user");
            this.notifySubscribers();
        }
    }

    register(data: EmployeeRegistrationData): Promise<void> {
        return axios.post(API_URL + "/register", data);
    }

    getCurrentUser(): User | null {
        const userStr = localStorage.getItem("user");
        if (userStr) {
            return JSON.parse(userStr);
        }

        return null;
    }

    isAuthenticated(): boolean {
        const user = this.getCurrentUser();
        return !!user && !!user.access_token;
    }

    isAdmin(): boolean {
        const user = this.getCurrentUser();
        return !!user && user.roles.includes("ROLE_ADMIN");
    }

    subscribe(callback: () => void): void {
        this.authenticationChangeSubscribers.push(callback);
    }

    unsubscribe(callback: () => void): void {
        this.authenticationChangeSubscribers = this.authenticationChangeSubscribers.filter(subscriber => subscriber !== callback);
    }

    private notifySubscribers(): void {
        this.authenticationChangeSubscribers.forEach(subscriber => subscriber());
    }
}

export default new AuthService();