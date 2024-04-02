import axios, { AxiosError } from "axios";
import { useNavigate, Navigate, redirect } from "react-router-dom";
import { API_URL } from "../config";
import React = require("react");

interface User {
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
        try {
            localStorage.removeItem("user");
            this.notifySubscribers();
        } catch (error) {
            console.error("Error removing user from localStorage:", error);
        }
    }

    register(email: string, password: string, firstName: string, lastName: string, phoneNumber: string): Promise<any> {
        return axios.post(API_URL + "/register", {
            email,
            password,
            firstName,
            lastName,
            phoneNumber
        });
    }

    getCurrentUser(): User {
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