import axios from "axios";
import { API_URL } from "../config";

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
    login(email: string, password: string): Promise<User> {
        return axios
        .post(API_URL + "login", {
            email,
            password
        })
        .then(response => {
            if (response.data.access_token) {
                localStorage.setItem("user", JSON.stringify(response.data));
            }

            return response.data;
        });
    }

    logout(): void {
        localStorage.removeItem("user");
    }

    register(email: string, password: string, firstName: string, lastName: string, phoneNumber: string): Promise<any> {
        return axios.post(API_URL + "register", {
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

    // getCurrentUserToken(): string | null {
    //     const user = this.getCurrentUser();
    //     return user ? user.access_token : null;
    // }
}

export default new AuthService();