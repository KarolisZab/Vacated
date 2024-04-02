import axios from "axios";
import { API_URL } from "../config";
import apiService from "./api-service";
import authHeader from "./auth-header";
// import handler from "./handler";
import { NavigateFunction } from "react-router-dom";
import handleAuthenticationError from "./handler";

const URL = '/admin/users';

interface Employee {
    id: string;
    email: string;
    roles: string[];
    firstName: string;
    lastName: string;
    phoneNumber: string;
    access_token: string;
}

class EmployeeService {
    
    async getAllEmployees(): Promise<Employee[]> {
        return await apiService.get<Employee[]>(URL);
    }

    async getEmployeeById(employeeId: string): Promise<Employee> {
        return await apiService.get<Employee>(`${URL}/${employeeId}`);
    }

    async updateEmployee(employeeId: string, employeeData: Partial<Employee>): Promise<Employee> {
        return await apiService.patch<Employee>(`${URL}/${employeeId}`, employeeData);
    }

    async deleteEmployee(employeeId: string): Promise<void> {
        return await apiService.delete(`${URL}/${employeeId}`);
    }
}

export default new EmployeeService();