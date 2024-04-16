import axios from "axios";
import { API_URL } from "../config";
import { EmployeeRegistrationData } from "./types";
import { GoogleLoginResponse, GoogleLoginResponseOffline } from "react-google-login";

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

    async loginWithGoogle(response: GoogleLoginResponse | GoogleLoginResponseOffline): Promise<void> {
        if ('tokenId' in response) {
            // Handle successful Google login
            // Send the tokenId to your backend to authenticate the user
            const tokenId = response.tokenId;
            try {
                // Send the tokenId to your backend to authenticate the user
                const response = await fetch('/api/google-login', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ tokenId }),
                });

                if (response.ok) {
                    console.log('Logged in with Google successfully');
                } else {
                    console.error('Failed to log in with Google:', response.statusText);
                }
            } catch (error) {
                console.error('Error logging in with Google:', error.message);
                throw new Error('Failed to log in with Google');
            }
        } else {
            console.error('Google login failed:', response);
            throw new Error('Failed to log in with Google');
        }
    }
}

export default new AuthService();