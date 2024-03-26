import axios from "axios";
import authHeader from "./auth-header";
import authService from "./auth-service";
import { API_URL } from "../config";

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
        try {
            const response = await axios.get<Employee[]>(API_URL, {
                headers: authHeader()
            });
            return response.data;
        } catch (error) {
            throw new Error(`Error fetching employees: ${error.message}`);
        }
    }

    async getEmployeeById(employeeId: string): Promise<Employee> {
        try {
            const response = await axios.get<Employee>(`${API_URL}/${employeeId}`, {
                headers: authHeader()
            });
            return response.data;
        } catch (error) {
            throw new Error(`Error fetching employee with ID ${employeeId}: ${error.message}`);
        }
    }

    async updateEmployee(employeeId: string, employeeData: Partial<Employee>): Promise<Employee> {
        try {
            const response = await axios.patch<Employee>(`${API_URL}/${employeeId}`, employeeData, {
                headers: authHeader()
            });
            return response.data;
        } catch (error) {
            throw new Error(`Error updating employee with ID ${employeeId}: ${error.message}`);
        }
    }

    async deleteEmployee(employeeId: string): Promise<void> {
        try {
            await axios.delete(`${API_URL}/${employeeId}`, {
                headers: authHeader()
            });
        } catch (error) {
            throw new Error(`Error deleting employee with ID ${employeeId}: ${error.message}`);
        }
    }
}

export default new EmployeeService();